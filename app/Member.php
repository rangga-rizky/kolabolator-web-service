<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    
	public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

     public function idea()
    {
        return $this->belongsTo('App\Idea');
    }
}
