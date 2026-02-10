<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GisFeature extends Model
{
    protected $fillable = [
        'layer_id',
        'import_id',
        'geom_type',
        'properties',
        'source_feature_id',
        // geom is set via raw SQL
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function layer()
    {
        return $this->belongsTo(GisLayer::class, 'layer_id');
    }

    public function revision()
    {
        return $this->belongsTo(GisRevision::class, 'revision_id');
    }
}
