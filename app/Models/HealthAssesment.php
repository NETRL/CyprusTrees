<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthAssesment extends Model
{
    /** @use HasFactory<\Database\Factories\HealthAssesmentFactory> */
    use HasFactory;

       protected $primaryKey = 'assessment_id'; 

    protected $fillable = [
        'tree_id',
        'assessed_by',
        'assessed_at',
        'health_status',
        'pests_diseases',
        'risk_score',
        'actions_recommended',
    ];

    protected $casts = [
        'assessed_at' => 'datetime',
        'risk_score'  => 'float',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by', 'id');
    }
}
