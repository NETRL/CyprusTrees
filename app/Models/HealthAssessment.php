<?php

namespace App\Models;

use App\Enums\HealthStatus;
use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthAssessment extends Model
{
    /** @use HasFactory<\Database\Factories\HealthAssesmentFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'assessment_id';

    protected $appends = ['id'];

    protected $fillable = [
        'tree_id',
        'assessed_by',
        'assessed_at',
        'health_status',
        'pests_diseases',
        'risk_score',
        'actions_recommended',
    ];

    protected $tableColumns = [
        'assessment_id',
        'tree_id',
        'assessed_by',
        'assessed_at',
        'health_status',
        'pests_diseases',
        'risk_score',
        'actions_recommended',
    ];

    protected $searchable = [
        'assessment_id',
        'tree_id',
        'assessed_by',
        'assessed_at',
        'health_status',
        'pests_diseases',
        'risk_score',
        'actions_recommended',

        'tree.address',
        'tree.species.common_name',
        'tree.species.latin_name',
        'tree.tags.name',
        'assessor.first_name',
        'assessor.last_name',
    ];

    protected $sortable = [
        'assessment_id',
        'tree_id',
        'assessed_by',
        'assessed_at',
        'health_status',
        'pests_diseases',
        'risk_score',
        'actions_recommended',
    ];

    protected $dateFilterable = [
        'assessed_at',
    ];

    public static function relationships(): array
    {
        return [
            'tree',
            'assessor',
        ];
    }

    protected $casts = [
        'assessed_at' => 'datetime',
        'risk_score'  => 'float',
        'health_status' => HealthStatus::class,
    ];

    public function getIdAttribute()
    {
        return $this->attributes['assessment_id'] ?? null;
    }

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by', 'id');
    }

    public static function getHealthStatusOptions(): array
    {
        return HealthStatus::options();
    }
}
