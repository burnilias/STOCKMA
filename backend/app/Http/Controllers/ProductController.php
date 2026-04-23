<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // $this->authorize('viewAny', Product::class);

        $query = Product::query()->with(['category', 'alerteStocks']);

        if ($search = $request->query('search')) {
            $query->where('nom', 'like', '%'.$search.'%');
        }

        if ($cid = $request->query('category_id') ?? $request->query('id_categorie')) {
            $query->where('id_categorie', $cid);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $products = $query->orderBy('nom')->paginate($perPage);

        $items = collect($products->items())->map(
            fn (Product $p) => $this->productResponse($p)
        )->values();

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        // $this->authorize('create', Product::class);

        $data = $request->validated();
        if (! isset($data['quantite'])) {
            $data['quantite'] = 0;
        }

        $product = Product::create($data);
        $product->load(['category', 'alerteStocks']);

        return response()->json([
            'success' => true,
            'message' => 'Produit créé.',
            'data' => $this->productResponse($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        // $this->authorize('view', $product);
        $product->load(['category', 'alerteStocks']);

        return response()->json([
            'success' => true,
            'data' => $this->productResponse($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        // $this->authorize('update', $product);

        $product->update($request->validated());
        $product->load(['category', 'alerteStocks']);

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour.',
            'data' => $this->productResponse($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        // $this->authorize('delete', $product);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productResponse(Product $product): array
    {
        $seuil = $product->relationLoaded('alerteStocks') && $product->alerteStocks->isNotEmpty()
            ? (int) $product->alerteStocks->min('seuil_min')
            : null;

        return [
            'id_product' => $product->id_product,
            'nom' => $product->nom,
            'prix' => (float) $product->prix,
            'quantite' => (int) $product->quantite,
            'stock_status' => $product->stockStatus($seuil),
            'id_categorie' => $product->id_categorie,
            'category' => $product->relationLoaded('category') && $product->category
                ? [
                    'id_categorie' => $product->category->id_categorie,
                    'nom_categorie' => $product->category->nom_categorie,
                ]
                : null,
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];
    }
}
