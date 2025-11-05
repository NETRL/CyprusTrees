<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreeTag extends Model
{
    /** @use HasFactory<\Database\Factories\TreeTagFactory> */
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'tag_id',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_id', 'tree_id');
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'tag_id');
    }
}
