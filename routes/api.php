<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login','AuthenticateController@login');
Route::post('register','AuthenticateController@register');
Route::get('idea_category','ideaCategoryController@index');
Route::get('provinces','AreaController@getProvincies');
Route::get('provinces/{id}/city','AreaController@getCitybyProvince');

Route::group(['prefix' => 'v1','middleware' => ['jwt.auth']],function(){
	//Idea
	Route::get('ideas','IdeaController@index');
	
	Route::get('ideas/user','IdeaController@indexByUser');
	Route::get('ideas/{id}','IdeaController@show');
	Route::post('ideas','IdeaController@store');

	//MemberRequest
	Route::post('member_requests','MemberRequestController@store');
	Route::post('member_requests/accept','MemberRequestController@accept');
	Route::post('member_requests/reject','MemberRequestController@reject');

	//discussion
	Route::post('discussions','DiscussionController@store');
	Route::get('discussions/{id}','DiscussionController@show');
	Route::get('discussions/idea/{idea_id}','DiscussionController@indexByIdea');

	//commetn
	Route::post('comments','CommentController@store');

	//commetn
	Route::post('upvotes','UpvoteController@store');

	//user
	Route::get('users','UserController@show');
	Route::get('users/{id}','UserController@showById');

	//funding
	Route::post('fundings','FundingController@store');
	Route::get('fundings','FundingController@index');
	Route::get('fundings/{id}','FundingController@show');

	//notif
	Route::get('notifications','NotificationController@index');
});
