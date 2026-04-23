<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\AlerteStock;
use App\Support\StockMovementFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $cacheKey = "dashboard_data_{$companyId}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $totalProducts = Product::count();

            $valeurTotale = (float) Product::query()
                ->selectRaw('SUM(quantite * prix) as total')
                ->value('total') ?? 0;

            $alertesActives = (int) AlerteStock::query()
                ->join('products', 'alerte_stocks.id_product', '=', 'products.id_product')
                ->whereColumn('products.quantite', '<=', 'alerte_stocks.seuil_min')
                ->count();

            $outOfStock = Product::query()->where('quantite', '<=', 0)->count();

            $recentMovements = StockMovement::query()
                ->with(['product', 'user'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn (StockMovement $m) => [
                    'id' => $m->id,
                    'type' => $m->type,
                    'quantity' => (int) $m->quantity,
                    'date' => $m->date->format('Y-m-d'),
                    'product_name' => $m->product?->nom,
                    'user_name' => $m->user?->nom,
                ]);

            $chartFrom = now()->subDays(6)->startOfDay()->toDateString();
            $chartRows = StockMovement::query()
                ->where('date', '>=', $chartFrom)
                ->select('date', 'type', DB::raw('SUM(quantity) as total'))
                ->groupBy('date', 'type')
                ->get();

            $chartLabels = [];
            $entries = [];
            $exits = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i)->format('Y-m-d');
                $chartLabels[] = $day;

                $dayEntry = $chartRows->first(fn ($r) => $r->date->format('Y-m-d') === $day && $r->type === StockMovement::TYPE_ENTRY);
                $dayExit = $chartRows->first(fn ($r) => $r->date->format('Y-m-d') === $day && $r->type === StockMovement::TYPE_EXIT);

                $entries[] = (float) ($dayEntry?->total ?? 0);
                $exits[] = (float) ($dayExit?->total ?? 0);
            }

            return [
                'kpis' => [
                    'total_products' => $totalProducts,
                    'valeur_totale' => $valeurTotale,
                    'alertes_count' => $alertesActives,
                    'out_of_stock_count' => $outOfStock,
                ],
                'recent_movements' => $recentMovements,
                'chart' => [
                    'labels' => $chartLabels,
                    'entries' => $entries,
                    'exits' => $exits,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function stockStatus(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category', 'alerteStocks']);

        if ($search = $request->query('search')) {
            $query->where('nom', 'like', '%'.$search.'%');
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $products = $query->orderBy('nom')->paginate($perPage);

        $data = collect($products->items())->map(function (Product $p) {
            $seuil = $p->alerteStocks->min('seuil_min');

            return [
                'id_product' => $p->id_product,
                'nom' => $p->nom,
                'prix' => (float) $p->prix,
                'quantite' => (int) $p->quantite,
                'stock_status' => $p->stockStatus($seuil !== null ? (int) $seuil : null),
                'category' => $p->category ? [
                    'id_categorie' => $p->category->id_categorie,
                    'nom_categorie' => $p->category->nom_categorie,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category', 'alerteStocks'])
            ->where(function ($q) {
                $q->where('quantite', '<=', 0)
                    ->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('alerte_stocks')
                            ->whereColumn('alerte_stocks.id_product', 'products.id_product')
                            ->whereColumn('products.quantite', '<=', 'alerte_stocks.seuil_min');
                    });
            });

        if ($search = $request->query('search')) {
            $query->where('nom', 'like', '%'.$search.'%');
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $products = $query->orderBy('quantite')->paginate($perPage);

        $data = collect($products->items())->map(function (Product $p) {
            $seuil = $p->alerteStocks->min('seuil_min');

            return [
                'id_product' => $p->id_product,
                'nom' => $p->nom,
                'prix' => (float) $p->prix,
                'quantite' => (int) $p->quantite,
                'stock_status' => $p->stockStatus($seuil !== null ? (int) $seuil : null),
                'category' => $p->category ? [
                    'id_categorie' => $p->category->id_categorie,
                    'nom_categorie' => $p->category->nom_categorie,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function movementHistory(Request $request): JsonResponse
    {
        $query = StockMovement::query()->with(['product.category', 'user']);

        if ($productId = $request->query('product_id')) {
            $query->where('id_product', $productId);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($from = $request->query('date_from')) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('date', '<=', $to);
        }

        $perPage = min((int) $request->query('per_page', 20), 100);
        $movements = $query->orderByDesc('date')->orderByDesc('id')->paginate($perPage);

        $data = collect($movements->items())->map(
            fn (StockMovement $m) => StockMovementFormatter::toArray($m)
        );

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ]);
    }
}
