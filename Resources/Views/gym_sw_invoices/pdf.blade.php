<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $invoice->invoice_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        color: #222;
        background: #fff;
        direction: rtl;
    }

    /* ── Header ── */
    .header {
        background-color: #1a3a5c;
        padding: 18px 24px;
        width: 100%;
    }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: middle; }
    .header .branch-name  { font-size: 20px; font-weight: bold; color: #ffffff; }
    .header .invoice-title { font-size: 14px; margin-top: 4px; color: #cde; }
    .header .invoice-number { font-size: 22px; font-weight: bold; color: #ffffff; text-align: left; }
    .header .invoice-date   { font-size: 12px; color: #cde; text-align: left; }

    /* ── Status badges ── */
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        color: #fff;
    }
    .badge-draft     { background-color: #6c757d; }
    .badge-partial   { background-color: #fd7e14; }
    .badge-paid      { background-color: #28a745; }
    .badge-cancelled { background-color: #dc3545; }

    /* ── Sections ── */
    .section { padding: 14px 24px; }
    .section-title {
        font-size: 12px;
        font-weight: bold;
        color: #1a3a5c;
        border-bottom: 2px solid #1a3a5c;
        padding-bottom: 4px;
        margin-bottom: 10px;
    }

    /* ── Info grid ── */
    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 5px 8px; font-size: 11px; }
    .info-table .label { color: #555; width: 35%; }
    .info-table .value { font-weight: bold; color: #222; }

    /* ── Two-column layout ── */
    .two-col { width: 100%; border-collapse: collapse; }
    .two-col td { width: 50%; vertical-align: top; padding: 0 8px; }

    /* ── Financials ── */
    .fin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    .fin-table th {
        background-color: #1a3a5c;
        color: #fff;
        padding: 7px 10px;
        font-size: 11px;
        text-align: right;
    }
    .fin-table td {
        padding: 7px 10px;
        font-size: 12px;
        border-bottom: 1px solid #e8e8e8;
    }
    .fin-table tr:last-child td { border-bottom: none; }
    .fin-table .total-row td {
        background-color: #eef2f7;
        font-weight: bold;
        font-size: 13px;
    }
    .amount-col { text-align: left; }

    /* ── QR code ── */
    .qr-section {
        text-align: center;
        padding: 10px 24px 0;
    }
    .qr-section img {
        width: 110px;
        height: 110px;
    }
    .qr-label {
        font-size: 10px;
        color: #888;
        margin-top: 4px;
    }

    /* ── Notes ── */
    .notes-box {
        background-color: #f9f9f9;
        border-right: 4px solid #1a3a5c;
        padding: 8px 12px;
        font-size: 11px;
        color: #444;
        margin: 0 24px 12px;
    }

    /* ── Footer ── */
    .footer {
        background-color: #f0f4f8;
        text-align: center;
        padding: 8px 24px;
        font-size: 10px;
        color: #555;
        border-top: 1px solid #ccc;
        position: fixed;
        bottom: 0;
        width: 100%;
    }
</style>
</head>
<body>

{{-- ── Header ──────────────────────────────────────────────────────── --}}
<div class="header">
    <table>
        <tr>
            <td>
                <div class="branch-name">
                    {{ $settings?->getRawOriginal('name_ar') ?: ($settings?->getRawOriginal('name_en') ?: config('app.name')) }}
                </div>
                <div class="invoice-title">
                    @if($invoice->type === 'sales')        فاتورة ضريبية مبسطة
                    @elseif($invoice->type === 'purchase') فاتورة مشتريات
                    @else                                  إشعار دائن
                    @endif
                </div>
            </td>
            <td style="text-align:left;">
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="invoice-date">
                    {{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : now()->format('Y-m-d') }}
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Invoice details + Member info (two-column) ─────────────────── --}}
<div class="section">
    <table class="two-col">
        <tr>
            {{-- Invoice details --}}
            <td>
                <div class="section-title">بيانات الفاتورة</div>
                <table class="info-table">
                    <tr>
                        <td class="label">رقم الفاتورة</td>
                        <td class="value">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">نوع الفاتورة</td>
                        <td class="value">
                            @if($invoice->type === 'sales')        مبيعات
                            @elseif($invoice->type === 'purchase') مشتريات
                            @else                                  إشعار دائن
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">تاريخ الإصدار</td>
                        <td class="value">{{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">الحالة</td>
                        <td class="value">
                            @php
                                $badgeClass  = match($invoice->status) {
                                    'paid'      => 'badge-paid',
                                    'partial'   => 'badge-partial',
                                    'cancelled' => 'badge-cancelled',
                                    default     => 'badge-draft',
                                };
                                $statusLabel = match($invoice->status) {
                                    'paid'      => 'مدفوعة',
                                    'partial'   => 'مدفوعة جزئياً',
                                    'cancelled' => 'ملغاة',
                                    default     => 'مسودة',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                    @if($invoice->reference_invoice_id)
                    <tr>
                        <td class="label">فاتورة مرجعية</td>
                        <td class="value">{{ optional($invoice->originalInvoice)->invoice_number ?? '#'.$invoice->reference_invoice_id }}</td>
                    </tr>
                    @endif
                </table>
            </td>

            {{-- Member / Supplier info --}}
            <td>
                @if($invoice->member)
                <div class="section-title">بيانات العميل</div>
                <table class="info-table">
                    <tr>
                        <td class="label">الاسم</td>
                        <td class="value">{{ $invoice->member->name }}</td>
                    </tr>
                    @if($invoice->member->phone)
                    <tr>
                        <td class="label">الجوال</td>
                        <td class="value">{{ $invoice->member->phone }}</td>
                    </tr>
                    @endif
                    @if($invoice->member->national_id)
                    <tr>
                        <td class="label">الهوية / الإقامة</td>
                        <td class="value">{{ $invoice->member->national_id }}</td>
                    </tr>
                    @endif
                    @if($invoice->member->email)
                    <tr>
                        <td class="label">البريد الإلكتروني</td>
                        <td class="value">{{ $invoice->member->email }}</td>
                    </tr>
                    @endif
                </table>
                @elseif($invoice->supplier_id)
                <div class="section-title">بيانات المورد</div>
                <table class="info-table">
                    <tr>
                        <td class="label">رقم المورد</td>
                        <td class="value">{{ $invoice->supplier_id }}</td>
                    </tr>
                </table>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ── Financial summary ────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-title">الملخص المالي</div>
    <table class="fin-table">
        <thead>
            <tr>
                <th>البيان</th>
                <th class="amount-col">المبلغ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>المجموع قبل الضريبة</td>
                <td class="amount-col">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>ضريبة القيمة المضافة ({{ $invoice->vat_rate }}%)</td>
                <td class="amount-col">{{ number_format($invoice->vat_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>الإجمالي</td>
                <td class="amount-col">{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── ZATCA QR code ────────────────────────────────────────────────── --}}
@if($invoice->zatcaBillingInvoice && $invoice->zatcaBillingInvoice->zatca_qr_code)
<div class="qr-section">
    <img src="data:image/png;base64,{{ $invoice->zatcaBillingInvoice->zatca_qr_code }}" alt="ZATCA QR Code">
    <div class="qr-label">رمز الاستجابة السريعة - هيئة الزكاة والضريبة والجمارك</div>
</div>
@endif

{{-- ── Notes ───────────────────────────────────────────────────────── --}}
@if($invoice->notes)
<div class="notes-box">
    <strong>ملاحظات:</strong> {{ $invoice->notes }}
</div>
@endif

{{-- ── Footer ───────────────────────────────────────────────────────── --}}
<div class="footer">
    تم إنشاء هذه الفاتورة بواسطة النظام &mdash; {{ now()->format('Y-m-d H:i') }}
</div>

</body>
</html>
