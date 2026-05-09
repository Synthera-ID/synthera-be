<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MembershipResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource ke dalam bentuk array (JSON).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'google_id'        => $this->google_id,
            'role' => $this->role,
            'phone' =>   $this->phone,
            'company_code' => $this->company_code,
            'two_factor_enabled' => $this->two_factor_enabled,
            // Ini bagian pentingnya:
            // Jika kolom 'avatar' di database ada isinya, pakai itu.
            // Jika kosong (null), pakai UI-Avatars sebagai cadangan (fallback).
            'avatar_url'  => $this->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF',
            'created_at'       => $this->created_at->format('Y-m-d H:i:s'),
            'membership' => MembershipResource::make(
                $this->whenLoaded('membership')
            ),
        ];
    }
}
