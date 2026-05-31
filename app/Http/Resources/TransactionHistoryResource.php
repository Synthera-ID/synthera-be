<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionHistoryResource extends JsonResource
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
            'invoice_code' => $this->invoice_code,
            'user_id' => $this->user_id,
            'payment_id' => $this->payment_id,
            'plan_id' => $this->plan_id,
            'discount_id' => $this->discount_id,

            'amount' => $this->amount,
            'discount_amount' => $this->discount_amount,
            'final_amount' => $this->final_amount,

            'transaction_status' => $this->transaction_status,
            'notes' => $this->notes,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'CompanyCode' => $this->CompanyCode,
            'Status' => $this->Status,
            'IsDeleted' => $this->IsDeleted,

            'CreatedBy' => $this->CreatedBy,
            'CreatedDate' => $this->CreatedDate,
            'LastUpdateBy' => $this->LastUpdateBy,
            'LastUpdateDate' => $this->LastUpdateDate,

            'plan' => $this->whenLoaded('plan', function () {
                return [
                    'id' => $this->plan->id,
                    'name' => $this->plan->name,
                    'description' => $this->plan->description,
                    'price' => $this->plan->price,
                    'duration_days' => $this->plan->duration_days,
                    'tier' => $this->plan->tier,
                    'max_courses' => $this->plan->max_courses,
                    'api_daily_limit' => $this->plan->api_daily_limit,
                    'api_rate_limit' => $this->plan->api_rate_limit,
                    'is_active' => $this->plan->is_active,

                    'CompanyCode' => $this->plan->CompanyCode,
                    'Status' => $this->plan->Status,
                    'IsDeleted' => $this->plan->IsDeleted,

                    'CreatedBy' => $this->plan->CreatedBy,
                    'CreatedDate' => $this->plan->CreatedDate,
                    'LastUpdateBy' => $this->plan->LastUpdateBy,
                    'LastUpdateDate' => $this->plan->LastUpdateDate,

                    'created_at' => $this->plan->created_at,
                    'updated_at' => $this->plan->updated_at,
                ];
            }),

            'payment' => $this->whenLoaded('payment', function () {
                return [
                    'id' => $this->payment->id,
                    'payment_method' => $this->payment->payment_method,
                    'payment_code' => $this->payment->payment_code,
                    'payment_gateway' => $this->payment->payment_gateway,
                    'gateway_ref' => $this->payment->gateway_ref,
                    'min_amount' => $this->payment->min_amount,
                    'payment_status' => $this->payment->payment_status,

                    'created_at' => $this->payment->created_at,
                    'updated_at' => $this->payment->updated_at,

                    'CompanyCode' => $this->payment->CompanyCode,
                    'Status' => $this->payment->Status,
                    'IsDeleted' => $this->payment->IsDeleted,

                    'CreatedBy' => $this->payment->CreatedBy,
                    'CreatedDate' => $this->payment->CreatedDate,
                    'LastUpdateBy' => $this->payment->LastUpdateBy,
                    'LastUpdateDate' => $this->payment->LastUpdateDate,
                ];
            }),
        ];
    }
}
