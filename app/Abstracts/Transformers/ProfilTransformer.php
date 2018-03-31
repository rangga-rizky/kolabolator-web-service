<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;


class ProfilTransformer extends Transformer {

    public function transform($data)
    {        

        if (empty($data['avatar'])) {
            $avatar = null;
        } else {
            $avatar = url('avatar/'.$data['avatar']);
        }

        return [
            'id' => $data['id'],
            'username' => $data['username'],  
            'city' => $data->city['name'],  
            'province' => $data->city->province['name'],  
            'description' => $data['description'], 
            'avatar' => $avatar, 
            'balance' => "Rp " . number_format($data['balance'],2,',','.'), 
            'current_job' => $data['current_job'],  
            'skills' => $data['skills'], 
            'number_of_ideas' => $data['number_of_ideas'], 
            'collaboration_on' => $data["collaboration_on"],
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
        ];
    }
}
