<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceEvent extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceEventFactory> */
    use HasFactory;

    protected $primaryKey = 'event_id';


    protected $fillable = [
        'tree_id',
        'type_id',
        'performed_by',
        'performed_at',
        'quantity',
        'cost',
        'notes',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'quantity'     => 'float',
        'cost'         => 'float',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function type()
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }
}
