<?php

namespace App\Abstracts\Transformers;

use Carbon\Carbon;
use App\MemberRequest;

class NotifTransformer extends Transformer {

  
    public function transform($data)
    {       
        if($data['action'] == 2){
            $request = MemberRequest::where("id",$data['helper_id'])->first();
            $notes =  $request->notes;
            $request_id = $data['helper_id'];
            $idea_id = null;
        }else if($data['action'] == 3){
            $idea_id = $data['helper_id'];
            $request_id = null;
            $notes = "";
        }
        else{
            $notes = "";
            $idea_id = null;
            $request_id = null;
        }
        
        return [
            'id' => $data['id'],
            'text' => $data['text'],  
            'user_name' => $data->user['username'],            
            'user_id' => $data->user['id'],
            'action' => $data['action'], 
            'phone' => $data['phone'], 
            'request_id' => $request_id, 
            'idea_id' => $idea_id, 
            'notes' => $notes, 
            'created_at' => Carbon::parse($data['created_at'])->format('h:i - d M Y'),
            'updated_at' => Carbon::parse($data['updated_at'])->format('h:i - d M Y'),
        ];
    }
}
