<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create users
    $this->user = User::factory()->create(['role' => 'user']);
    $this->admin = User::factory()->create(['role' => 'admin']);

    // Create subscription plan
    $this->plan = SubscriptionPlan::create([
        'name' => 'Premium Plan',
        'description' => 'Premium Access',
        'price' => 100000,
        'duration_days' => 30,
        'tier' => 'premium',
        'max_courses' => 10,
        'api_daily_limit' => 1000,
        'api_rate_limit' => 60,
        'is_active' => true,
    ]);

    // Create payment method
    $this->payment = Payment::create([
        'payment_method' => 'bank_transfer',
        'payment_code' => 'BCA_VA',
        'payment_gateway' => 'DuitKu',
        'min_amount' => 10000,
        'payment_status' => true,
    ]);

    // Create transaction
    $this->transaction = Transaction::create([
        'invoice_code' => 'SYN-TEST-12345',
        'user_id' => $this->user->id,
        'payment_id' => $this->payment->id,
        'plan_id' => $this->plan->id,
        'amount' => 100000,
        'final_amount' => 100000,
        'discount_amount' => 0,
        'transaction_status' => 'paid',
        'notes' => 'Test payment',
        'CreatedBy' => $this->user->name,
        'CreatedDate' => now(),
    ]);

    // The booted event hook automatically generates the invoice record
    $this->invoice = Invoice::where('transaction_id', $this->transaction->id)->first();
});

test('user can list their own invoices with filtering', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/invoices?status=paid');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'invoice_code',
                    'amount',
                    'total_amount',
                    'status',
                    'issued_at',
                ]
            ]
        ]);
});

test('user can view their own invoice detail', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/invoices/{$this->invoice->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'invoice_code' => $this->invoice->invoice_code,
            'status' => 'paid',
        ]);
});

test('user cannot view other users invoice detail', function () {
    $otherUser = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($otherUser)
        ->getJson("/api/invoices/{$this->invoice->id}");

    $response->assertStatus(404);
});

test('user can preview their own invoice HTML', function () {
    $response = $this->actingAs($this->user)
        ->get("/api/invoices/{$this->invoice->id}/preview");

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/html')
        ->assertSee('SYNTHERA')
        ->assertSee($this->invoice->invoice_code);
});

test('user can export their own invoice as PDF', function () {
    $response = $this->actingAs($this->user)
        ->get("/api/invoices/{$this->invoice->id}/export-pdf");

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('admin can list all invoices', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/admin/invoices');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});
