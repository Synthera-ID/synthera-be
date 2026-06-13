<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'user_id' => $this->user_id,
            'invoice_code' => $this->invoice_code,
            'amount' => $this->amount,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'due_at' => $this->due_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'CompanyCode' => $this->CompanyCode,
            'Status' => $this->Status,
            'IsDeleted' => $this->IsDeleted,
            'CreatedBy' => $this->CreatedBy,
            'CreatedDate' => $this->CreatedDate?->toIso8601String(),
            'LastUpdateBy' => $this->LastUpdateBy,
            'LastUpdateDate' => $this->LastUpdateDate?->toIso8601String(),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                ];
            }),

            'transaction' => $this->whenLoaded('transaction', function () {
                return [
                    'id' => $this->transaction->id,
                    'invoice_code' => $this->transaction->invoice_code,
                    'amount' => $this->transaction->amount,
                    'discount_amount' => $this->transaction->discount_amount,
                    'final_amount' => $this->transaction->final_amount,
                    'transaction_status' => $this->transaction->transaction_status,
                    'notes' => $this->transaction->notes,
                    'plan' => $this->transaction->plan ? [
                        'id' => $this->transaction->plan->id,
                        'name' => $this->transaction->plan->name,
                        'description' => $this->transaction->plan->description,
                        'price' => $this->transaction->plan->price,
                    ] : null,
                    'payment' => $this->transaction->payment ? [
                        'id' => $this->transaction->payment->id,
                        'payment_method' => $this->transaction->payment->payment_method,
                        'payment_code' => $this->transaction->payment->payment_code,
                        'payment_gateway' => $this->transaction->payment->payment_gateway,
                    ] : null,
                ];
            }),
        ];
    }
}
