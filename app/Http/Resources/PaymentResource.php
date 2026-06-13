<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Detail pembayaran
            'payment_method' => $this->payment_method,
            'payment_code' => $this->payment_code,
            'payment_gateway' => $this->payment_gateway,
            'gateway_ref' => $this->gateway_ref,

            // Nominal
            'min_amount' => (float) $this->min_amount,

            // Status
            'payment_status' => $this->payment_status,
            'status' => (int) $this->Status,
            'is_deleted' => (bool) $this->IsDeleted,

            // Audit
            'company_code' => $this->CompanyCode,
            'created_by' => $this->CreatedBy,
            'last_update_by' => $this->LastUpdateBy,

            // Tanggal custom DB
            'created_date' => $this->CreatedDate?->format('Y-m-d H:i:s'),
            'last_update_date' => $this->LastUpdateDate?->format('Y-m-d H:i:s'),

            // Laravel timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}