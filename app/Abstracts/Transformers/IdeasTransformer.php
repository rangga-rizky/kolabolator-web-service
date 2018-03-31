<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;

class IdeasTransformer extends Transformer {

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

        return [
            'id' => $data['id'],
            'title' => $data['title'],            
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
