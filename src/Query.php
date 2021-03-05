<?php

namespace Keerill\Queries;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class Query
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var array Allowed filtering
     */
    protected $fields = [];

    /**
     * @var array Sortable columns
     */
    protected $sortable = ['created_at'];

    /**
     * @var string|null Default sortable column
     */
    protected $sortBy = 'created_at';

    /**
     * @var boolean Default direction
     */
    protected $sortDesc = true;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        if (count($this->rules) > 0)
        {
            $this->request
                ->validate($this->getValidationRules());
        }
    }

    /**
     * @return array
     */
    protected function getFields(): array
    {
        return array_filter($this->request->only($this->fields), function($value) { return $value !== null; });
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return array_intersect($this->request->get('scopes', []), $this->relations);
    }

    /**
     * @return string|null
     */
    public function getSortBy(): ?string
    {
        return in_array($this->request->get('sort_by'), $this->sortable)
            ? $this->request->get('sort_by') : $this->sortBy;
    }

    /**
     * @return string
     */
    public function getSortDesc(): string
    {
        return boolval($this->request->get('sort_desc', $this->sortDesc)) ? 'desc' : 'asc';
    }

    /**
     * @return array
     */
    protected function getValidationRules(): array
    {
        return array_merge([
            'scopes' => ['filled', 'array'],
            'scopes.*' => ['required', 'string'],
            'sort_by' => ['filled', 'string'],
            'sort_desc' => ['filled', 'boolean']
        ], $this->rules);
    }

    /**
     * @param Builder $builder
     * @return void
     */
    public function apply(Builder $builder): void
    {
        $this->builder = $builder;

        $this->filteringByFields($this->getFields());
        $this->loadingRelations($this->getRelations());

        $sortBy = $this->getSortBy();

        if ($sortBy !== null) {
            $this->sortingByField($sortBy, $this->getSortDesc());
        }
    }

    /**
     * @param array $fields
     * @return void
     */
    protected function filteringByFields(array $fields): void
    {
        foreach ($fields as $field => $value) {
            $method = Str::camel($field);

            if (method_exists($this, $method))
                call_user_func([$this, $method], $value);
        }
    }

    /**
     * @param array $relations
     * @return void
     */
    protected function loadingRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            $method = Str::camel("relation_{$relation}");

            if (method_exists($this, $method)) {
                call_user_func([$this, $method]);
                continue;
            }

            $this->builder
                ->with($relation);
        }
    }

    /**
     * @param string $sortBy
     * @param string $sortDesc
     * @return void
     */
    protected function sortingByField(string $sortBy, string $sortDesc): void
    {
        $this->builder
            ->orderBy($sortBy, $sortDesc);
    }
}
