<?php

namespace App\Traits;

trait SmartFilter
{

    /**
     * Apply filters to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFilter($query, array $filters)
    {
        foreach ($this->getFilterableAttributes($filters) as $attribute => $value) {
            if (isset($value) && $this->isFilterableAttribute($attribute)) {
                $operation = $this->getFilterOperation($attribute);
                $this->analyseFilterAttribute($query, $attribute, $value, $operation);
            }
        }

        return $query;
    }

    /**
     * Get the filterable attributes from the provided filters.
     *
     * @param  array  $filters
     * @return array
     */
    protected function getFilterableAttributes(array $filters)
    {
        $filterableKeys = array_keys($this->filterableAttributes);
        $filteredKeys = array_intersect($filterableKeys, array_keys($filters));
        $nestedFilters = $this->getNestedFilters($filteredKeys, $filters);
        // dd($filters, $filterableKeys, $filteredKeys, $nestedFilters, array_merge($filters, $nestedFilters));
        return array_merge($filters, $nestedFilters);
    }

    /**
     * Flatten the filterable attributes array to handle nested attributes.
     *
     * @param  array  $filterableAttributes
     * @param  string|null  $prefix
     * @return array
     */
    protected function getNestedFilters($filteredKeys)
    {
        $nestedFilters = [];

        foreach ($filteredKeys as $key) {
            $parts = explode('.', $key);
            $attribute = $parts[0];

            if (count($parts) > 1 && array_key_exists($attribute, $this->filterableAttributes)) {
                $nestedKey = implode('.', array_slice($parts, 1));

                $operation = $this->filterableAttributes[$attribute]['operation'] ?? null;

                if (isset($filters[$key])) {
                    $nestedFilters[$nestedKey] = $filters[$key];
                }

                $nestedFilters[$nestedKey]['operation'] = $operation;
                $nestedFilters[$nestedKey]['table'] = $this->filterableAttributes[$attribute]['table'] ?? null;
            }
        }
        return $nestedFilters;
    }

    /**
     * Check if the given attribute is filterable.
     *
     * @param  string  $attribute
     * @return bool
     */
    protected function isFilterableAttribute($attribute)
    {
        return array_key_exists($attribute, $this->filterableAttributes);
    }

    /**
     * Get the filter operation for the given attribute.
     *
     * @param  string  $attribute
     * @return string|null
     */
    protected function getFilterOperation($attribute)
    {
        if (str_contains($attribute, '.')) {
            // Nested attribute operation
            return $this->filterableAttributes[$attribute]['operation'] ?? null;
        } else {
            // Simple attribute operation
            return $this->filterableAttributes[$attribute] ?? null;
        }
    }

    /**
     * Analyse the attribute if it's simple or nested and then apply the filteration
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  string|null  $operation
     * @return void
     */
    function analyseFilterAttribute($query, $attribute, $value, $operation)
    {
        $parts = explode('.', $attribute);
        $attributeName = array_shift($parts);

        if (count($parts) > 0) {
            // Nested attribute filtering
            $relatedAttribute = implode('.', $parts);

            $partsSubChild = explode('.', $relatedAttribute);
            $attributeSubChildName = array_shift($partsSubChild);
            if (count($partsSubChild) > 0) {
                $relatedAttributeSubChild = implode('.', $partsSubChild);
                $query->whereHas($attributeName, function ($query) use ($relatedAttributeSubChild, $value, $operation, $attributeSubChildName) {
                    $query->whereHas($attributeSubChildName, function ($query) use ($relatedAttributeSubChild, $value, $operation) {
                        $this->applyAttributeFilter($query, $relatedAttributeSubChild, $value, $operation);
                    });
                });
            } else {
                $query->whereHas($attributeName, function ($query) use ($relatedAttribute, $value, $operation) {
                    $this->applyAttributeFilter($query, $relatedAttribute, $value, $operation);
                });
            }
        } else {
            $this->applyAttributeFilter($query, $attribute, $value, $operation);
        }
    }

    /**
     * Apply the filter for the given attribute to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  string|null  $operation
     * @return void
     */
    protected function applyAttributeFilter($query, $attribute, $value, $operation)
    {
        // Simple attribute filtering
        switch ($operation) {
            case 'like':
                $query->where($attribute, 'LIKE', '%' . $value . '%');
                break;
            case 'equals':
                $query->where($attribute, $value);
                break;
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween($attribute, $value);
                }
                break;
            case 'date':
                if (is_array($value)) {
                    if (isset($value['date_from']) && isset($value['date_to'])) {
                        $query->whereBetween($attribute, [$value['date_from'], $value['date_to']]);
                    } elseif (isset($value['date_from'])) {
                        $query->whereDate($attribute, '>=', $value['date_from']);
                    } elseif (isset($value['date_to'])) {
                        $query->whereDate($attribute, '<=', $value['date_to']);
                    }
                }
                break;
            default:
                // Unsupported operation or undefined operation
                break;
        }
    }

    /**
     * Get the table name for the nested relation.
     *
     * @param  string  $relation
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function getNestedTable($relation, $model)
    {
        // dd($this->filterableAttributes[$relation]['table']);
        return $this->filterableAttributes[$relation]['table'] ?? $model->getTable();
    }
}
