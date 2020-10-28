<?php

namespace Keerill\Queries;

use Illuminate\Database\Eloquent\Builder;

trait Querable
{
    /**
     * @param Builder $builder
     * @param Query $filter
     */
    public function scopeApplyQuery(Builder $builder, Query $query)
    {
        $query->apply($builder);
    }
}

