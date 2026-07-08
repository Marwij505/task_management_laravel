<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flowlist Admin Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #102033;
        }

        .wrapper {
            max-width: 960px;
            margin: 60px auto;
            padding: 24px;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .stat {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e5eaf0;
        }

        .stat h3 {
            margin: 0 0 10px;
            font-size: 15px;
            color: #64748b;
        }

        .stat strong {
            font-size: 30px;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
        }

        a, button {
            border: 0;
            border-radius: 10px;
            padding: 12px 18px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }

        a {
            background: #16a34a;
            color: white;
        }

        button {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body>
    <main class="wrapper">
        <section class="card">
            <h1>Admin Dashboard</h1>
            <p>
                Halo, {{ auth()->user()->full_name ?? auth()->user()->username }}.
                Kamu masuk sebagai <strong>{{ auth()->user()->role }}</strong>.
            </p>

            <div class="grid">
                <div class="stat">
                    <h3>Total Users</h3>
                    <strong>{{ $totalUsers }}</strong>
                </div>

                <div class="stat">
                    <h3>Admins</h3>
                    <strong>{{ $totalAdmins }}</strong>
                </div>

                <div class="stat">
                    <h3>Regular Users</h3>
                    <strong>{{ $totalRegularUsers }}</strong>
                </div>

                <div class="stat">
                    <h3>Total Tasks</h3>
                    <strong>{{ $totalTasks }}</strong>
                </div>
            </div>

            <div class="actions">
                <a href="{{ route('dashboard') }}">Go to User Dashboard</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>