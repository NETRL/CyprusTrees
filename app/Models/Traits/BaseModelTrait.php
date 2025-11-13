<?php


namespace App\Models\Traits;

use App\Helpers\ColumnTypeHelper;
use Throwable;

trait BaseModelTrait
{
    public function getModelRelationships(): array
    {
        try {
            return $this->relationships();
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function getDataColumns($tableColumns = [], $formColumns = [], $excludedTableColumns = [], $excludedFormColumns = []): array
    {
        $self         = new static();
        $formColumns  = $formColumns === [] ? ($self->formColumns ?? []) : $formColumns;
        $tableColumns = $tableColumns === [] ? ($self->tableColumns ?? []) : $tableColumns;

        if ($excludedTableColumns !== []) {
            $tableColumns = array_diff($tableColumns, $excludedTableColumns);
        }

        if ($excludedFormColumns !== []) {
            $formColumns = array_diff($formColumns, $excludedFormColumns);
        }

        $cols = ColumnTypeHelper::getColumnsForTable(
            $self->getTable(),
            $formColumns,
            $tableColumns,
            $self->getHidden(),
        );

        if (!empty($tableColumns) && isset($cols['items'])) {
            $byName = [];
            foreach ($cols['items'] as $c) {
                $byName[$c['field']] = $c;
            }
            $filtered = [];
            foreach ($tableColumns as $name) {
                if (isset($byName[$name])) {
                    $filtered[] = $byName[$name];
                }
            }
            $cols['items'] = $filtered;
        }

        return $cols;
    }
}
