<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use App\FirebaseToken;
use Hash;
use Validator;
 

class AuthenticateController extends Controller
{
    public function __construct(User $user,FirebaseToken $firebaseToken){
    	$this->user = $user;
        $this->firebaseToken = $firebaseToken;
    }

    public function login(Request $request){
    	$credentials = $request->only(['email','password']);

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'Check your internet connection then try again']);
        }

    	if(!$token = JWTAuth::attempt($credentials)){

    		return response()->json(['error' => true,'message' => 'Invalid Credentials']);

    	}

    	$user = User::where('email',$request->input('email'))->first();
        $fbase = FirebaseToken::where('token',$request->input('token'))->first();
        $input = [
            'user_id' => $user->id,
            'token' => $request->input('token')
        ];
        if(empty($fbase)){
            try{
                $this->firebaseToken->create($input);
            }catch(\Exception $e){
                return response()->json(['error' => true, 'message' => 'There is error in server']);
            }
        }else{
            $fbase->user_id = $user->id;
            $fbase->save();
        }

    	return response()->json(['error' => false,'message' => 'Login Success', 'token' => $token ,'user' => $user ]);
    }

    public function register(Request $request){
    	$credentials = $request->only(['username', 'email', 'password','birthdate','phone','city_id']);

    	$credentials = [
    		'username' => $credentials["username"],
    		'email' => $credentials["email"],
    		'birthdate' => $credentials["birthdate"],
    		'phone' => $credentials["phone"],
    		'city_id' => $credentials["city_id"],
    		'password' => Hash::make($credentials['password'])

    	];

    	try{
    		$user = $this->user->create($credentials);
    	}catch(\Exception $e){
    		return response()->json(['error' => true, 'message' => 'User Already exist']);
    	}

    	return response()->json(['error' => false, 'message' => 'User registration success. now try to login']);

    }

   
}
