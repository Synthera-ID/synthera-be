<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;

class MembershipController extends Controller
{
    public function index()
    {
        return Membership::all();
    }

    public function show($id)
    {
        return Membership::findOrFail($id);
    }
}