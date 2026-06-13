<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 14px;
            line-height: 1.6;
        }
        .report-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .date-range {
            text-align: right;
            font-size: 13px;
            color: #64748b;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .stats-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            width: 33.33%;
        }
        .stats-value {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }
        .stats-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 12px;
            margin-top: 30px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .report-table th {
            background-color: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        .report-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="report-box">
        <table class="header-table">
            <tr>
                <td>
                    <div class="title">SYNTHERA SALES REPORT</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Generated at: {{ now()->format('d M Y H:i') }}</div>
                </td>
                <td class="date-range">
                    <strong>Period:</strong> {{ $start_date }} - {{ $end_date }}
                </td>
            </tr>
        </table>

        <table class="stats-table">
            <tr>
                <td class="stats-card" style="border-right: none;">
                    <div class="stats-label">Total Revenue</div>
                    <div class="stats-value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
                </td>
                <td class="stats-card" style="border-right: none;">
                    <div class="stats-label">Total Invoices</div>
                    <div class="stats-value">{{ $summary['total_invoices'] }}</div>
                </td>
                <td class="stats-card">
                    <div class="stats-label">Paid Invoices</div>
                    <div class="stats-value">{{ $summary['paid_invoices'] }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Revenue by Plan</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th style="text-align: center;">Invoices Count</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan_distribution as $plan)
                    <tr>
                        <td><strong>{{ $plan['name'] }}</strong></td>
                        <td style="text-align: center;">{{ $plan['count'] }}</td>
                        <td style="text-align: right;">Rp {{ number_format($plan['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if(empty($plan_distribution))
                    <tr>
                        <td colspan="3" style="text-align: center; color: #64748b;">No data available</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="section-title">Revenue by Payment Method</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th style="text-align: center;">Invoices Count</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment_method_distribution as $payment)
                    <tr>
                        <td><strong>{{ ucfirst($payment['method']) }}</strong></td>
                        <td style="text-align: center;">{{ $payment['count'] }}</td>
                        <td style="text-align: right;">Rp {{ number_format($payment['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if(empty($payment_method_distribution))
                    <tr>
                        <td colspan="3" style="text-align: center; color: #64748b;">No data available</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="section-title">Monthly Trend</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th style="text-align: center;">Invoices Count</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly_trends as $trend)
                    <tr>
                        <td><strong>{{ $trend['month'] }}</strong></td>
                        <td style="text-align: center;">{{ $trend['count'] }}</td>
                        <td style="text-align: right;">Rp {{ number_format($trend['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if(empty($monthly_trends))
                    <tr>
                        <td colspan="3" style="text-align: center; color: #64748b;">No data available</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            Synthera Internal Report. Confidential.
        </div>
    </div>
</body>
</html>
