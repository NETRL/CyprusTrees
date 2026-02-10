<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GisLayer extends Model
{
    use SoftDeletes, BaseModelTrait, Paginatable;

    protected $fillable = [
        'key',
        'name',
        'display_name',
        'category',
        'source',
        'default_import_mode',
        'active_revision_id',
        'is_editable',
        'is_active',
        'metadata',
    ];

    protected array  $tableColumns = [
        'id',
        'display_name',
        'name',
        'key',
        'category',
        'source',
        'default_import_mode',
        'active_revision_id',
        'is_editable',
        'is_active',
        'metadata',
    ];

    protected $searchable = [
        'display_name',
        'name',
        'key',
        'category',
        'source',
        'default_import_mode',
        'active_revision_id',
        'is_editable',
        'is_active',
        'metadata',
    ];

    protected $sortable = [
        'display_name',
        'name',
        'key',
        'category',
        'source',
        'default_import_mode',
        'active_revision_id',
        'is_editable',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(GisRevision::class, 'layer_id');
    }

    public function includeRevisions(): HasMany
    {
        return $this->revisions()
            ->where('status', 'completed')
            ->where('is_included', true)
            ->whereNull('deleted_at');
    }

    public function activeRevision(): BelongsTo
    {
        return $this->belongsTo(GisRevision::class, 'active_revision_id');
    }

    public function features()
    {
        return $this->hasMany(GisFeature::class, 'layer_id');
    }
}
