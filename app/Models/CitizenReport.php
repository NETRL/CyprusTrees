<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CitizenReport extends Model
{
    /** @use HasFactory<\Database\Factories\CitizenReportFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'report_id';

    public $timestamps = false;

    protected $appends = ['tree_label', 'description_preview'];


    protected $fillable = [
        'report_type_id',
        'created_by',
        'tree_id',
        'photo_id',
        'lat',
        'lon',
        'description',
        'status',
        'created_at',
        'resolved_at',
    ];

    protected $searchable = [
        'report_type_id',
        'created_by',
        'tree_id',
        'photo_id',
        'lat',
        'lon',
        'description',
        'status',
        'created_at',
        'resolved_at',

        'type.name',
        'photo.id',
        'photo.caption',
        'tree.address',
        'tree.species.common_name',
        'tree.species.latin_name',
        'tree.tags.name',
        'creator.first_name',
        'creator.last_name',
    ];

    protected $sortable = [
        'report_type_id',
        'created_by',
        'tree_id',
        'photo_id',
        'lat',
        'lon',
        'description',
        'status',
        'created_at',
        'resolved_at',
    ];

    protected $dateFilterable = [
        'created_at',
        'resolved_at',
    ];


    public static function relationships(): array
    {
        return [
            'type',
            'creator',
            'tree',
            'photo',
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
        return $this->belongsTo(ReportType::class, 'report_type_id', 'id');
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



    public function getTreeLabelAttribute(): string
    {
        if (!$this->tree_id) return '-';
        if (!$this->relationLoaded('tree') || !$this->tree) return (string) $this->tree_id;

        $species = $this->tree->species;
        $common  = $species?->common_name ?? '';
        $latin   = $species?->latin_name ?? '';
        $tags    = $this->tree->tags_label ?? '';

        $label = trim($this->tree_id . ' - ' . $common . ' (' . $latin . ') ' . $tags);
        return $label !== '' ? $label : (string) $this->tree_id;
    }

    public function getDescriptionPreviewAttribute(): string
    {
        $text = (string)($this->description ?? '');
        $text = preg_replace('/\s+/u', ' ', trim($text)); // collapse whitespace
        return $text === '' ? '-' : Str::limit($text, 140); // tune length
    }
}
