<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;


class CommentTransformer extends Transformer {

    public function transform($data)
    {        
        if (empty($data->user['avatar'])) {
            $avatar = null;
        } else {
            $avatar = url('avatar/'.$data->user['avatar']);
        }

        return [
            'id' => $data['id'],
            'comment' => $data['comment'],  
            'user_name' => $data->user['username'],            
            'user_id' => $data->user['id'],
            'user_avatar' => $avatar,
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
        ];
    }
}
