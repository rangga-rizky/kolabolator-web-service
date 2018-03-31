<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Member;
use JWTAuth;
use App\Abstracts\Transformers\ProfilTransformer;

class UserController extends Controller
{
    public function __construct(User $user,ProfilTransformer $profilTransformer){
    	$this->user = $user;
    	$this->profilTransformer = $profilTransformer;
    }

     public function show(){
     	$user = JWTAuth::toUser(JWTAuth::getToken()); 
     	$collaboration_on = Member::where('user_id',$user->id)->count();
     	$user["collaboration_on"] = $collaboration_on;

     	return response()->json($this->profilTransformer->transform($user));
     }

      public function showById($id){
        $user = User::where('id',$id)->first();
        $collaboration_on = Member::where('user_id',$id)->count();
        $user["collaboration_on"] = $collaboration_on;
        $user["balance"] = "-";
        return response()->json($this->profilTransformer->transform($user));
     }
}
