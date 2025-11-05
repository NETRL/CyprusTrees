<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    /** @use HasFactory<\Database\Factories\SpeciesFactory> */
    use HasFactory;

      protected $fillable = [
        'latin_name',
        'common_name',
        'family',
        'drought_tolerance',
        'canopy_class',
        'notes',
    ];

     public function trees()
    {
        return $this->hasMany(Tree::class);
    }
}
