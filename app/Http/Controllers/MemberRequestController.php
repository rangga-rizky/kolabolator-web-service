<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MemberRequest;
use App\Idea;
use App\Notification;
use App\User;
use App\FirebaseToken;
use JWTAuth;
use Validator;

class MemberRequestController extends Controller
{
    //
     public function __construct(MemberRequest $memberRequest,Notification $notification){
    	$this->memberRequest = $memberRequest;
        $this->notification = $notification;
    }

    public function store(Request $request){
    	$user = JWTAuth::toUser(JWTAuth::getToken());


    	$validator = Validator::make($request->all(), [
            'idea_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Your data is incomplete']);
        }

        $input = [
    		'idea_id' => $request["idea_id"],
    		'notes' => $request["notes"],
            'phone_request' =>$request["phone_request"],
    		'user_id' => $user->id,
    	];

        $idea = Idea::where("id",$input["idea_id"])->first(); 
        $firebaseTokens = FirebaseToken::where("user_id",$idea->user->id)->get();

        if($input["phone_request"] == 1){
            $text = $user->username." Ingin Berkolaborasi dengan anda pada ide anda yang berjudul ".$idea->title." Anda dapat menghubunginya di ".$user->phone;
            $phone = $user->phone;
        }else{
            $text = $user->username." Ingin Berkolaborasi dengan anda pada ide anda yang berjudul ".$idea->title;
            $phone = null;
        }

         $msg = array
        (
            'message'   => $text,
        );

        foreach ($firebaseTokens as $firebaseToken) {           
            $this->sendNotif($msg,$firebaseToken["token"]);
        }

        try{    		
    		$requestMember = $this->memberRequest->create($input);

            $notifInput = [
                'text' => $text,
                'action' => 2,
                'phone' => $phone,
                'helper_id' => $requestMember->id,
                'user_id' => $idea->user->id,
            ];

            $this->notification->create($notifInput);
    	}catch(\Exception $e){
            //dd($e->getMessage());
    		return response()->json(['error' => true, 'message' => 'There is problem on server']);
    	}

    	return response()->json(['error' => false, 'message' => 'Request success created']);
  
    }

    public function accept(Request $request){
        $request = MemberRequest::where("id",$request->input("request_id"))->first();
        
        if($request->status != "WAITING"){
            return response()->json(['error' => true, 'message' => 'Anda sudah melakukan aksi pada request ini']);
        }

        $request->status = "ACCEPTED";
        $request->save();

        return response()->json(['error' => false, 'message' => 'Request Accepted']);
    }

    public function reject(Request $request){
        $request = MemberRequest::where("id",$request->input("request_id"))->first();     

        if($request->status != "WAITING"){
            return response()->json(['error' => true, 'message' => 'Anda sudah melakukan aksi pada request ini']);
        }
           $request->status = "REJECTED";
        $request->save();

        return response()->json(['error' => false, 'message' => 'Request Rejected']);
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
