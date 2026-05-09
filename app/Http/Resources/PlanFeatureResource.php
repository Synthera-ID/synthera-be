<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanFeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->feature_label,
            'limit_value' => $this->limit_value,
            'unlimited' => $this->is_unlimited,
            'description' => $this->description,
        ];
    }
}
