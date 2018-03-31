<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Idea_category extends Model
{
	protected $table = 'idea_categories';

	public function ideas()
    {
        return $this->hasMany('App\Idea');
    }
    
}
