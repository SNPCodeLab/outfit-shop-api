<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'OutfitShop Notification' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0f172a;
            padding: 24px;
            text-align: center;
        }
        .header img.logo {
            height: 48px;
            max-width: 220px;
            object-fit: contain;
            display: inline-block;
        }
        .header h1 {
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            margin: 12px 0 0;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 32px 28px;
            line-height: 1.6;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 16px;
        }
        .badge-gif {
            height: 28px;
            vertical-align: middle;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url', 'https://api.kesararamwithdigital.tech') }}" target="_blank">
                <img src="https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png" alt="OutfitShop Logo" class="logo">
            </a>
            <h1>OutfitShop Ecommerce Clothing API</h1>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px;">
                <strong>OutfitShop</strong> • Powered by Modern Enterprise API
            </p>
            <p style="margin: 0 0 6px;">
                Support: <a href="mailto:{{ config('api.support_email', 'support@kesararamwithdigital.tech') }}" style="color: #2563eb;">{{ config('api.support_email', 'support@kesararamwithdigital.tech') }}</a>
            </p>
            <p style="margin: 0; color: #94a3b8;">
                &copy; {{ date('Y') }} OutfitShop. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
