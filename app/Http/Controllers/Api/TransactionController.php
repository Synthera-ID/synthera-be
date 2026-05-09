<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
       $transaction = Transaction:: with([
        'user',
        'plan',
        'payment',
        

       ])->latest()->get();

       return response()->json([
        'message' => 'Berhasi mengambil data transaksi.',
        'data' => $transaction
       ]);
    }

    public function show($id)
    {
       $transaction = Transaction::with([
        'user',
        'plan',
        'payment',
       ])->findOrFail($id);

       if (!$transaction) {
        return response()->json([
            'message' => 'Transaksi tidak ditemukan.'
        ], 404);    
    }
    
       return response()->json([
        'message' => 'detail transaksi berhasil diambil.',
        'data' => $transaction
       ]);
    }


public function update(Request $request, $id)
{
    $transaction = Transaction::find($id);

   if (!$transaction) {
        return response()->json([   
            'message' => 'Transaksi tidak ditemukan.',
        ], 404);
    }

    $validated = $request->validate([
       'transaction_status' => 'required|string|'
    ]);

    $transaction->update([
        'transaction_status' => $validated['transaction_status'],
    ]);

    return response()->json([
        'message' => 'Status transaksi berhasil diperbarui.',
        'data' => $transaction
    ]);
}


public function destroy($id)
{
    $transaction = Transaction::find($id);

    if (!$transaction) {
        return response()->json([
            'message' => 'Transaksi tidak ditemukan.'
        ], 404);
    }

    $transaction->delete();

    return response()->json([
        'message' => 'Transaksi berhasil dihapus.'
    ]);
}

}