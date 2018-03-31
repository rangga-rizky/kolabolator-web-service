<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{	
	protected $fillable = ['user_id', 'idea_id', 'text'];

    public function idea()
    {
        return $this->belongsTo('App\Idea');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

     public function comments()
    {
        return $this->hasMany('App\Comment');
    }
}
