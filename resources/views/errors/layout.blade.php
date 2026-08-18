<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') • OutfitShop API</title>
    <!-- Brand Favicon -->
    <link rel="icon" type="image/svg+xml" href="https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg">
    <link rel="shortcut icon" href="https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 40px 32px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        }
        .brand-logo {
            height: 48px;
            margin-bottom: 24px;
            object-fit: contain;
        }
        .code {
            font-size: 64px;
            font-weight: 800;
            color: #38bdf8;
            line-height: 1;
            margin-bottom: 12px;
        }
        .message {
            font-size: 20px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 12px;
        }
        .description {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.15s ease;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        .btn-outline:hover {
            background: #334155;
            color: #ffffff;
        }
        .footer-tag {
            margin-top: 30px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        <a href="{{ url('/') }}">
            <img src="https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png" alt="OutfitShop Logo" class="brand-logo">
        </a>
        <div class="code">@yield('code')</div>
        <div class="message">@yield('message')</div>
        <div class="description">@yield('description')</div>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn">API Root</a>
            <a href="{{ url('/guide') }}" class="btn btn-outline">Documentation</a>
        </div>

        <div class="footer-tag">
            OutfitShop Ecommerce Clothing API • {{ date('Y') }}
        </div>
    </div>
</body>
</html>
