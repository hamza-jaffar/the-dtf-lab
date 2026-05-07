<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }

        .document-title {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 30px;
        }

        .info-grid td {
            vertical-align: top;
            width: 50%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th {
            background: #f8f8f8;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #999;
            font-size: 10px;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            display: inline-block;
        }

        .badge-success {
            background: #e6fffa;
            color: #047481;
        }

        .badge-pending {
            background: #fffaf0;
            color: #9c4221;
        }

        .total-row td {
            border-top: 2px solid #333;
            font-size: 14px;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    @if($company_logo)
                        <img src="{{ public_path('storage/' . $company_logo) }}"
                            style="max-height: 100px; margin-bottom: 10px;">
                    @else
                        <div class="company-name">{{ $company_name }}</div>
                    @endif
                    <div class="document-title">@yield('document_title')</div>
                </td>
                <td style="width: 50%; text-align: right; color: #666; font-size: 10px;">
                    <div class="font-bold" style="font-size: 14px; color: #333; margin-bottom: 5px;">{{ $company_name }}
                    </div>
                    @if($company_address)
                    <div>{{ $company_address }}</div>@endif
                    @if($company_phone)
                    <div>Phone: {{ $company_phone }}</div>@endif
                    @if($company_email)
                    <div>Email: {{ $company_email }}</div>@endif
                </td>
            </tr>
        </table>
    </div>

    @yield('content')

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }} | {{ $company_name }}
    </div>
</body>

</html>