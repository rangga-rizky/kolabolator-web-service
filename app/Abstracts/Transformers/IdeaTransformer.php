<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;
use App\Abstracts\Transformers\DiscussionTransformer;

class IdeaTransformer extends Transformer {

    public function transform($data)
    {
        if (empty($data['image_path'])) {
            $images = null;
        } else {
            $images["small"] = url('images/small/'.$data['image_path']);
            $images["large"] = url('images/large/'.$data['image_path']);
        }

        if (empty($data->user['avatar'])) {
            $avatar = null;
        } else {
            $avatar = url('avatar/'.$data->user['avatar']);
        }
        
        if(sizeof($data->discussions) > 0){
            $data->discussions = (new DiscussionTransformer)->transformCollection($data->discussions);
        }else{
            $data->discussions = [];
        }

        return [
            'id' => $data['id'],
            'title' => $data['title'],  
            'description' => $data['description'],
            'user_id' => $data->user['id'],
            'user_name' => $data->user['username'],
            'user_avatar' => $avatar,
            'user_city' => $data->user->city['name'],
            'is_mine' => $data['is_mine'],
            'on_request' => $data['on_request'],
            'already_upvotes' => $data['already_upvotes'],
            'is_member' => $data['is_member'],
            'collabolator_requirements' => $data['collabolator_requirements'],
            'category_name' => $data->category["name"], 
            'number_of_members' => sizeof($data->members),         
            'image_path' => $images,
            'is_private' => $data['is_private'],
            'number_of_upvotes' => $data['number_of_upvotes'],
            'expired_date' => Carbon::parse($data['expired_date'])->format('l, d M Y'),
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
            'location' => $data['latitude'].','.$data['longitude'],
            //'number of_discussion' => sizeof($data->discussions),
            'discussions' => $data->discussions,
        ];
    }
}
