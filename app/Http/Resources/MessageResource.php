<?php

namespace App\Http\Resources;

use App\Model\DeliveryMan;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MessageResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'customer_id' => ($this->customer_id != null) ? User::select([DB::raw("CONCAT(f_name, ' ' ,l_name) AS name"), 'image'])->find($this->customer_id) : null,
            'deliveryman_id' => ($this->deliveryman_id != null) ? DeliveryMan::select([DB::raw("CONCAT(f_name, ' ' ,l_name) AS name"), 'image'])->find($this->deliveryman_id) : null,
            'message' => $this->message,
            'attachment' => json_decode($this->attachment),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
