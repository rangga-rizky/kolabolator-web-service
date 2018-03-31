<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Discussion;
use JWTAuth;
use Validator;
use App\FirebaseToken;
use App\Member;
use App\Notification;
use App\Idea;
use App\Abstracts\Transformers\DiscussionTransformer;
use App\Abstracts\Transformers\DiscussionWithCommentTransformer;

class DiscussionController extends Controller
{
	protected $discussionTransformer,$discussionWithCommentTransformer;
    public function __construct(Discussion $discussion,Notification $notification ,DiscussionTransformer $discussionTransformer,DiscussionWithCommentTransformer $discussionWithCommentTransformer){
    	$this->discussion = $discussion;
        $this->notification = $notification;
    	$this->discussionTransformer = $discussionTransformer;
    	$this->discussionWithCommentTransformer = $discussionWithCommentTransformer;
    }

     public function store(Request $request){
    	$user = JWTAuth::toUser(JWTAuth::getToken());
    	$validator = Validator::make($request->all(), [
            'idea_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Your data is incomplete']);
        }

        $idea = Idea::where("id",$request["idea_id"])->first();
        $members = $idea->members;
        $input = [
    		'idea_id' => $request["idea_id"],
    		'text' => $request["text"],
    		'user_id' => $user->id,
    	];

         $msg = array
        (
            'message'   => "Ada diskusi terbaru di ".$idea->title,
        );

        foreach ($members as $member) {
            $notifInput = [
                'text' => "Ada diskusi terbaru di ".$idea->title,
                'action' => 3,
                'phone' => null,
                'helper_id' => $idea->id,
                'user_id' => $member->user_id,
            ];

            $fbaseTokens = FirebaseToken::where("user_id",$member->user_id)->get();
            foreach ($fbaseTokens as $fbaseToken) {
                $this->sendNotif($msg,$fbaseToken["token"]);
            }

            $this->notification->create($notifInput);
        }

        try{    		
    		$this->discussion->create($input);
    	}catch(\Exception $e){
    		//dd($e->getMessage());
    		return response()->json(['error' => true, 'message' => 'There is problem on server']);
    	}

    	return response()->json(['error' => false, 'message' => 'Discussion success created']);
  
    }

    public function indexByIdea($idea_id){

    	$discussions = Discussion::where('idea_id',$idea_id)->orderBy('updated_at','DESC')->get();                
    	return response()->json($this->discussionTransformer->transformCollection($discussions));
    }

    public function show($id){
        
        $user = JWTAuth::toUser(JWTAuth::getToken());
    	$discussion = Discussion::where('id',$id)->first();
    	if(empty($discussion)){
    		return response()->json(['error' => true, 'message' => 'This Discussion does not exist']);
    	}

        $member = Member::where("idea_id",$discussion->idea_id)->where("user_id",$user->id)->first();       

        if(empty($member)){ //cek apakah member?
            $discussion["is_member"] = false;
        }else{
            $discussion["is_member"] = true;
        }

    	return response()->json($this->discussionWithCommentTransformer->transform($discussion));

    }

     private function sendNotif($msg,$token){
        $apikey = 'AIzaSyDpLD78kq18qVa1h1tV1Ouw8r29dls9ObA';
        $token = array($token);
        $fields = array
        (
            'registration_ids'  => $token,
            'data'          => $msg
        );

        $headers = array
        (
            'Authorization: key=' . $apikey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'fcm.googleapis.com/fcm/send'  );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
         curl_close( $ch );
    }
}
