<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_code }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 14px;
            line-height: 1.6;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: top;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
        }
        .company-details {
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .bill-to {
            font-size: 14px;
        }
        .bill-to-title {
            font-size: 12px;
            text-transform: uppercase;
            color: #999;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-info {
            text-align: right;
            font-size: 13px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 10px;
            text-align: right;
        }
        .totals-table .label {
            color: #64748b;
            font-size: 13px;
        }
        .totals-table .val {
            font-weight: bold;
            font-size: 14px;
            width: 150px;
        }
        .totals-table .grand-total {
            font-size: 18px;
            color: #4f46e5;
            font-weight: bold;
            border-top: 2px solid #e2e8f0;
            padding-top: 12px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #dcfce7;
            color: #15803d;
        }
        .status-pending {
            background-color: #fef9c3;
            color: #a16207;
        }
        .status-failed {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-cancelled {
            background-color: #e2e8f0;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td>
                    <div class="title">SYNTHERA</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Invoice Code: {{ $invoice->invoice_code }}</div>
                </td>
                <td class="company-details">
                    <strong>Synthera ID</strong><br>
                    Green Office Park, BSD City<br>
                    Tangerang, Indonesia<br>
                    support@synthera.id
                </td>
            </tr>
        </table>

        <table class="details-table">
            <tr>
                <td style="width: 50%;">
                    <div class="bill-to-title">Billed To:</div>
                    <div class="bill-to">
                        <strong>{{ $invoice->user->name }}</strong><br>
                        {{ $invoice->user->email }}<br>
                        {{ $invoice->user->phone ?? 'No Phone Number' }}
                    </div>
                </td>
                <td class="invoice-info" style="width: 50%;">
                    <div class="bill-to-title">Invoice Information:</div>
                    <strong>Status:</strong> 
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ $invoice->status }}
                    </span><br>
                    <strong>Issued Date:</strong> {{ $invoice->issued_at->format('d M Y H:i') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_at->format('d M Y H:i') }}<br>
                    @if($invoice->paid_at)
                        <strong>Paid Date:</strong> {{ $invoice->paid_at->format('d M Y H:i') }}<br>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Subscription / Product</th>
                    <th style="width: 20%; text-align: right;">Price</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $invoice->transaction->plan->name ?? 'Membership Subscription' }}</strong>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                            {{ $invoice->transaction->plan->description ?? 'Subscription plan' }}
                        </div>
                    </td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="val">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="val">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Tax (0%)</td>
                <td class="val">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label grand-total">Grand Total</td>
                <td class="val grand-total">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        @if($invoice->notes)
            <div style="margin-top: 30px; font-size: 12px; border-left: 3px solid #e2e8f0; padding-left: 10px; color: #64748b;">
                <strong>Notes:</strong><br>
                {{ $invoice->notes }}
            </div>
        @endif

        <div class="footer">
            Thank you for your subscription. If you have any questions, please contact billing@synthera.id.
        </div>
    </div>
</body>
</html>
