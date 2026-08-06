<!DOCTYPE html>
<html lang="{{ $is_arabic ? 'ar' : 'en' }}" dir="{{ $is_arabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 620px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f97d04;
        }
        .logo { max-width: 140px; height: auto; margin-bottom: 10px; }
        .gym-name { font-size: 22px; font-weight: bold; color: #333; margin: 0; }
        .success-banner {
            background: #e6f4ea;
            border: 1px solid #a8d5b0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .success-banner .icon { font-size: 28px; line-height: 1; }
        .success-banner h4 { margin: 0 0 3px; font-size: 16px; color: #1a7a2e; }
        .success-banner p  { margin: 0; font-size: 13px; color: #2d6a35; }
        .invoice-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .invoice-card-header {
            background: #f97d04;
            color: #fff;
            padding: 12px 16px;
            font-size: 15px;
            font-weight: bold;
        }
        .invoice-body { padding: 14px 16px; }
        .invoice-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        .invoice-row:last-child { border-bottom: none; }
        .invoice-row .label { color: #777; }
        .invoice-row .value { font-weight: 600; color: #333; }
        .invoice-row.total-row .label { font-weight: bold; color: #333; font-size: 15px; }
        .invoice-row.total-row .value { font-weight: bold; color: #f97d04; font-size: 15px; }
        .terms-section {
            background: #fffaf5;
            border: 1px solid #f0d0a8;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.7;
        }
        .terms-section h5 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #c46a00;
        }
        .footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
        .footer a { color: #f97d04; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header --}}
        <div class="header">
            @if(!empty($gym_logo))
                <img src="{{ asset('uploads/settings/' . $gym_logo) }}"
                     alt="{{ $gym_name }}"
                     class="logo"
                     onerror="this.style.display='none'">
            @endif
            <h1 class="gym-name">{{ $gym_name }}</h1>
        </div>

        {{-- Success banner --}}
        <div class="success-banner">
            <div class="icon">✅</div>
            <div>
                <h4>{{ $is_arabic ? 'تمت عملية الدفع بنجاح' : 'Payment Successful' }}</h4>
                <p>{{ $is_arabic ? 'تم تفعيل اشتراكك بنجاح.' : 'Your subscription has been activated successfully.' }}</p>
            </div>
        </div>

        {{-- Greeting --}}
        <p style="font-size:15px;">
            @if($is_arabic)
                مرحباً <strong>{{ $member_name }}</strong>،
            @else
                Hello <strong>{{ $member_name }}</strong>,
            @endif
        </p>

        {{-- Invoice card --}}
        <div class="invoice-card">
            <div class="invoice-card-header">
                {{ $is_arabic ? 'تفاصيل الفاتورة' : 'Invoice Details' }}
                &nbsp;#{{ $invoice_id }}
            </div>
            <div class="invoice-body">

                <div class="invoice-row">
                    <span class="label">{{ $is_arabic ? 'الاشتراك' : 'Subscription' }}</span>
                    <span class="value">{{ $subscription_name }}</span>
                </div>

                <div class="invoice-row">
                    <span class="label">{{ $is_arabic ? 'تاريخ البدء' : 'Start Date' }}</span>
                    <span class="value">{{ $joining_date }}</span>
                </div>

                <div class="invoice-row">
                    <span class="label">{{ $is_arabic ? 'تاريخ الانتهاء' : 'Expiry Date' }}</span>
                    <span class="value">{{ $expire_date }}</span>
                </div>

                @if($vat > 0)
                <div class="invoice-row">
                    <span class="label">{{ $is_arabic ? 'المبلغ قبل الضريبة' : 'Amount before VAT' }}</span>
                    <span class="value">{{ number_format($amount - $vat, 2) }} {{ $currency }}</span>
                </div>
                <div class="invoice-row">
                    <span class="label">{{ $is_arabic ? 'الضريبة' : 'VAT' }} ({{ $vat_percentage }}%)</span>
                    <span class="value">{{ number_format($vat, 2) }} {{ $currency }}</span>
                </div>
                @endif

                <div class="invoice-row total-row">
                    <span class="label">{{ $is_arabic ? 'الإجمالي' : 'Total' }}</span>
                    <span class="value">{{ number_format($amount, 2) }} {{ $currency }}</span>
                </div>

            </div>
        </div>

        {{-- Terms section --}}
        @if(!empty($terms_content) && trim(strip_tags($terms_content)))
        <div class="terms-section">
            <h5>{{ $is_arabic ? 'الشروط والأحكام' : 'Terms & Conditions' }}</h5>
            {!! $terms_content !!}
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>{{ $is_arabic ? 'شكراً لثقتك بنا!' : 'Thank you for your trust!' }}</p>
            @if(!empty($gym_phone))
                <p>{{ $is_arabic ? 'هاتف' : 'Phone' }}: <a href="tel:{{ $gym_phone }}">{{ $gym_phone }}</a></p>
            @endif
            @if(!empty($gym_email))
                <p>{{ $is_arabic ? 'بريد إلكتروني' : 'Email' }}: <a href="mailto:{{ $gym_email }}">{{ $gym_email }}</a></p>
            @endif
            <p style="margin-top:16px;font-size:11px;color:#aaa;">
                {{ $is_arabic ? 'هذا البريد الإلكتروني تم إرساله تلقائياً. الرجاء عدم الرد عليه.' : 'This email was sent automatically. Please do not reply.' }}
            </p>
        </div>

    </div>
</body>
</html>
