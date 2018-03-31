<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;


class DiscussionTransformer extends Transformer {

    public function transform($data)
    {        

        return [
            'id' => $data['id'],
            'text' => $data['text'],  
            'user_name' => $data->user['username'],
           // 'user_photo' => $data['description'],
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
        ];
    }
}
