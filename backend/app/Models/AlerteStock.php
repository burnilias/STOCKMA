<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlerteStock extends Model
{
    use BelongsToCompany;

    protected $table = 'alerte_stocks';

    protected $primaryKey = 'id_alerte';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'seuil_min',
        'date_alerte',
        'id_product',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'seuil_min' => 'integer',
            'date_alerte' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    public function declencherAlerte(): void
    {
        $this->date_alerte = now()->toDateString();
        $this->save();
    }

    public function notifierAdmin(): void
    {
        // À implémenter : envoi email ou log
        // Pour l'instant, logger l'alerte
        \Log::info("Alerte stock déclenchée pour le produit ID {$this->id_product}, quantité en dessous du seuil {$this->seuil_min}");
    }
}
