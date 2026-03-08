<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::all();
    }

    public function show($id)
    {
        return Transaction::findOrFail($id);
    }
}