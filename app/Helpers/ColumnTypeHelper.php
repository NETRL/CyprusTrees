<?php

namespace App\Helpers;


use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class ColumnTypeHelper
{
    private static array $modelMappings = [
        //        'App\Models\ModelName'       => ['App\Models\ModelName', 'Label', 'value'],
    ];

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public static function getColumns(string $tableName): JsonResponse
    {
        $columns = DB::select("
            SELECT
                column_name AS Field,
                data_type AS Type,
                is_nullable AS \"Null\",
                column_default AS \"default\",
                ' ' AS extra, -- Placeholder for compatibility
                ' ' AS key -- Placeholder for compatibility
            FROM
                information_schema.columns
            WHERE
                table_schema = 'public' AND -- Use your schema name if it's not 'public'
                table_name = ?
            ORDER BY
                ordinal_position
        ", [$tableName]);





        $columnNames          = [];
        $columnLabels         = [];
        $columnTypes          = [];
        $inputTypes           = [];
        $columnKeys           = [];
        $requiredFields       = [];
        $columnsRequiredInfo  = [];
        $infoColumns          = [];
        $infoColumnsRelations = [];

        foreach ($columns as $column) {
            $columnName    = $column->field;
            $columnType    = $column->type;
            $columnKey     = $column->key;
            $requiredField = $column->Null === 'NO';
            $columnLabel   = ucwords(str_replace('_', ' ', $columnName));
            $infoColumn    = config('database-comments.' . $tableName . '_' . $columnName, null);

            if ($columnKey === 'MUL') {
                $inputTypes[] = 'Dropdown';
                $columnLabel  = ucwords(str_replace('Id', '', $columnLabel));
            } else {
                $inputTypes[] = self::getInputTypeForColumnType($columnType);
            }

            $columnNames[]                     = $columnName;
            $columnLabels[]                    = $columnLabel;
            $columnTypes[]                     = $columnType;
            $columnKeys[]                      = $columnKey;
            $requiredFields[]                  = $requiredField;
            $columnsRequiredInfo[$columnName]  = $requiredField;
            $infoColumns[]                     = $infoColumn;
            $infoColumnsRelations[$columnName] = $infoColumn;
        }

        $modelName = 'App\Models\\' . self::convertToModelName($tableName);

        $relations = self::getRelationships($modelName);


        // add relations in columns
        foreach ($relations as $relation => $type) {
            $relationColumnName = $relation . '_id';
            $columnNames[]      = $relation;
            $relationLabel      = ucwords(self::formatColumnLabel($relation));
            $columnLabels[]     = $relationLabel;
            $columnTypes[]      = 'int';
            $columnKeys[]       = 'MUL';

            $relationRequired = $columnsRequiredInfo[$relationColumnName] ?? false;
            $requiredFields[] = $relationRequired;

            $infoColumns[] = $infoColumnsRelations[$relationColumnName] ?? null;


            if (in_array($type, ['BelongsTo', 'HasOne'])) {
                $inputTypes[] = 'Dropdown';
            } elseif (in_array($type, ['HasMany', 'BelongsToMany', 'MorphToMany', 'MorphMany', 'MorphedByMany'])) {
                $inputTypes[] = 'MultiSelect';
            } elseif ($type === 'MorphTo') {
                $inputTypes[] = 'Dropdown'; // or another appropriate type for MorphTo
            }
        }

        return response()->json([
            'columnNames'    => $columnNames,
            'labelNames'     => $columnLabels,
            'columnTypes'    => $columnTypes,
            'inputTypes'     => $inputTypes,
            'columnKeys'     => $columnKeys,
            'requiredFields' => $requiredFields,
            'infoColumns'    => $infoColumns,
        ]);
    }

    private static function getInputTypeForColumnType(string $columnType): string
    {
        // Normalize the type (remove modifiers like unsigned, size, and time zone details)
        $columnType = strtolower(trim($columnType));
        $columnType = preg_replace([
            '/unsigned/i',
            '/\([^)]*\)/',
            '/\s*without time zone/i',
            '/\s*with time zone/i',
        ], '', $columnType);

        // Define mapping of column types to input types
        $typeMapping = [
            // Numeric types
            'int'       => 'Number',
            'smallint'  => 'Number',
            'mediumint' => 'Number',
            'bigint'    => 'Number',
            'decimal'   => 'Number',
            'float'     => 'Number',
            'double'    => 'Number',
            'numeric'   => 'Number',
            'real'      => 'Number',

            // String types
            'varchar'   => 'InputText',
            'char'      => 'InputText',
            'text'      => 'Textarea',
            'uuid'      => 'InputText',

            // Date and time types
            'date'      => 'Calendar',
            'time'      => 'Calendar',
            'timestamp' => 'Calendar',
            'datetime'  => 'Calendar',

            // Boolean types
            'boolean'   => 'Boolean',
            'bool'      => 'Boolean',
            'tinyint'   => 'Boolean',

            // Other types
            'bit'       => 'Checkbox',
            'enum'      => 'Dropdown',
            'set'       => 'MultiSelect',
            'json'      => 'Textarea',
            'jsonb'     => 'Textarea',
        ];

        // Default to 'InputText' if no mapping is found
        return $typeMapping[$columnType] ?? 'InputText';
    }


    public static function getColumnsForTable(string $tableName, array $formColumns, array $tableColumns, array $hiddenColumns): array
    {
        $columnsData = ColumnTypeHelper::getColumns($tableName)->getData(true);

        $columnsNames = $columnsData["columnNames"];
        $items        = [];

        foreach ($columnsNames as $column => $data) {
            if (in_array($data, $hiddenColumns)) {
                continue;
            }

            $potentialHeader = array_flip($tableColumns)[$data] ?? array_flip($formColumns)[$data] ?? 0;

            $item = [
                'field'          => $data,
                'header'         => !is_numeric($potentialHeader) ? $potentialHeader : $columnsData["labelNames"][$column],
                'component_type' => $columnsData["inputTypes"][$column],
                'showTable'      => in_array($data, array_values($tableColumns)),
                'showForm'       => in_array($data, array_values($formColumns)),
                'required'       => $columnsData["requiredFields"][$column],
                'info'           => $columnsData["infoColumns"][$column],
            ];

            if ($item['component_type'] === 'Dropdown' && str_contains($columnsData['columnNames'][$column], '_enum')) {
                $enumOptions = $tableName . '_' . $data;
                $enumOptions = self::snakeToCamel($enumOptions);
                $enumOptions = 'App\Enum\\' . $enumOptions;
                $enumOptions = $enumOptions::cases();

                if (!is_null($enumOptions)) {
                    $enumOptions = array_map(function ($value) {
                        return [
                            'label' => ucwords(str_replace('_', ' ', strtolower($value->name))),
                            [],
                            app()->getLocale(),
                            'value' => $value->value,
                        ];
                    }, $enumOptions);

                    $item['options'] = $enumOptions;
                }
            } elseif ($item['component_type'] === 'Dropdown') {
                $modelName       = 'App\Models\\' . self::convertForeingKeyToModelName($data);
                $item['options'] = self::getOptionsForModel($modelName);
            } elseif ($item['component_type'] === 'MultiSelect') {
                $modelName       = 'App\Models\\' . self::convertToModelName($data);
                $item['options'] = self::getOptionsForModel($modelName);
                if (isset(self::$modelMappings[$modelName])) {
                    $item['valueColumn'] = self::$modelMappings[$modelName][2];
                }
            }

            $items[] = $item;
        }

        $missingColumns = array_diff($tableColumns, array_column($items, 'field'));

        foreach ($missingColumns as $data) {
            if (in_array($data, $hiddenColumns)) {
                continue;
            }
            $potentialHeader = array_flip($tableColumns)[$data] ?? array_flip($formColumns)[$data] ?? 0;


            $items[] = [
                'field'          => $data,
                'header'         => !is_numeric($potentialHeader) ? $potentialHeader : ucwords(str_replace('.', ' ', $data)),
                'component_type' => 'InputText',
                'showTable'      => true,
                'showForm'       => false,
            ];
        }

        $items = collect($items)->sortBy(function ($item) use ($tableColumns) {
            return array_search($item['field'], $tableColumns);
        })->values()->all();

        $columns['label'] = ucwords(str_replace('_', ' ', $tableName));
        $columns['items'] = $items;

        return $columns;
    }

    private static function getOptionsForModel($modelName): ?array
    {
        if (isset(self::$modelMappings[$modelName])) {
            $mapping = self::$modelMappings[$modelName];
            return self::getOptions($mapping[0], $mapping[1], $mapping[2]);
        }

        return null;
    }

    private static function getOptions($modelName, $labelColumn, $valueColumn): array
    {
        $options = [];

        $items = $modelName::all([$labelColumn, $valueColumn]);

        foreach ($items as $item) {
            $options[] = [
                'label' => $item->{$labelColumn},
                'value' => $item->{$valueColumn}
            ];
        }
        return $options;
    }

    private static function convertForeingKeyToModelName($string): string
    {
        if (str_ends_with($string, '_id')) {
            $string = substr($string, 0, -3);
        }

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    private static function getEnumOptions(string $columnType): array
    {

        preg_match('/^enum\((.*)\)$/', $columnType, $matches);
        $enumValues = explode(',', $matches[1]);
        $options    = [];

        foreach ($enumValues as $value) {
            $v = trim($value, "'");

            $label     = ucwords(str_replace('_', ' ', $v));
            $options[] = [
                'label' => $label,
                'value' => $v
            ];
        }

        return $options;
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    private static function getRelationships($modelName): array
    {
        $modelInstance = app()->make($modelName);

        $reflectionClass = new ReflectionClass($modelInstance);
        $methods         = $reflectionClass->getMethods();

        $relationships = [];

        foreach ($methods as $method) {
            if ($method->class == $modelName && $method->isPublic() && Str::contains($method->getReturnType()?->getName(), [
                'BelongsTo',
                'HasOne',
                'HasMany',
                'BelongsToMany',
                'MorphTo',
                'MorphOne',
                'MorphMany',
                'MorphToMany',
                'MorphedByMany'
            ])) {
                $relation = $method->name;

                switch ($method->getReturnType()->getName()) {
                    case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                        $relationships[$relation] = 'BelongsTo';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOne':
                        $relationships[$relation] = 'HasOne';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasMany':
                        $relationships[$relation] = 'HasMany';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                        $relationships[$relation] = 'BelongsToMany';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\MorphTo':
                        $relationships[$relation] = 'MorphTo';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\MorphOne':
                        $relationships[$relation] = 'MorphOne';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\MorphMany':
                        $relationships[$relation] = 'MorphMany';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\MorphToMany':
                        $relationships[$relation] = 'MorphToMany';
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\MorphedByMany':
                        $relationships[$relation] = 'MorphedByMany';
                        break;
                }
            }
        }

        return $relationships;
    }


    private static function convertToModelName($tableName): string
    {
        $modelName = str_replace('_', ' ', $tableName);
        $modelName = ucwords($modelName);
        $modelName = str_replace(' ', '', $modelName);

        return Str::singular($modelName);
    }

    private static function snakeToCamel($input, $capitalizeFirstCharacter = true): array|string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }
        return $str;
    }

    private static function formatColumnLabel(string $label): string
    {
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $label);
    }
}
