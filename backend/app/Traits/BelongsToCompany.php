<?php

namespace App\Traits;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function ($query) {
            if (auth()->check() && auth()->user()->role !== 'super_admin') {
                $query->where(
                    (new static)->getTable() . '.company_id',
                    auth()->user()->company_id
                );
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && !$model->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
