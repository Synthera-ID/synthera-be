<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL PAYMENTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Payment::all()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET DETAIL PAYMENT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $payment = Payment::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_code' => 'required|string',
            'payment_gateway' => 'required|string',
            'min_amount' => 'required|numeric',
            'payment_status' => 'required|string',
        ]);

        $payment = Payment::create([
            'payment_method' => $request->payment_method,
            'payment_code' => $request->payment_code,
            'payment_gateway' => $request->payment_gateway,
            'min_amount' => $request->min_amount,
            'payment_status' => $request->payment_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => $payment
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'payment_method' => 'required|string',
            'payment_code' => 'required|string',
            'payment_gateway' => 'required|string',
            'min_amount' => 'required|numeric',
            'payment_status' => 'required|string',
        ]);

        $payment->update([
            'payment_method' => $request->payment_method,
            'payment_code' => $request->payment_code,
            'payment_gateway' => $request->payment_gateway,
            'min_amount' => $request->min_amount,
            'payment_status' => $request->payment_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }
}