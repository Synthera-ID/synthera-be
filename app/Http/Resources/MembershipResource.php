<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SubscriptionResource;

class MembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'expired_at' => $this->end_date,
            'status' => $this->status,
            'auto_renew' => $this->auto_renew,
            'subscription' => SubscriptionResource::make(
                $this->whenLoaded('subscription')
            ),
            'created_at' => $this->created_at,
        ];
    }
}
