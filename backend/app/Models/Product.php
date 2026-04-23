<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany;

    protected $table = 'products';

    protected $primaryKey = 'id_product';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nom',
        'prix',
        'quantite',
        'id_categorie',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'quantite' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id_product';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categorie', 'id_categorie');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'id_product', 'id_product');
    }

    public function alerteStocks(): HasMany
    {
        return $this->hasMany(AlerteStock::class, 'id_product', 'id_product');
    }

    public function verifierStock(int $quantiteDemandee): bool
    {
        return (int) $this->quantite >= $quantiteDemandee;
    }

    public function stockStatus(?int $seuilMin = null): string
    {
        $qty = (int) $this->quantite;
        if ($qty <= 0) {
            return 'out';
        }
        if ($seuilMin !== null && $qty <= $seuilMin) {
            return 'low';
        }

        return 'ok';
    }
}
