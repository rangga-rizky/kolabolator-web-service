<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Funding extends Model
{
    protected $fillable = ['user_id', 'idea_id','amount','with_phone'];

    public function idea()
    {
        return $this->belongsTo('App\Idea');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    

}
