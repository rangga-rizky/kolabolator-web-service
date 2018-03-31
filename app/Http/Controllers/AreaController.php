<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\Province;

class AreaController extends Controller
{
    //
    public function getProvincies(){

    	$provinces = Province::all();
    	return response()->json($provinces);

    }


    public function getCitybyProvince($province_id){

    	$cities = City::where('province_id',$province_id)->get();
    	return response()->json($cities);
    }
}
