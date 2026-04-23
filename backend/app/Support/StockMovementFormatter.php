<?php

namespace App\Support;

use App\Models\StockMovement;

class StockMovementFormatter
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(StockMovement $movement): array
    {
        $product = $movement->product;

        return [
            'id' => $movement->id,
            'type' => $movement->type,
            'quantity' => (int) $movement->quantity,
            'note' => $movement->note,
            'supplier' => $movement->supplier,
            'reason' => $movement->reason,
            'date' => $movement->date->format('Y-m-d'),
            'created_at' => $movement->created_at?->toIso8601String(),
            'product' => $product ? [
                'id_product' => $product->id_product,
                'nom' => $product->nom,
                'prix' => (float) $product->prix,
                'quantite' => (int) $product->quantite,
                'category' => $product->relationLoaded('category') && $product->category
                    ? [
                        'id_categorie' => $product->category->id_categorie,
                        'nom_categorie' => $product->category->nom_categorie,
                    ]
                    : null,
            ] : null,
            'user' => $movement->relationLoaded('user') && $movement->user
                ? ['id_personne' => $movement->user->id_personne, 'nom' => $movement->user->nom]
                : null,
        ];
    }
}
