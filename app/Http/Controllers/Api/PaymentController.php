<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::all();
    }

    public function show($id)
    {
        return Payment::findOrFail($id);
    }
}