<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTree extends Model
{
    protected $fillable = [
        'tree_id',
        'user_id',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_id', 'tree_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
