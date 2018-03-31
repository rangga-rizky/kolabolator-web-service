<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Idea;
use App\Funding;
use App\Notification;
use App\User;
use App\FirebaseToken;
use JWTAuth;
use App\Abstracts\Transformers\FundingsTransformer;
use App\Abstracts\Transformers\FundingTransformer;
use Validator;
use Carbon\Carbon;
use DB;

class FundingController extends Controller
{
	 protected $fundingsTransformer,$fundingTransformer;
    public function __construct(Funding $funding,Notification $notification, FundingsTransformer $fundingsTransformer,FundingTransformer $fundingTransformer){
    	$this->funding = $funding;                
        $this->notification = $notification;        
        $this->fundingTransformer = $fundingTransformer;
    	$this->fundingsTransformer = $fundingsTransformer;
    }

     public function index(Request $request){

        $user = JWTAuth::toUser(JWTAuth::getToken()); 
        if($request->category_id == 0){ // tidak memakai filter

            switch (strtoupper($request->sortBy)) {
                case 'NEWEST':                    
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->orderBy('updated_at','DESC')->where('funding_required','>',0)->where('user_id','!=',$user->id)->whereRaw('funding_required > funding_gived')->get();                    break;
                
                case 'UPVOTES':
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->orderBy('number_of_upvotes','DESC')->where('funding_required','>',0)->where('user_id','!=',$user->id)->whereRaw('funding_required > funding_gived')->get();
                    break;

                case 'LOCATION':

                    $location = explode(',', $request->location);
                    $ideas = Idea::select('*',DB::raw('( 6371 * acos( cos( radians('.$location[0].') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$location[1].') ) + sin( radians('.$location[0].') ) * sin( radians( latitude ) ) ) ) AS distance '))
                     ->whereDate('expired_date', '>=', Carbon::today())
                     ->orderBy('distance')->where('funding_required','>',0)->whereRaw('funding_required > funding_gived')->where('user_id','!=',$user->id)->get();                    break;
                
                default:
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->orderBy('updated_at','DESC')->where('user_id','!=',$user->id)->whereRaw('funding_required > funding_gived')->get();
                    break;
            }

        }else{
            switch (strtoupper($request->sortBy)) {
                case 'NEWEST':                    
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->where('idea_category_id',$request->category_id)->orderBy('updated_at','DESC')->where('funding_required','>',0)->where('user_id','!=',$user->id)->whereRaw('funding_required > funding_gived')->get();                    break;
                
                case 'UPVOTES':
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->where('idea_category_id',$request->category_id)->orderBy('number_of_upvotes','DESC')->where('funding_required','>',0)->whereRaw('funding_required > funding_gived')->where('user_id','!=',$user->id)->get();
                    break;

                case 'LOCATION':

                    $location = explode(',', $request->location);
                    $ideas = Idea::select('*',DB::raw('( 6371 * acos( cos( radians('.$location[0].') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$location[1].') ) + sin( radians('.$location[0].') ) * sin( radians( latitude ) ) ) ) AS distance '))
                     ->where('idea_category_id',$request->category_id)
                     ->whereRaw('funding_required > funding_gived')
                     ->whereDate('expired_date', '>=', Carbon::today())
                     ->orderBy('distance')->where('funding_required','>',0)->where('user_id','!=',$user->id)->get();                    break;
                
                default:
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->where('idea_category_id',$request->category_id)->orderBy('updated_at','DESC')->where('funding_required','>',0)->where('user_id','!=',$user->id)->whereRaw('funding_required > funding_gived')->get();
                    break;
            }

        }

        return response()->json($this->fundingsTransformer->transformCollection($ideas));

    }


    public function show($id){
        $idea = Idea::where("id",$id)->first();
        $user = JWTAuth::toUser(JWTAuth::getToken()); 

        if(empty($idea)){
            return response()->json(['error' => true, 'message' => 'This Funding not Found']);
        }

        return response()->json($this->fundingTransformer->transform($idea));


    }

     public function store(Request $request){

        $user = JWTAuth::toUser(JWTAuth::getToken());  
        $validator = Validator::make($request->all(), [
            'idea_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Your data is incomplete']);
        }

        $input = $request->only(['idea_id', 'amount', 'with_phone']);

        $input = [
            'idea_id' => $input["idea_id"],
            'amount' => $input["amount"],
            'with_phone' => $input["with_phone"],
            'user_id' => $user->id,
        ];
        $idea = Idea::where("id",$input["idea_id"])->first(); 
        $firebaseTokens = FirebaseToken::where("user_id",$idea->user->id)->get();

        if($input["with_phone"] == 1){
            $text = $user->username." Memberi dana sebesar "."Rp." . number_format($input["amount"],2,',','.')." pada ide anda yang berjudul ".$idea->title." Anda dapat menghubunginya di ".$user->phone;
            $phone = $user->phone;
        }else{
            $text = $user->username." Memberi dana sebesar "."Rp." . number_format($input["amount"],2,',','.')." pada ide anda yang berjudul ".$idea->title;
            $phone = null;
        }

        $notifInput = [
            'text' => $text,
            'action' => 1,
            'user_id' => $idea->user->id,
            'phone' => $phone,
        ];

         $msg = array
        (
            'message'   => $text,
        );

        foreach ($firebaseTokens as $firebaseToken) {           
            $this->sendNotif($msg,$firebaseToken["token"]);
        }

        try{            
            $this->funding->create($input);
            $this->notification->create($notifInput);
        }catch(\Exception $e){
            return response()->json(['error' => true, 'message' => 'There is problem on server']);
        }

        return response()->json(['error' => false, 'message' => 'Funding success created']);

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
