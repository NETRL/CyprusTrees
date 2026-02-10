<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GisRevision extends Model
{
    use SoftDeletes;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_FAILED = 'failed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    public const MODE_REPLACE = 'replace';
    public const MODE_APPEND = 'append';

    protected $fillable = [
        'layer_id',
        'imported_by',
        'revision_no',
        'label',
        'import_mode',
        'status',
        'original_name',
        'original_ext',
        'stored_path',
        'features_imported',
        'feature_counts',
        'bbox',
        'centroid',
        'error',
        'activated_at',
        'archived_at',
        'is_included',
    ];


    protected $casts = [
        'feature_counts' => 'array',
        'bbox' => 'array',
        'features_imported' => 'integer',
        'is_included' => 'boolean',
        'activated_at' => 'datetime',
        'archived_at' => 'datetime',
    ];
    public function layer(): BelongsTo
    {
        return $this->belongsTo(GisLayer::class, 'layer_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function features(): HasMany
    {
        return $this->hasMany(GisFeature::class, 'revision_id');
    }

    public function scopeVisible($q)
    {
        return $q->whereNull('deleted_at');
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', self::STATUS_COMPLETED);
    }

    public function scopeIncluded($q)
    {
        return $q->where('is_included', true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_COMPLETED, self::STATUS_ARCHIVED], true);
    }
}
