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
        font-size: 13px;
        color: #222;
        background: #fff;
        direction: rtl;
    }

    /* ── Header ── */
    .header {
        background-color: #1a3a5c;
        color: #fff;
        padding: 18px 24px;
        width: 100%;
    }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: middle; }
    .header .branch-name { font-size: 20px; font-weight: bold; }
    .header .invoice-title { font-size: 15px; margin-top: 4px; color: #cde; }
    .header .invoice-number { font-size: 22px; font-weight: bold; text-align: left; }
    .header .invoice-date   { font-size: 12px; color: #cde; text-align: left; }

    /* ── Status badge ── */
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        color: #fff;
    }
    .badge-draft     { background-color: #6c757d; }
    .badge-paid      { background-color: #28a745; }
    .badge-cancelled { background-color: #dc3545; }

    /* ── Sections ── */
    .section { padding: 16px 24px; }
    .section-title {
        font-size: 13px;
        font-weight: bold;
        color: #1a3a5c;
        border-bottom: 2px solid #1a3a5c;
        padding-bottom: 4px;
        margin-bottom: 10px;
    }

    /* ── Info grid ── */
    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 5px 8px; font-size: 12px; }
    .info-table .label { color: #555; width: 40%; }
    .info-table .value { font-weight: bold; }

    /* ── Financials ── */
    .fin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    .fin-table th {
        background-color: #1a3a5c;
        color: #fff;
        padding: 8px 10px;
        font-size: 12px;
        text-align: right;
    }
    .fin-table td {
        padding: 8px 10px;
        font-size: 13px;
        border-bottom: 1px solid #e0e0e0;
    }
    .fin-table tr:last-child td { border-bottom: none; }
    .fin-table .total-row td {
        background-color: #f0f4f8;
        font-weight: bold;
        font-size: 14px;
    }
    .text-left { text-align: left; }

    /* ── Notes ── */
    .notes-box {
        background-color: #f9f9f9;
        border-right: 4px solid #1a3a5c;
        padding: 10px 14px;
        font-size: 12px;
        color: #444;
        margin: 0 24px 16px;
    }

    /* ── Footer ── */
    .footer {
        background-color: #f0f4f8;
        text-align: center;
        padding: 10px 24px;
        font-size: 11px;
        color: #555;
        border-top: 1px solid #ccc;
        position: fixed;
        bottom: 0;
        width: 100%;
    }
</style>
</head>
<body>

{{-- ── Header ──────────────────────────────────────────────────── --}}
<div class="header">
    <table>
        <tr>
            <td class="branch-name">
                {{ optional($invoice->branch ?? null)->name ?? config('app.name') }}
                <div class="invoice-title">
                    @if($invoice->type === 'sales')       فاتورة مبيعات
                    @elseif($invoice->type === 'purchase') فاتورة مشتريات
                    @else                                  إشعار دائن
                    @endif
                </div>
            </td>
            <td class="invoice-number" style="text-align:left;">
                {{ $invoice->invoice_number }}
                <div class="invoice-date">
                    {{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : now()->format('Y-m-d') }}
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Invoice info ─────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-title">بيانات الفاتورة</div>
    <table class="info-table">
        <tr>
            <td class="label">رقم الفاتورة</td>
            <td class="value">{{ $invoice->invoice_number }}</td>
            <td class="label">تاريخ الإصدار</td>
            <td class="value">{{ $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">نوع الفاتورة</td>
            <td class="value">
                @if($invoice->type === 'sales')       مبيعات
                @elseif($invoice->type === 'purchase') مشتريات
                @else                                  إشعار دائن
                @endif
            </td>
            <td class="label">تاريخ الاستحقاق</td>
            <td class="value">{{ $invoice->due_at ? $invoice->due_at->format('Y-m-d') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">الحالة</td>
            <td class="value">
                @php
                    $badgeClass = match($invoice->status) {
                        'paid'      => 'badge-paid',
                        'cancelled' => 'badge-cancelled',
                        default     => 'badge-draft',
                    };
                    $statusLabel = match($invoice->status) {
                        'paid'      => 'مدفوعة',
                        'cancelled' => 'ملغاة',
                        default     => 'مسودة',
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
            </td>
            @if($invoice->reference_invoice_id)
            <td class="label">فاتورة مرجعية</td>
            <td class="value">{{ optional($invoice->originalInvoice)->invoice_number ?? '#'.$invoice->reference_invoice_id }}</td>
            @else
            <td></td><td></td>
            @endif
        </tr>
    </table>
</div>

{{-- ── Member / Supplier ────────────────────────────────────────── --}}
@if($invoice->member_id || $invoice->supplier_id)
<div class="section">
    <div class="section-title">{{ $invoice->type === 'purchase' ? 'بيانات المورد' : 'بيانات العميل' }}</div>
    <table class="info-table">
        <tr>
            @if($invoice->member_id)
            <td class="label">رقم العضو</td>
            <td class="value">{{ $invoice->member_id }}</td>
            @endif
            @if($invoice->supplier_id)
            <td class="label">رقم المورد</td>
            <td class="value">{{ $invoice->supplier_id }}</td>
            @endif
        </tr>
    </table>
</div>
@endif

{{-- ── Financial summary ────────────────────────────────────────── --}}
<div class="section">
    <div class="section-title">الملخص المالي</div>
    <table class="fin-table">
        <thead>
            <tr>
                <th>البيان</th>
                <th class="text-left">المبلغ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>المجموع قبل الضريبة</td>
                <td class="text-left">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>ضريبة القيمة المضافة ({{ $invoice->vat_rate }}%)</td>
                <td class="text-left">{{ number_format($invoice->vat_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>الإجمالي</td>
                <td class="text-left">{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Notes ───────────────────────────────────────────────────── --}}
@if($invoice->notes)
<div class="notes-box">
    <strong>ملاحظات:</strong> {{ $invoice->notes }}
</div>
@endif

{{-- ── Footer ───────────────────────────────────────────────────── --}}
<div class="footer">
    تم إنشاء هذه الفاتورة بواسطة النظام &mdash; {{ now()->format('Y-m-d H:i') }}
</div>

</body>
</html>
