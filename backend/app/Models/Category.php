<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToCompany;

    protected $table = 'categories';

    protected $primaryKey = 'id_categorie';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nom_categorie',
        'company_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_categorie', 'id_categorie');
    }
}
