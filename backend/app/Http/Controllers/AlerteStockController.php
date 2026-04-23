<?php

namespace App\Http\Controllers;

use App\Models\AlerteStock;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlerteStockController extends Controller
{
    /**
     * Liste des alertes avec produit (seuil dépassé ou stock critique).
     */
    public function index(Request $request): JsonResponse
    {
        $query = AlerteStock::query()->with('product.category');

        if ($search = $request->query('search')) {
            $query->whereHas('product', fn ($q) => $q->where('nom', 'like', '%'.$search.'%'));
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $rows = $query->orderByDesc('date_alerte')->paginate($perPage);

        $data = collect($rows->items())->map(function (AlerteStock $a) {
            $p = $a->product;

            return [
                'id_alerte' => $a->id_alerte,
                'seuil_min' => (int) $a->seuil_min,
                'date_alerte' => $a->date_alerte->format('Y-m-d'),
                'product' => $p ? [
                    'id_product' => $p->id_product,
                    'nom' => $p->nom,
                    'quantite' => (int) $p->quantite,
                    'prix' => (float) $p->prix,
                    'category' => $p->relationLoaded('category') && $p->category
                        ? $p->category->nom_categorie
                        : null,
                ] : null,
                'alerte_active' => $p && (int) $p->quantite <= (int) $a->seuil_min,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_product' => 'required|exists:products,id_product',
            'seuil_min' => 'required|integer|min:0',
        ]);

        $alerte = AlerteStock::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Alerte de stock créée.',
            'data' => $alerte,
        ], 201);
    }

    public function update(Request $request, AlerteStock $alerte): JsonResponse
    {
        $data = $request->validate([
            'seuil_min' => 'required|integer|min:0',
        ]);

        $alerte->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Alerte de stock mise à jour.',
            'data' => $alerte,
        ]);
    }

    public function destroy(AlerteStock $alerte): JsonResponse
    {
        $alerte->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alerte de stock supprimée.',
        ]);
    }
}
