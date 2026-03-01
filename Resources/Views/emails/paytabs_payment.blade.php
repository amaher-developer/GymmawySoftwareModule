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
            border-bottom: 2px solid #1a237e;
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
            color: #1a237e;
        }
        .paytabs-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            border-radius: 10px;
        }
        .paytabs-logo {
            font-size: 26px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .paytabs-text {
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            margin-bottom: 20px;
        }
        .pay-button {
            display: inline-block;
            background-color: #ffffff;
            color: #1a237e !important;
            text-decoration: none;
            padding: 14px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
        }
        .secure-badge {
            margin-top: 15px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
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
            color: #1a237e;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            body { padding: 10px; }
            .container { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($gym_logo)
                <img src="{{ asset($gym_logo) }}" alt="{{ $gym_name }}" class="logo">
            @endif
            <h1 class="gym-name">{{ $gym_name }}</h1>
        </div>

        <div class="content">
            @if($is_arabic)
                <p class="greeting">مرحباً <strong>{{ $member_name }}</strong>،</p>
                <p>شكراً لاشتراكك معنا! يمكنك إتمام الدفع الآمن عبر <strong>PayTabs</strong>.</p>
            @else
                <p class="greeting">Hello <strong>{{ $member_name }}</strong>,</p>
                <p>Thank you for subscribing with us! You can complete your secure payment through <strong>PayTabs</strong>.</p>
            @endif

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">{{ $is_arabic ? 'الاشتراك' : 'Subscription' }}</span>
                    <span class="detail-value">{{ $subscription_name }}</span>
                </div>
                <div class="detail-row amount-row">
                    <span class="detail-label">{{ $is_arabic ? 'المبلغ' : 'Amount' }}</span>
                    <span class="detail-value">{{ $amount }} {{ $currency }}</span>
                </div>
            </div>

            <div class="paytabs-section">
                <div class="paytabs-logo">PayTabs</div>
                <p class="paytabs-text">
                    @if($is_arabic)
                        بوابة دفع آمنة ومعتمدة لإتمام معاملاتك بكل ثقة
                    @else
                        Secure and trusted payment gateway for confident transactions
                    @endif
                </p>

                <a href="{{ $payment_url }}" class="pay-button">
                    {{ $is_arabic ? 'ادفع الآن' : 'Pay Now' }}
                </a>

                <p class="secure-badge">
                    {{ $is_arabic ? '🔒 معالجة دفع آمنة ومشفرة' : '🔒 Secure & Encrypted Payment Processing' }}
                </p>
            </div>

            <p style="text-align: center; color: #666; font-size: 14px;">
                @if($is_arabic)
                    أو انسخ هذا الرابط وافتحه في المتصفح:<br>
                    <a href="{{ $payment_url }}" style="color: #1a237e; word-break: break-all;">{{ $payment_url }}</a>
                @else
                    Or copy this link and open it in your browser:<br>
                    <a href="{{ $payment_url }}" style="color: #1a237e; word-break: break-all;">{{ $payment_url }}</a>
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
