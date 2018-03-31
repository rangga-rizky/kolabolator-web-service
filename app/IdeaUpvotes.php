<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdeaUpvotes extends Model
{
    protected $table = "idea_upvotes";
    public $timestamps = false;
    protected $fillable = ['user_id', 'idea_id'];

     public function user()
    {
        return $this->belongsTo('App\User');
    }

     public function idea()
    {
        return $this->belongsTo('App\Idea');
    }
}
