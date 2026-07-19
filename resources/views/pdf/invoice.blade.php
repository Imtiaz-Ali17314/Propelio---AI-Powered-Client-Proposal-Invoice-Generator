<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 40px 45px; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            display: flex;
            width: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 55%;
            vertical-align: top;
        }

        .logo-box {
            width: 56px;
            height: 56px;
            background-color: #4f46e5;
            border-radius: 8px;
            color: #ffffff;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            line-height: 56px;
        }

        .agency-name {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
            color: #111827;
        }

        .meta-cell {
            width: 45%;
            vertical-align: top;
            text-align: right;
        }

        .invoice-title {
            font-size: 26px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 1px;
        }

        .invoice-number {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .status-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-unpaid { background-color: #fef3c7; color: #92400e; }
        .status-partially_paid { background-color: #dbeafe; color: #1e40af; }
        .status-overdue { background-color: #fee2e2; color: #991b1b; }
        .status-cancelled { background-color: #e5e7eb; color: #374151; }

        .divider {
            border-top: 2px solid #e5e7eb;
            margin: 20px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .info-table td {
            vertical-align: top;
            width: 50%;
        }

        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .value {
            font-size: 12px;
            color: #111827;
        }

        .value strong {
            font-size: 13px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.items thead th {
            background-color: #4f46e5;
            color: #ffffff;
            text-align: left;
            padding: 10px 12px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.items thead th.num { text-align: right; }

        table.items tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11.5px;
        }

        table.items tbody td.num { text-align: right; }

        table.items tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .totals-table {
            width: 100%;
            margin-top: 15px;
        }

        .totals-table td {
            padding: 6px 0;
        }

        .totals-table .totals-inner {
            width: 260px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-inner td {
            padding: 6px 12px;
            font-size: 12px;
        }

        .totals-inner .t-label {
            color: #6b7280;
            text-align: left;
        }

        .totals-inner .t-value {
            text-align: right;
            color: #111827;
        }

        .totals-inner .grand-total td {
            border-top: 2px solid #4f46e5;
            font-size: 15px;
            font-weight: bold;
            color: #4f46e5;
            padding-top: 10px;
        }

        .totals-inner .balance-due td {
            font-weight: bold;
            color: #991b1b;
        }

        .notes {
            margin-top: 30px;
            padding: 14px 16px;
            background-color: #f9fafb;
            border-left: 3px solid #4f46e5;
            font-size: 11px;
            color: #4b5563;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <div class="logo-box">{{ strtoupper(substr($invoice->user->name ?? 'A', 0, 1)) }}</div>
                <div class="agency-name">{{ $invoice->user->name ?? 'Your Agency' }}</div>
                <div class="value">{{ $invoice->user->email ?? '' }}</div>
            </td>
            <td class="meta-cell">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="status-badge status-{{ $invoice->status }}">
                    {{ str_replace('_', ' ', $invoice->status) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td>
                <div class="label">Billed To</div>
                <div class="value">
                    <strong>{{ $invoice->client->name ?? 'N/A' }}</strong><br>
                    @if($invoice->client->company ?? null)
                        {{ $invoice->client->company }}<br>
                    @endif
                    @if($invoice->client->email ?? null)
                        {{ $invoice->client->email }}<br>
                    @endif
                    @if($invoice->client->phone ?? null)
                        {{ $invoice->client->phone }}
                    @endif
                </div>
            </td>
            <td style="text-align: right;">
                <div class="label">Issue Date</div>
                <div class="value">{{ optional($invoice->issued_date)->format('d M, Y') }}</div>
                <div class="label" style="margin-top: 10px;">Due Date</div>
                <div class="value">{{ optional($invoice->due_date)->format('d M, Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="num" style="width: 15%;">Qty</th>
                <th class="num" style="width: 17%;">Rate</th>
                <th class="num" style="width: 18%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="num">${{ number_format($item->rate, 2) }}</td>
                    <td class="num">${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>
                <table class="totals-inner">
                    <tr>
                        <td class="t-label">Subtotal</td>
                        <td class="t-value">${{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label">Tax</td>
                        <td class="t-value">${{ number_format($invoice->tax, 2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="t-label">Total</td>
                        <td class="t-value">${{ number_format($invoice->total, 2) }}</td>
                    </tr>
                    @if($invoice->paidAmount > 0)
                        <tr>
                            <td class="t-label">Paid</td>
                            <td class="t-value">${{ number_format($invoice->paidAmount, 2) }}</td>
                        </tr>
                        <tr class="balance-due">
                            <td class="t-label">Balance Due</td>
                            <td class="t-value">${{ number_format($invoice->balanceDue, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($invoice->notes)
        <div class="notes">
            <strong>Notes:</strong> {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        Generated by Propelio &middot; {{ now()->format('d M, Y') }}
    </div>

</body>
</html>