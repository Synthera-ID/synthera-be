<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// This controller handles API key management for users. It allows authenticated users to:
// - List all their API keys (index)
// - Generate a new API key (generate)
// - Toggle the active/revoked status of a key (updateStatus)
// - Delete a key permanently (destroy)
// Each API key is associated with a user and has a unique name for easy identification.
// The generated API key is a random string of 64 characters, ensuring security and uniqueness.
class ApiKeyController extends Controller
{
    /**
     * List all API keys belonging to the authenticated user.
     * The full api_key value is masked (only first 8 and last 4 chars exposed).
     */
    public function index(Request $request)
    {
        $keys = ApiKey::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($key) {
                return [
                    'id'           => $key->id,
                    'name'         => $key->name,
                    'api_key'      => $key->api_key,
                    'api_key_masked' => substr($key->api_key, 0, 8) . str_repeat('x', 20) . substr($key->api_key, -4),
                    'is_active'    => $key->is_active,
                    'last_used_at' => $key->last_used_at,
                    'created_at'   => $key->created_at,
                    'updated_at'   => $key->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'API keys fetched successfully',
            'data'    => $keys,
        ]);
    }

    /**
     * Generate a new API key for the authenticated user.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:100',
        ]);

        $apiKey = ApiKey::create([
            'user_id' => $request->user()->id,
            'name'    => $request->name,
            'api_key' => Str::random(64),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully',
            'data'    => [
                'id'           => $apiKey->id,
                'name'         => $apiKey->name,
                'api_key'      => $apiKey->api_key,
                'api_key_masked' => substr($apiKey->api_key, 0, 8) . str_repeat('x', 20) . substr($apiKey->api_key, -4),
                'is_active'    => $apiKey->is_active,
                'last_used_at' => $apiKey->last_used_at,
                'created_at'   => $apiKey->created_at,
                'updated_at'   => $apiKey->updated_at,
            ],
        ]);
    }

    /**
     * Toggle the active/revoked status of an API key.
     * Only the owner of the key can update its status.
     */
    public function updateStatus(Request $request, $id)
    {
        $key = ApiKey::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $key->update([
            'is_active' => !$key->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $key->is_active ? 'API key activated successfully' : 'API key revoked successfully',
            'data'    => [
                'id'        => $key->id,
                'is_active' => $key->is_active,
            ],
        ]);
    }

    /**
     * Permanently delete an API key.
     * Only the owner of the key can delete it.
     */
    public function destroy(Request $request, $id)
    {
        $key = ApiKey::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $key->delete();

        return response()->json([
            'success' => true,
            'message' => 'API key deleted successfully',
        ]);
    }
}