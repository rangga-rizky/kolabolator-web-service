<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;
use App\Discussion;
use App\Member;
use JWTAuth;
use Validator;

class CommentController extends Controller
{
    public function __construct(Comment $comment ){
    	$this->comment = $comment;
    }

    public function store(Request $request){
    	$user = JWTAuth::toUser(JWTAuth::getToken());

    	$validator = Validator::make($request->all(), [
            'discussion_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Your data is incomplete']);
        }


        $discussion = Discussion::where("id", $request["discussion_id"])->first();
        $member = Member::where("idea_id", $discussion->idea_id)->where("user_id",$user->id)->first();
        if(empty($member)){ 
            return response()->json(['error' => true, 'message' => 'You are not allowed to comment here']);
        }

        $input = [
    		'discussion_id' => $request["discussion_id"],
    		'comment' => $request["comment"],
    		'user_id' => $user->id,
    	];

        try{    		
    		$this->comment->create($input);
    	}catch(\Exception $e){
    		//dd($e->getMessage());
    		return response()->json(['error' => true, 'message' => 'There is problem on server']);
    	}

    	return response()->json(['error' => false, 'message' => 'Comment success created']);
  
    }
}
