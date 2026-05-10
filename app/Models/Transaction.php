<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    protected $fillable = [
        'invoice_code',      // Kode unik transaksi/invoice
        'user_id',           // Relasi ke user yang bertransaksi
        'payment_id',
        'plan_id',           // Relasi ke paket/layanan yang dibeli
        'discount_id',       // Relasi ke promo/diskon yang digunakan
        'amount',            // Harga asli sebelum diskon
        'discount_amount',   // Nominal potongan harga
        'final_amount',      // Total akhir yang harus dibayar
        'transaction_status', // Status (misal: pending, success, failed)


        'CompanyCode',       // Kode identitas perusahaan
        'Status',            // Status tambahan (aktif/non-aktif)
        'IsDeleted',         // Flag untuk soft-delete manual
        'CreatedBy',         // User ID pembuat data
        'CreatedDate',       // Tanggal pembuatan data (manual)
    ];
}
