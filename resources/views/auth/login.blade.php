<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Telescope</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); width: 100%; max-width: 400px; }
        h1 { margin-top: 0; font-size: 1.5rem; font-weight: bold; text-align: center; color: #1f2937; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
        input { margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.5rem 0.75rem; font-size: 0.875rem; line-height: 1.5rem; }
        input:focus { outline: 2px solid transparent; outline-offset: 2px; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5); }
        button { margin-top: 1.5rem; width: 100%; display: flex; justify-content: center; padding: 0.5rem 1rem; border: transparent; font-size: 0.875rem; font-weight: 500; color: white; background-color: #4f46e5; border-radius: 0.375rem; cursor: pointer; }
        button:hover { background-color: #4338ca; }
        .error { color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Login</h1>
        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Log in</button>
        </form>
    </div>
</body>
</html>
