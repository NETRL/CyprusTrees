<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'url',
        'caption',
        'captured_at',
        'source',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_id', 'tree_id');
    }
}
