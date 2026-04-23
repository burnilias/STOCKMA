<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'statut',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'company_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'company_id');
    }

    public function alerteStocks(): HasMany
    {
        return $this->hasMany(AlerteStock::class, 'company_id');
    }
}
