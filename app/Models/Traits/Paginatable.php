<?php

namespace App\Models\Traits;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Str;
use Throwable;

trait Paginatable
{
    private const ASC  = 'asc';
    private const DESC = 'desc';

    public function scopeSetUpQuery(Builder $baseQuery): Builder
    {
        $valid = [];

        foreach ($this->getModelRelationships() as $relation) {
            if (method_exists($this, $relation)) {
                $valid[] = $relation;
            }
        }

        $baseQuery->with($valid);

        // for date filter.
        $dateFrom   = request()->input('date_from');
        $dateTo     = request()->input('date_to');
        $dateFields = request()->input('date_fields', []);
        $tz         = request()->input('tz', 'Europe/Athens');

        if (($dateFrom || $dateTo) && !empty($dateFields)) {
            $baseQuery->customDateFilter($dateFields, $dateFrom, $dateTo, $tz);
        }
        // accept both camelCase and snake_case params
        $sortOrder = request()->input('sortOrder', request()->input('sort_order'));
        $sortField = request()->input('sortField', request()->input('sort_field'));
        $search    = request()->input('search');

        // if user supplied a valid sort, apply it
        if ($sortField && in_array(strtolower($sortOrder), [self::ASC, self::DESC], true)) {
            $baseQuery->customSort($sortField, $sortOrder);
        } else {
            // default ordering when nothing provided
            $defaultSortField = $this->getDefaultSortField();
            $baseQuery->orderBy($defaultSortField, self::ASC);
        }

        // optional: apply search only if not empty
        if (!empty($search)) {
            $baseQuery->customSearch($search);
        }

        return $baseQuery;
    }


    public function scopeCustomSort(Builder $query, string $sortField, string $sortOrder): Builder
    {
        if ($this->tryDefinedSort($query, $sortField, $sortOrder)) {
            $function = Str::camel(str_replace('.', '_', $sortField)) . 'Sort';
            return $query->$function($sortOrder);
        }

        if (str_contains($sortField, '.') && $this->tryRelationSort($query, $sortField, $sortOrder)) {
            [$relationship, $column] = explode('.', $sortField, 2);

            /** @var Relation $rel */
            $rel = $this->$relationship();
            $related = $rel->getRelated();
            $relTable = $related->getTable();

            // Always select only base table columns to avoid collisions (id, created_at, etc.)
            $query->select($this->getTable() . '.*');

            if ($rel instanceof BelongsTo) {
                // maintenance_events.tree_id = tree.id
                $foreignKey = $rel->getForeignKeyName(); // e.g. tree_id
                $ownerKey   = $rel->getOwnerKeyName();   // e.g. id

                return $query
                    ->leftJoin("$relTable AS $relationship", "{$this->getTable()}.$foreignKey", '=', "$relationship.$ownerKey")
                    ->orderBy("$relationship.$column", $sortOrder);
            }

            if ($rel instanceof HasOneOrMany) {
                // base.id = rel.base_id (rare for your case, but keeps it generic)
                $localKey   = $rel->getLocalKeyName();
                $foreignKey = $rel->getForeignKeyName();

                return $query
                    ->leftJoin("$relTable AS $relationship", "{$this->getTable()}.$localKey", '=', "$relationship.$foreignKey")
                    ->orderBy("$relationship.$column", $sortOrder);
            }

            // Fallback: do nothing
            return $query;
        }


        if ($this->tryBaseTableSort($query, $sortField, $sortOrder)) {
            return $query->orderBy($sortField, $sortOrder);
        }

        return $query;
    }


    public function scopeCustomSearch(Builder $query, string $searchTerm): Builder
    {

        $searchTerm = trim($searchTerm);
        if ($searchTerm === '') return $query;

        return $query->where(function ($outer) use ($searchTerm) {

            foreach ($this->searchable as $i => $path) {

                if (!str_contains($path, '.')) {
                    $isFirst = ($i === 0);

                    if ($this->applyEnumSearchIfNeeded($outer, $path, $searchTerm, $isFirst)) {
                        continue;
                    }

                    // base table column
                    $method = ($i === 0) ? 'where' : 'orWhere';
                    $outer->$method($path, 'ILIKE', "%{$searchTerm}%"); // For Postgres ILIKE is Case agnostic. ('like' is the default)
                    continue;
                }

                // nested relation path: tree.species.common_name etc
                $parts = explode('.', $path);
                $column = array_pop($parts); // last segment is column
                $relations = $parts;         // preceding segments are relations

                $outer->orWhereHas($relations[0], function ($q) use ($relations, $column, $searchTerm) {
                    $this->applyNestedWhereHas($q, array_slice($relations, 1), $column, $searchTerm);
                });
            }
        });
    }


    public function scopeCustomDateFilter(
        Builder $query,
        array $fields,
        ?string $from,
        ?string $to,
        string $tz = 'Europe/Athens'
    ): Builder {
        // whitelist: only allow filterable date columns
        $allowed = property_exists($this, 'dateFilterable') ? $this->dateFilterable : [];
        $fields = array_values(array_intersect($fields, $allowed));
        if (empty($fields)) return $query;

        // parse boundaries (local tz day bounds -> UTC)
        $fromUtc = $from
            ? CarbonImmutable::parse($from, $tz)->startOfDay()->utc()
            : null;

        $toUtc = $to
            ? CarbonImmutable::parse($to, $tz)->endOfDay()->utc()
            : null;

        if (!$fromUtc && !$toUtc) return $query;

        return $query->where(function ($q) use ($fields, $fromUtc, $toUtc) {
            foreach ($fields as $i => $field) {
                $q->orWhere(function ($qq) use ($field, $fromUtc, $toUtc) {
                    if ($fromUtc) $qq->where($field, '>=', $fromUtc);
                    if ($toUtc)   $qq->where($field, '<=', $toUtc);
                });
            }
        });
    }

    private function applyNestedWhereHas(Builder $q, array $remainingRelations, string $column, string $searchTerm): void
    {
        if (empty($remainingRelations)) {
            $q->where($column, 'ILIKE', "%{$searchTerm}%"); // For Postgres ILIKE is Case agnostic. ('like' is the default)
            return;
        }

        $next = array_shift($remainingRelations);
        $q->whereHas($next, function ($qq) use ($remainingRelations, $column, $searchTerm) {
            $this->applyNestedWhereHas($qq, $remainingRelations, $column, $searchTerm);
        });
    }

    private function applyEnumSearchIfNeeded(Builder $outer, string $column, string $term, bool $isFirst): bool
    {
        $values = $this->enumValuesMatching($column, $term);
        if (empty($values)) return false;

        // Use IN(...) against the stored enum values
        if ($isFirst) {
            $outer->whereIn($column, $values);
        } else {
            $outer->orWhereIn($column, $values);
        }

        return true;
    }

    private function enumValuesMatching(string $column, string $term): array
    {
        if (!property_exists($this, 'enumSearchMap')) return [];

        $map = $this->enumSearchMap ?? [];
        $enumClass = $map[$column] ?? null;

        if (!$enumClass || !enum_exists($enumClass)) return [];

        $t = mb_strtolower(trim($term));
        if ($t === '') return [];

        $matches = [];

        foreach ($enumClass::cases() as $case) {
            $value = mb_strtolower($case->value);
            $label = method_exists($case, 'label') ? mb_strtolower($case->label()) : '';

            // match on: value, label, and also label shorthand tokens like "m", "f", "m/f"
            if (str_contains($value, $t) || ($label !== '' && str_contains($label, $t))) {
                $matches[] = $case->value;
                continue;
            }

            // extra: match tokens extracted from label, e.g. "m", "male", "m/f", "monoecious"
            if ($label !== '') {
                $labelTokens = preg_split('/[^a-z0-9\/]+/i', $label, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($labelTokens as $tok) {
                    if ($tok === $t) {
                        $matches[] = $case->value;
                        break;
                    }
                }
            }
        }

        return array_values(array_unique($matches));
    }





    private function tryBaseTableSort($query, $sortField, $sortOrder): bool
    {
        try {
            $query->clone()->orderBy($sortField, $sortOrder)->limit(1)->get();
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    private function tryRelationSort($query, $sortField, $sortOrder): bool
    {
        try {
            [$relationship, $column] = explode('.', $sortField, 2);

            $rel = $this->$relationship();
            $related = $rel->getRelated();
            $relTable = $related->getTable();

            $q = $query->clone()->select($this->getTable() . '.*');

            if ($rel instanceof BelongsTo) {
                $foreignKey = $rel->getForeignKeyName();
                $ownerKey   = $rel->getOwnerKeyName();

                $q->leftJoin("$relTable AS $relationship", "{$this->getTable()}.$foreignKey", '=', "$relationship.$ownerKey")
                    ->orderBy("$relationship.$column", $sortOrder)
                    ->limit(1)->get();

                return true;
            }

            if ($rel instanceof HasOneOrMany) {
                $localKey   = $rel->getLocalKeyName();
                $foreignKey = $rel->getForeignKeyName();

                $q->leftJoin("$relTable AS $relationship", "{$this->getTable()}.$localKey", '=', "$relationship.$foreignKey")
                    ->orderBy("$relationship.$column", $sortOrder)
                    ->limit(1)->get();

                return true;
            }

            return false;
        } catch (\Throwable) {
            return false;
        }
    }


    private function tryDefinedSort($query, $sortField, $sortOrder): bool
    {
        try {
            $function = Str::camel(str_replace('.', '_', $sortField)) . 'Sort';
            $query->clone()->$function($sortOrder)->limit(1)->get();
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    private function getDefaultSortField(): string
    {
        // If the model defines a default sort field, use it
        if (property_exists($this, 'defaultSortField') && !empty($this->defaultSortField)) {
            return $this->defaultSortField;
        }

        // Otherwise, if it has a $sortable array, use the first one
        if (property_exists($this, 'sortable') && !empty($this->sortable)) {
            return $this->sortable[0];
        }

        // Fallback to the model's primary key (handles non default ids)
        return $this->getKeyName();
    }
}
