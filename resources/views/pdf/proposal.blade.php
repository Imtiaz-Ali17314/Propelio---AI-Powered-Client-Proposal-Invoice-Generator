<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proposal: {{ $proposal->title }}</title>
    <style>
        @page { margin: 40px 45px; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.6;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 55%;
            vertical-align: top;
        }

        .logo-table {
            border-collapse: collapse;
        }

        .logo-letter {
            width: 66px; 
            height: 56px; 
            background-color: #4f46e5; 
            border-radius: 10px; 
            -webkit-border-radius: 10px; 
            text-align: center; 
            vertical-align: middle; 
            padding-bottom: 10px; 
            font-size: 26px; 
            font-weight: bold; 
            color: #ffffff;
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

        .proposal-title {
            font-size: 26px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 1px;
        }

        .proposal-number {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .status-table {
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .status-cell {
            padding: 4px 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .proposal-status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 6px;
        }

        .proposal-date {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .divider {
            border-top: 2px solid #f3f4f6;
            margin: 15px 0 20px 0;
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
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #9ca3af;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .value {
            font-size: 11.5px;
            color: #374151;
        }

        .value strong {
            font-size: 12px;
            color: #111827;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        .overview-box {
            background-color: #f9fafb;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #374151;
        }

        .deliverables-list {
            margin-top: 5px;
            margin-bottom: 20px;
            padding-left: 15px;
        }

        .deliverables-list li {
            margin-bottom: 6px;
            color: #374151;
        }

        .timeline-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .timeline-table tr {
            page-break-inside: avoid;
        }

        .timeline-table td {
            padding: 10px 0;
            vertical-align: top;
            border-bottom: 1px solid #f3f4f6;
        }

        .phase-duration {
            width: 90px;
            font-weight: bold;
            color: #4f46e5;
            font-size: 10.5px;
        }

        .phase-content {
            padding-left: 10px;
        }

        .phase-title {
            font-weight: bold;
            color: #111827;
            font-size: 11.5px;
            margin-bottom: 2px;
        }

        .phase-desc {
            color: #4b5563;
            font-size: 10.5px;
            line-height: 1.5;
        }

        table.cost-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        table.cost-table thead th {
            background-color: #4f46e5;
            color: #ffffff;
            text-align: left;
            padding: 8px 12px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.cost-table thead th.num { text-align: right; }

        table.cost-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            color: #374151;
        }

        table.cost-table tbody td.num { text-align: right; font-weight: bold; color: #111827; }

        table.cost-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .grand-total-row {
            background-color: #f5f3ff !important;
        }

        .grand-total-row td {
            border-top: 1.5px solid #4f46e5;
            border-bottom: 1.5px solid #4f46e5 !important;
            font-weight: bold;
            font-size: 12px !important;
        }

        .grand-total-label {
            color: #4f46e5 !important;
        }

        .grand-total-val {
            color: #4f46e5 !important;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 12px;
        }
    </style>
</head>
<body>

    @php
        $currencySymbols = ['USD' => '$', 'PKR' => '₨', 'EUR' => '€', 'GBP' => '£'];
        $currencyCode = $proposal->cost_breakdown['currency'] ?? 'USD';
        $currencySymbol = $currencySymbols[$currencyCode] ?? $currencyCode . ' ';

        $statusColors = [
            'draft' => ['bg' => '#fef3c7', 'text' => '#92400e'],
            'sent' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
            'accepted' => ['bg' => '#d1fae5', 'text' => '#065f46'],
            'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
        ];
        $statusColor = $statusColors[$proposal->status] ?? $statusColors['draft'];
    @endphp

    {{-- Top bar: logo/agency on left, INVOICE STYLE header on right --}}
   <table class="header-table">
    <tr>
        <td class="logo-cell">
            <table class="logo-table">
                <tr>
                    <td class="logo-letter">
                        {{ strtoupper(substr($proposal->user->name ?? 'A', 0, 1)) }}
                    </td>
                </tr>
            </table>

            <div class="agency-name">{{ $proposal->user->name ?? 'Your Agency' }}</div>
            <div class="value">{{ $proposal->user->email ?? '' }}</div>
        </td>

        <td class="meta-cell">
            <div class="proposal-title">PROPOSAL</div>

            <div class="proposal-number">
                {{ $proposal->proposal_number ?? ('#'.$proposal->id) }}
            </div>

            <div class="proposal-date" style="margin-top: 4px;">
                Date: {{ $proposal->created_at->format('d M, Y') }}
            </div>
        </td>
    </tr>
</table>

    <div class="divider"></div>

    {{-- Full-width left-aligned proposal title --}}
    <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #e5e7eb;">
        <div style="font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 5px;">Proposal Title</div>
        <div class="proposal-title">{{ $proposal->title }}</div>
        @if($proposal->total_amount > 0)
            <div style="margin-top: 6px; font-size: 11px; color: #6b7280;">
                Estimated Budget:
                <strong style="color: #4f46e5;">{{ $currencySymbol }}{{ number_format($proposal->total_amount, 2) }}</strong>
            </div>
        @endif
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="label">Prepared For</div>
                <div class="value">
                    <strong>{{ $proposal->client->name ?? 'Client' }}</strong><br>
                    @if($proposal->client->company ?? null)
                        {{ $proposal->client->company }}<br>
                    @endif
                    @if($proposal->client->email ?? null)
                        {{ $proposal->client->email }}
                    @endif
                </div>
            </td>
            <td style="text-align: right;">
                <div class="label">Prepared By</div>
                <div class="value">
                    <strong>{{ $proposal->user->name ?? 'Your Agency' }}</strong><br>
                    {{ $proposal->user->email ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Project Overview -->
    @if(!empty($proposal->scope['overview']))
        <div class="section-title">Project Overview</div>
        <div class="overview-box">
            {{ $proposal->scope['overview'] }}
        </div>
    @endif

    <!-- Scope / Deliverables -->
    @if(!empty($proposal->scope['deliverables']))
        <div class="section-title">Key Deliverables / Scope</div>
        <ul class="deliverables-list">
            @foreach($proposal->scope['deliverables'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endif

    <!-- Timeline / Phases -->
    @if(!empty($proposal->timeline['phases']))
        <div class="section-title">Project Timeline & Milestones</div>
        <table class="timeline-table">
            <tbody>
                @foreach($proposal->timeline['phases'] as $phase)
                    <tr>
                        <td class="phase-duration">{{ $phase['duration'] ?? '' }}</td>
                        <td class="phase-content">
                            <div class="phase-title">{{ $phase['name'] ?? '' }}</div>
                            <div class="phase-desc">{{ $phase['description'] ?? '' }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Cost Breakdown -->
    @if(!empty($proposal->cost_breakdown['items']))
        <div class="section-title">Cost Breakdown & Investment</div>
        <table class="cost-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="num" style="width: 25%;">Estimated Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proposal->cost_breakdown['items'] as $item)
                    <tr>
                        <td>{{ $item['label'] ?? '' }}</td>
                        <td class="num">{{ $currencySymbol }}{{ number_format($item['amount'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="grand-total-row">
                    <td class="grand-total-label">Total Estimated Investment</td>
                    <td class="grand-total-val">{{ $currencySymbol }}{{ number_format($proposal->cost_breakdown['total'] ?? $proposal->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generated by Propelio AI Proposal &middot; {{ now()->format('d M, Y') }}
    </div>

</body>
</html>