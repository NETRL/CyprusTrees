<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $fillable = [
        'tree_id',
        'url',
        'caption',
        'captured_at',
        'source',
        'path',     
        'status',  
        'error_message',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_id', 'tree_id');
    }
}
