<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceTypeFactory> */
    use HasFactory;
    protected $fillable = ['name'];

    public function events()
    {
        return $this->hasMany(MaintenanceEvent::class);
    }
}
