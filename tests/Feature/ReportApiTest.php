<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'user']);
    $this->admin = User::factory()->create(['role' => 'admin']);

    // Create plan
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

    // Create payment
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
});

test('regular user cannot access reports endpoints', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/admin/reports/preview');

    $response->assertStatus(403);
});

test('admin can preview sales report data', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/admin/reports/preview');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'start_date',
                'end_date',
                'summary' => [
                    'total_revenue',
                    'total_invoices',
                    'paid_invoices',
                ],
                'plan_distribution',
                'payment_method_distribution',
                'monthly_trends',
            ]
        ]);
});

test('admin can export sales report as PDF', function () {
    $response = $this->actingAs($this->admin)
        ->get('/api/admin/reports/export-pdf');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

test('admin can export sales report as CSV', function () {
    $response = $this->actingAs($this->admin)
        ->get('/api/admin/reports/export-csv');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
