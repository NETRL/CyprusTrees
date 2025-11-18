<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $fillable = [
        'name',
        'sponsor',
        'start_date',
        'end_date',
        'notes',
    ];


    protected array  $searchable = [
        'id',
        'name',
        'sponsor',
        'start_date',
        'end_date',
        'notes',
    ];

    protected array $sortable = [
        'id',
        'name',
        'sponsor',
        'start_date',
        'end_date',
        'notes',
    ];


    public static function relationships(): array
    {
        return [
            'plantingEvents',
        ];
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];


    public function plantingEvents()
    {
        return $this->hasMany(PlantingEvent::class);
    }
}
