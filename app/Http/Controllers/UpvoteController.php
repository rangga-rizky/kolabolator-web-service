<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\IdeaUpvotes;
use JWTAuth;
use Validator;


class UpvoteController extends Controller
{
     public function __construct(IdeaUpvotes $ideaUpvotes){
    	$this->ideaUpvotes = $ideaUpvotes;
    }

    public function store(Request $request){
    	$user = JWTAuth::toUser(JWTAuth::getToken());

    	$validator = Validator::make($request->all(), [
            'idea_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Your data is incomplete']);
        }

        $upvoted = IdeaUpvotes::where('idea_id',$request["idea_id"])->where('user_id',$user->id)->first();
        if(!empty($upvoted)){
        	return response()->json(['error' => true, 'message' => 'You are already upvoted']);
        }


        $input = [
    		'idea_id' => $request["idea_id"],
    		'user_id' => $user->id,
    	];

        try{    		
    		$this->ideaUpvotes->create($input);
    	}catch(\Exception $e){
            //dd($e->getMessage());
    		return response()->json(['error' => true, 'message' => 'There is problem on server']);
    	}

    	return response()->json(['error' => false, 'message' => 'upvoted']);
  

    }
}
