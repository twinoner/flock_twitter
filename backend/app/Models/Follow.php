<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    public $timestamps = false;
    protected $fillable = ['follower_id', 'following_id'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}
