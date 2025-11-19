<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceTypeFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'type_id';

    protected $appends = ['id'];

    protected $fillable = ['name'];

    protected $tableColumns = [
        'type_id',
        'name',
        'events_count'
    ];

    protected $searchable = [
        'type_id',
        'name',
    ];

    protected $sortable = [
        'type_id',
        'name',
        'events_count'
    ];

    public static function relationships(): array
    {
        return [
            'events',
        ];
    }

    public function getIdAttribute(): ?int
    {
        return $this->attributes['type_id'] ?? null;
    }


    public function events()
    {
        return $this->hasMany(MaintenanceEvent::class, 'type_id', 'type_id');
    }
}
