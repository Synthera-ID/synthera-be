<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            
            // Ini bagian pentingnya:
            // Jika kolom 'avatar' di database ada isinya, pakai itu.
            // Jika kosong (null), pakai UI-Avatars sebagai cadangan (fallback).
            'profile_picture'  => $this->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF',
            
            'created_at'       => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}