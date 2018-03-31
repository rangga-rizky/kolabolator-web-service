<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;

class FundingTransformer extends Transformer {

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

        $totalFunding = 0;

        foreach ($data->fundings as $funding) {
           $totalFunding = $totalFunding + $funding["amount"];
        }

        
         

        return [
            'id' => $data['id'],
            'title' => $data['title'],
            'funding_needed' => $data['funding_required'] - $totalFunding,  
            'funding_required' => $data['funding_required'],  
            'category_name' => $data->category["name"],             
            'number_of_members' => sizeof($data->members),              
            'user_city' => $data->user->city['name'],   
            'image_path' => $images,
            'user_id' => $data->user['id'],
            'user_name' => $data->user['username'],
            'user_avatar' => $avatar,
            'number_of_upvotes' => $data['number_of_upvotes'],
            'expired_date' => 'expired on '.Carbon::parse($data['expired_date'])->format('l, d M Y'),
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
            'location' => $data['latitude'].','.$data['longitude'],
        ];
    }
}
