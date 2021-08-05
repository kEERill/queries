<?php /** @noinspection PhpUnused */

namespace Keerill\Queries;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method newQueryWithoutRelationships()
 */

trait InteractsWithQuery
{
    /**
     * @param Builder $builder
     * @param Query|null $query
     */
    public function scopeApplyQuery(Builder $builder, Query $query = null)
    {
        if ($query != null)
            $query->apply($builder);
    }

    /**
     * @param Query $query
     * @return $this
     */
    public function applyRelationsByQuery(Query $query): self
    {
        $this->newQueryWithoutRelationships()
            ->applyQuery($query)
            ->eagerLoadRelations([$this]);

        return $this;
    }
}

