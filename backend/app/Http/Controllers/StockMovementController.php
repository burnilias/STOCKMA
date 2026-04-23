<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\AlerteStockSync;
use App\Support\StockMovementFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'data' => collect($movements->items())->map(
                fn (StockMovement $m) => StockMovementFormatter::toArray($m)
            )->values(),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ]);
    }

    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $data = $request->validated();

        $movement = DB::transaction(function () use ($data, $request): StockMovement {
            /** @var Product $product */
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $qty = (int) $data['quantity'];

            if ($data['type'] === StockMovement::TYPE_ENTRY) {
                $product->quantite = (int) $product->quantite + $qty;
            } else {
                $current = (int) $product->quantite;
                if ($current < $qty) {
                    throw ValidationException::withMessages([
                        'quantity' => ['Stock insuffisant. Disponible : '.$current],
                    ]);
                }
                $product->quantite = $current - $qty;
            }

            $product->save();

            $mov = StockMovement::create([
                'id_product' => $product->id_product,
                'id_user' => $request->user()->id_personne,
                'type' => $data['type'],
                'quantity' => $qty,
                'note' => $data['note'] ?? null,
                'supplier' => $data['supplier'] ?? null,
                'reason' => $data['reason'] ?? null,
                'date' => $data['date'],
            ]);

            AlerteStockSync::afterQuantityChange($product->fresh());

            return $mov;
        });

        $movement->load(['product.category', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Mouvement de stock enregistré.',
            'data' => StockMovementFormatter::toArray($movement),
        ], 201);
    }
}
