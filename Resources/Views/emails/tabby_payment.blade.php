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
            max-width: 600px;
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
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3BFFC3;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .gym-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .greeting {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .payment-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
            font-weight: 500;
        }
        .detail-value {
            color: #333;
            font-weight: 600;
        }
        .amount-row {
            font-size: 18px;
            color: #e74c3c;
        }
        .tabby-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #3BFFC3 0%, #00D4AA 100%);
            border-radius: 10px;
        }
        .tabby-logo {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
        }
        .tabby-text {
            color: #000;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .pay-button {
            display: inline-block;
            background-color: #000;
            color: #fff !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .pay-button:hover {
            background-color: #333;
        }
        .installments {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .installment-box {
            background-color: rgba(255,255,255,0.9);
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 12px;
            color: #333;
            margin: 0 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
        .contact-info {
            margin-top: 15px;
        }
        .contact-info a {
            color: #00D4AA;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
            .installments {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container" style="text-align:center;direction:rtl;">
        <div class="header">
            @if($gym_logo)
                <img src="{{ asset($gym_logo) }}" alt="{{ $gym_name }}" class="logo">
            @endif
            <h1 class="gym-name">{{ $gym_name }}</h1>
        </div>

        <div class="content">
            @if($is_arabic)
                <p class="greeting">مرحباً <strong>{{ $member_name }}</strong>،</p>
                <p>شكراً لاشتراكك معنا! يمكنك إتمام دفع المبلغ المتبقي بسهولة عبر <strong>تابي</strong>.</p>
            @else
                <p class="greeting">Hello <strong>{{ $member_name }}</strong>,</p>
                <p>Thank you for subscribing with us! You can easily complete your remaining payment through <strong>Tabby</strong>.</p>
            @endif

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">{{ $is_arabic ? 'الاشتراك' : 'Subscription' }}</span>
                    <span class="detail-value">{{ $subscription_name }}</span>
                </div>
                <div class="detail-row amount-row">
                    <span class="detail-label">{{ $is_arabic ? 'المبلغ المدفوع' : 'Paid Amount' }}:  </span>
                    <span class="detail-value">{{ $amount }} {{ $currency }}</span>
                </div>
            </div>

            <div class="tabby-section">
                <div class="tabby-logo">tabby</div>
                <p class="tabby-text">
                    @if($is_arabic)
                        قسّط مشترياتك على 4 دفعات بدون فوائد أو رسوم
                    @else
                        Split into 4 interest-free payments. No fees.
                    @endif
                </p>

                <a href="{{ $payment_url }}" class="pay-button">
                    {{ $is_arabic ? 'ادفع الآن' : 'Pay Now' }}
                </a>

                @php
                    $installmentAmount = number_format(floatval(str_replace(',', '', $amount)) / 4, 2);
                @endphp
                <div class="installments" >
                    <span class="installment-box">{{ $is_arabic ? 'اليوم' : 'Today' }}: {{ $installmentAmount }} {{ $currency }}</span>
                    <span class="installment-box">{{ $is_arabic ? 'شهر 1' : 'Month 1' }}: {{ $installmentAmount }} {{ $currency }}</span>
                    <span class="installment-box">{{ $is_arabic ? 'شهر 2' : 'Month 2' }}: {{ $installmentAmount }} {{ $currency }}</span>
                    <span class="installment-box">{{ $is_arabic ? 'شهر 3' : 'Month 3' }}: {{ $installmentAmount }} {{ $currency }}</span>
                </div>
            </div>

            <p style="text-align: center; color: #666; font-size: 14px;">
                @if($is_arabic)
                    أو انسخ هذا الرابط وافتحه في المتصفح:<br>
                    <a href="{{ $payment_url }}" style="color: #00D4AA; word-break: break-all;">{{ $payment_url }}</a>
                @else
                    Or copy this link and open it in your browser:<br>
                    <a href="{{ $payment_url }}" style="color: #00D4AA; word-break: break-all;">{{ $payment_url }}</a>
                @endif
            </p>
        </div>

        <div class="footer">
            <p>{{ $is_arabic ? 'شكراً لك!' : 'Thank you!' }}</p>
            <div class="contact-info">
                @if($gym_phone)
                    <p>{{ $is_arabic ? 'هاتف' : 'Phone' }}: <a href="tel:{{ $gym_phone }}">{{ $gym_phone }}</a></p>
                @endif
                @if($gym_email)
                    <p>{{ $is_arabic ? 'بريد إلكتروني' : 'Email' }}: <a href="mailto:{{ $gym_email }}">{{ $gym_email }}</a></p>
                @endif
            </div>
            <p style="margin-top: 20px; font-size: 11px; color: #aaa;">
                {{ $is_arabic ? 'هذا البريد الإلكتروني تم إرساله تلقائياً. الرجاء عدم الرد عليه.' : 'This email was sent automatically. Please do not reply.' }}
            </p>
        </div>
    </div>
</body>
</html>
