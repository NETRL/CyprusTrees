<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    /** @use HasFactory<\Database\Factories\NeighborhoodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'district',
        'geom_ref',
    ];
    
    public function trees()
    {
        return $this->hasMany(Tree::class);
    }
}
