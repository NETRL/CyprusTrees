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
    public $timestamps = false;

    protected $fillable = [
        'report_type_id',
        'created_by',
        'tree_id',
        'photo_id',
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
            'creator',
            'photo',
            // 'tree',
        ];
    }


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

    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }

    public static function getReportStatusOptions(): array
    {
        return ReportStatus::options();
    }
}
