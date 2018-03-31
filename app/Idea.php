<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
     protected $fillable = [
       'user_id', 'title', 'description','idea_category_id','image_path','collabolator_requirements','is_private','funding_required','latitude','longitude','expired_date'
    ];

    public function category()
    {
        return $this->belongsTo('App\Idea_category','idea_category_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
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

     public function fundings()
    {
        return $this->hasMany('App\Funding');
    }


    public function discussions()
    {
        return $this->hasMany('App\Discussion')->orderBy('created_at','DESC')->limit(3);
    }

}
