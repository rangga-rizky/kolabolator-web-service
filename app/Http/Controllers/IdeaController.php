<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Idea;
use App\Member;
use App\IdeaUpvotes;
use App\MemberRequest;
use JWTAuth;
use DB;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use App\Abstracts\Transformers\IdeasTransformer;
use App\Abstracts\Transformers\IdeaTransformer;
use App\Abstracts\Transformers\FundingsTransformer;
use Validator;

class IdeaController extends Controller
{

    protected $ideasTransformer,$ideaTransformer,$fundingsTransformer;

    public function __construct(Idea $idea ,IdeasTransformer $ideasTransformer,IdeaTransformer $ideaTransformer,FundingsTransformer $fundingsTransformer){
    	$this->idea = $idea;
        $this->ideasTransformer = $ideasTransformer;
        $this->ideaTransformer = $ideaTransformer;
        $this->fundingsTransformer = $fundingsTransformer;
    }

    public function index(Request $request){

        if($request->category_id == 0){ // tidak memakai filter

            switch (strtoupper($request->sortBy)) {
                case 'NEWEST':                    
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->orderBy('updated_at','DESC')->get();                    break;
                
                case 'UPVOTES':
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->orderBy('number_of_upvotes','DESC')->get();
                    break;

                case 'LOCATION':

                    $location = explode(',', $request->location);
                    $ideas = Idea::select('*',DB::raw('( 6371 * acos( cos( radians('.$location[0].') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$location[1].') ) + sin( radians('.$location[0].') ) * sin( radians( latitude ) ) ) ) AS distance '))
                     ->whereDate('expired_date', '>=', Carbon::today())
                     ->orderBy('distance')->get();                    break;
                
                default:
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->orderBy('updated_at','DESC')->get();
                    break;
            }

        }else{
            switch (strtoupper($request->sortBy)) {
                case 'NEWEST':                    
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->where('idea_category_id',$request->category_id)->orderBy('updated_at','DESC')->get();                    break;
                
                case 'UPVOTES':
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->where('idea_category_id',$request->category_id)->orderBy('number_of_upvotes','DESC')->get();
                    break;

                case 'LOCATION':

                    $location = explode(',', $request->location);
                    $ideas = Idea::select('*',DB::raw('( 6371 * acos( cos( radians('.$location[0].') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$location[1].') ) + sin( radians('.$location[0].') ) * sin( radians( latitude ) ) ) ) AS distance '))
                     ->where('idea_category_id',$request->category_id)
                     ->whereDate('expired_date', '>=', Carbon::today())
                     ->orderBy('distance')->get();                    break;
                
                default:
                    $ideas = Idea::whereDate('expired_date', '>=', Carbon::today())->where('idea_category_id',$request->category_id)->orderBy('updated_at','DESC')->get();
                    break;
            }

        }

        return response()->json($this->ideasTransformer->transformCollection($ideas));

    }

     public function indexByUser(Request $request){

        $user = JWTAuth::toUser(JWTAuth::getToken()); 
        if($request->category_id == 0){ // tidak memakai filter

            switch (strtoupper($request->sortBy)) {
                case 'NEWEST':                    
                    $ideas = Idea::orderBy('updated_at','DESC')->where('user_id',$user->id)->get();                    break;
                
                case 'UPVOTES':
                    $ideas = Idea::orderBy('number_of_upvotes','DESC')->where('user_id',$user->id)->get();
                    break;

                case 'LOCATION':

                    $location = explode(',', $request->location);
                    $ideas = Idea::select('*',DB::raw('( 6371 * acos( cos( radians('.$location[0].') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$location[1].') ) + sin( radians('.$location[0].') ) * sin( radians( latitude ) ) ) ) AS distance '))
                     ->orderBy('distance')->where('user_id',$user->id)->get();                    break;
                
                default:
                    $ideas = Idea::orderBy('updated_at','DESC')->where('user_id',$user->id)->get();
                    break;
            }

        }else{
            switch (strtoupper($request->sortBy)) {
                case 'NEWEST':                    
                    $ideas = Idea::where('idea_category_id',$request->category_id)->orderBy('updated_at','DESC')->where('user_id',$user->id)->get();                    break;
                
                case 'UPVOTES':
                    $ideas = Idea::where('idea_category_id',$request->category_id)->orderBy('number_of_upvotes','DESC')->where('user_id',$user->id)->get();
                    break;

                case 'LOCATION':

                    $location = explode(',', $request->location);
                    $ideas = Idea::select('*',DB::raw('( 6371 * acos( cos( radians('.$location[0].') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$location[1].') ) + sin( radians('.$location[0].') ) * sin( radians( latitude ) ) ) ) AS distance '))
                     ->where('idea_category_id',$request->category_id)
                     ->orderBy('distance')->where('user_id',$user->id)->get();                    break;
                
                default:
                    $ideas = Idea::where('idea_category_id',$request->category_id)->orderBy('updated_at','DESC')->where('funding_required','>',0)->where('user_id',$user->id)->get();
                    break;
            }

        }

        return response()->json($this->fundingsTransformer->transformCollection($ideas));

    }

   

    public function store(Request $request){

    	$user = JWTAuth::toUser(JWTAuth::getToken());    	

    	$validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Your data is incomplete']);
        }

        $image_path = null;
        if ($request->hasFile('images')) {
        	$image = $request->file('images');
        	$name = time().'.'.$image->getClientOriginalExtension();

        	$image_resize = Image::make($image->getRealPath());              
   			$image_resize->resize(300, 300);
    		$image_resize->save(public_path('images/small/' .$name));

       		$destinationPath = public_path('/images/large');
       		$image->move($destinationPath, $name);
       		$image_path = $name;

        }

        $input = $request->only(['title', 'description', 'idea_category_id','collabolator_requirements','is_private','funding_required','latitude','longitude','expired_date']);

    	$input = [
    		'title' => $input["title"],
    		'description' => $input["description"],
    		'idea_category_id' => $input["idea_category_id"],
    		'user_id' => $user->id,
    		'image_path' => $image_path,
    		'collabolator_requirements' => $input["collabolator_requirements"],
    		'is_private' => $input["is_private"],
    		'funding_required' => $input['funding_required'],
    		'latitude' => $input['latitude'],
    		'longitude' => $input['longitude'],
    		'expired_date' => $input['expired_date'],

    	];

    	try{    		
    		$this->idea->create($input);
    	}catch(\Exception $e){
    		return response()->json(['error' => true, 'message' => 'There is problem on server']);
    	}

    	return response()->json(['error' => false, 'message' => 'Ideas success created']);

    }

    //cek apakah expired? private? ,member?,sudah upvote? ,cek apakah sudah mengirim request?
    public function show($id){

        $idea = Idea::where("id",$id)->first();
        $user = JWTAuth::toUser(JWTAuth::getToken()); 

        if(empty($idea)){
            return response()->json(['error' => true, 'message' => 'This Idea not Found']);
        }

        if($idea->user_id == $user->id){ //cek apakah miliknya
            $idea["is_mine"] = true;
        }else{
            $idea["is_mine"] = false;
        }

        if($idea->expired_date < date("Y-m-d H:i:s")){ //cek expired
            return response()->json(['error' => true, 'message' => 'This idea was expired']);
        }

        $upvotes = IdeaUpvotes::where("idea_id",$id)->where("user_id",$user->id)->first();
        $member = Member::where("idea_id",$id)->where("user_id",$user->id)->first();
        $onRequest = MemberRequest::where("idea_id",$id)->where("user_id",$user->id)->where("status","WAITING")->first();

        if(empty($onRequest)){ //cek sudah request join?
            $idea["on_request"] = false;
        }else{
            $idea["on_request"] = true;
        }

        if(empty($upvotes)){ //cek sudah upvote?
            $idea["already_upvotes"] = false;
        }else{
            $idea["already_upvotes"] = true;
        }

        if(empty($member)){ //cek apakah member?
            $idea["is_member"] = false;
        }else{
            $idea["is_member"] = true;
        }

        if($idea->is_private){  //cek private

            if(!$idea["is_member"] && !$idea["is_mine"]){
                $idea["description"] = "Anda harus ikut berkolaborasi untuk dapat mengakses ini";
                $idea["discussions"] = [];
                return response()->json($this->ideaTransformer->transform($idea));

            }else{
                return response()->json($this->ideaTransformer->transform($idea));
            }

        }else{
            return response()->json($this->ideaTransformer->transform($idea));
        }

    }

}
