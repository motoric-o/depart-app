<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ApiSearchable
{
    /**
     * Apply search, sort, and pagination to the query.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchableFields Array of fields to search. Key = field name, Value = match type ('like', 'exact') or relationship.
     *                                Example: ['name' => 'like', 'category.name' => 'like']
     * @param array $sortableFields Array of allowed sort fields.
     * @param array $defaultSort Default sort ['field' => 'id', 'order' => 'desc']
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function applyApiParams(Builder $query, Request $request, array $searchableFields = [], array $sortableFields = [], array $defaultSort = ['field' => 'created_at', 'order' => 'desc'])
    {
        // 1. Search
        if ($request->filled('search') && !empty($searchableFields)) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field => $type) {
                    // Handle Relationships (dots)
                    if (str_contains($field, '.')) {
                        $relation = Str::beforeLast($field, '.');
                        $column = Str::afterLast($field, '.');
                        
                        $q->orWhereHas($relation, function ($subQ) use ($column, $search) {
                            $subQ->where($column, 'like', "%{$search}%");
                        });
                    } else {
                        // Standard Column
                         $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        // 2. Sort
        $sortBy = $request->get('sort_by', $defaultSort['field']);
        $sortOrder = $request->get('sort_order', $defaultSort['order']);

        if (in_array($sortBy, $sortableFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy($defaultSort['field'], $defaultSort['order']);
        }

        // 3. Paginate
        $perPage = $request->get('per_page', 10);
        return $query->paginate($perPage);
    }
}
