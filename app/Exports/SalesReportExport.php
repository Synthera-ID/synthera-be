<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromArray, WithHeadings, WithMapping
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Invoice Code',
            'User Name',
            'User Email',
            'Plan Name',
            'Payment Method',
            'Amount',
            'Discount',
            'Total Amount',
            'Status',
            'Issued Date',
            'Paid Date',
        ];
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        return [
            $row['invoice_code'] ?? '',
            $row['user_name'] ?? '',
            $row['user_email'] ?? '',
            $row['plan_name'] ?? '',
            $row['payment_method'] ?? '',
            $row['amount'] ?? 0,
            $row['discount_amount'] ?? 0,
            $row['total_amount'] ?? 0,
            ucfirst($row['status'] ?? ''),
            $row['issued_at'] ?? '',
            $row['paid_at'] ?? '-',
        ];
    }
}
