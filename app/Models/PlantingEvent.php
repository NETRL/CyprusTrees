<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantingEvent extends Model
{
    /** @use HasFactory<\Database\Factories\PlantingEventFactory> */
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'campaign_id',
        'planted_by',
        'planted_at',
        'method',
        'notes',
    ];

    protected $casts = [
        'planted_at' => 'datetime',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function planter()
    {
        return $this->belongsTo(User::class, 'planted_by', 'id');
    }
}
