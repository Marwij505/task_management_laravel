<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied - Flowlist</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #102033;
        }

        .card {
            width: min(520px, 90%);
            padding: 36px;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 42px;
        }

        p {
            margin: 0 0 24px;
            color: #64748b;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            background: #16a34a;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>403</h1>
        <p>
            Access denied. You do not have permission to open this admin page.
        </p>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}">Back to Admin Dashboard</a>
            @else
                <a href="{{ route('dashboard') }}">Back to Dashboard</a>
            @endif
        @else
            <a href="{{ route('login') }}">Back to Login</a>
        @endauth
    </main>
</body>
</html>