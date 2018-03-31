<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'username', 'email', 'password','birthdate','phone','city_id','description','current_job',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function ideas()
    {
        return $this->hasMany('App\Idea');
    }

     public function members()
    {
        return $this->hasMany('App\Member');
    }

     public function memberRequests()
    {
        return $this->hasMany('App\MemberRequest');
    }

      public function upvotes()
    {
        return $this->hasMany('App\IdeaUpvotes');
    }

    public function discussions()
    {
        return $this->hasMany('App\Discussion');
    }

      public function fundings()
    {
        return $this->hasMany('App\Funding');
    }


    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

     public function notifications()
    {
        return $this->hasMany('App\Notification');
    }

    public function city()
    {
        return $this->belongsTo('App\City');
    }
}
