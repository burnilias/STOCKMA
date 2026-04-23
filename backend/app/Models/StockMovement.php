<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToCompany;

    public const TYPE_ENTRY = 'entry';
    public const TYPE_EXIT = 'exit';

    protected $table = 'stock_movements';

    protected $fillable = [
        'id_product',
        'id_user',
        'type',
        'quantity',
        'note',
        'supplier',
        'reason',
        'date',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_personne');
    }
}
