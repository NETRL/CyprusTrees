<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    /** @use HasFactory<\Database\Factories\ReportTypeFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $fillable = ['name'];

    protected $tableColumns = [
        'id',
        'name',
        'reports_count'
    ];

    protected $searchable = [
        'id',
        'name'
    ];

    protected $sortable = [
        'id',
        'name',
        'reports_count'
    ];


    public static function relationships(): array
    {
        return [
            'reports'
        ];
    }

    public function reports()
    {
        return $this->hasMany(CitizenReport::class);
    }
}
