<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notification;
use App\Abstracts\Transformers\NotifTransformer;
use JWTAuth;

class NotificationController extends Controller
{
	protected $notifTransformer;
    public function __construct(Notification $notification,NotifTransformer $notifTransformer){
    	$this->notification = $notification;
        $this->notifTransformer = $notifTransformer;
    }

    public function index(){
       $user = JWTAuth::toUser(JWTAuth::getToken()); 
       $notifications = Notification::where("user_id",$user->id)->orderBy("created_at","DESC")->get();

       return response()->json($this->notifTransformer->transformCollection($notifications));	
    }

}
