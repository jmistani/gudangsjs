<?php

namespace App\Traits;
use App\Models\stockMutation;

trait SecondReferencedByStockMutations
{
    /**
     * Relation with StockMutation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphMany
     */
    public function stockMutations()
    {
        return $this->morphMany(StockMutation::class, 'second_reference');
    }
}
