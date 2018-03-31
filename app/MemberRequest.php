<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberRequest extends Model
{
    protected $table = "member_requests";
    protected $fillable = ['user_id', 'idea_id', 'notes','phone_request'];

     public function user()
    {
        return $this->belongsTo('App\User');
    }

     public function idea()
    {
        return $this->belongsTo('App\Idea');
    }

}
