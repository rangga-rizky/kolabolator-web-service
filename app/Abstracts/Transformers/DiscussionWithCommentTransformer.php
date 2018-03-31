<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;
use App\Abstracts\Transformers\CommentTransformer;


class DiscussionWithCommentTransformer extends Transformer {

    public function transform($data)
    {        

        return [
            'id' => $data['id'],
            'text' => $data['text'],  
            'user_name' => $data->user['username'],
            'is_member' => $data["is_member"],
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
            'comments' => (new CommentTransformer)->transformCollection($data->comments),
        ];
    }
}
