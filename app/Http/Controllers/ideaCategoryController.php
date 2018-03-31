<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Idea_category;

class ideaCategoryController extends Controller
{
    public function index(){

    	$category = Idea_category::all();
    	return response()->json($category);

    }

}
