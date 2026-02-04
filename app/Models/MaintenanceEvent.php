<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceEvent extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceEventFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'event_id';

    protected $appends = ['id'];

    protected $fillable = [
        'tree_id',
        'type_id',
        'performed_by',
        'performed_at',
        'quantity',
        'cost',
        'notes',
    ];

    protected $tableColumns = [
        'event_id',
        'tree_id',
        'tree.address',
        'type_id',
        'performed_by',
        'performed_at',
        'quantity',
        'cost',
        'notes',

    ];
    protected $searchable = [
        'event_id',
        'tree_id',
        'type_id',
        'performed_by',
        'quantity',
        'cost',
        'notes',

        'type.name',
        'tree.address',
        'tree.species.common_name',
        'tree.species.latin_name',
        'tree.tags.name',
        'performer.first_name',
        'performer.last_name',
    ];

    protected $sortable = [
        'event_id',
        'tree_id',
        'type_id',
        'performed_by',
        'performed_at',
        'quantity',
        'cost',
        'notes',
        'tree.address',
    ];

    protected array $dateFilterable = [
        'performed_at',
    ];

    public static function relationships(): array
    {
        return [
            'tree',
            'type',
            'performer',
        ];
    }

    protected $casts = [
        'performed_at' => 'datetime',
        'quantity'     => 'float',
        'cost'         => 'float',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['event_id'] ?? null;
    }

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function type()
    {
        return $this->belongsTo(MaintenanceType::class, 'type_id', 'type_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }

    public function performedAtIn(string $tz)
    {
        return $this->performed_at?->clone()->timezone($tz);
    }
}
