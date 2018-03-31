<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'text','action','helper_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
