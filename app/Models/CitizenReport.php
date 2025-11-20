<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitizenReport extends Model
{
    /** @use HasFactory<\Database\Factories\CitizenReportFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'report_type_id',
        'created_by',
        'tree_id',
        'lat',
        'lon',
        'description',
        'status',
        'photo_url',
        'created_at',
        'resolved_at',
    ];

    protected $tableColumns = [
        'report_id',
        'report_type_id',
        'created_by',
        'tree_id',
        'lat',
        'lon',
        'description',
        'status',
        'photo_url',
        'created_at',
        'resolved_at',
    ];

    protected $searchable = [
        'report_id',
        'report_type_id',
        'created_by',
        'tree_id',
        'lat',
        'lon',
        'description',
        'status',
        'photo_url',
        'created_at',
        'resolved_at',
    ];

    protected $sortable = [
        'report_id',
        'report_type_id',
        'created_by',
        'tree_id',
        'lat',
        'lon',
        'description',
        'status',
        'photo_url',
        'created_at',
        'resolved_at',
    ];

    public static function relationships(): array
    {
        return [
            'type',
            // 'creator',
            // 'tree',
        ];
    }

    public $timestamps = false; // we manually handle timestamps above

    protected $casts = [
        'lat'         => 'float',
        'lon'         => 'float',
        'created_at'  => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['report_id'] ?? null;
    }

    public function type()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public static function getReportStatusOptions(): array
    {
        return ReportStatus::options();
    }
}
