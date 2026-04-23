<?php

namespace App\Support;

use App\Models\Product;

class AlerteStockSync
{
    public static function afterQuantityChange(Product $product): void
    {
    }
}
