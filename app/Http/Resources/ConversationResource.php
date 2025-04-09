<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'reply' => $this->reply,
            'checked' => $this->checked,
            'image' => json_decode($this->image),
            'is_reply' => (boolean)$this->is_reply,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
