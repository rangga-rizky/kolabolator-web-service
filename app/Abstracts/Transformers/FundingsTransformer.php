<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;

class FundingsTransformer extends Transformer {

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

        
        if($totalFunding == 0){
            $persen = 0;
        }else{
            $persen = number_format((float)$totalFunding/$data['funding_required'] * 100, 0, '.', '');

        }       

        return [
            'id' => $data['id'],
            'title' => $data['title'],
            'funding_fulfilled' => $persen,            
            'image_path' => $images,
            'is_private' => $data['is_private'],
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
