<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AtomSuit</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --accent: #c084fc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            text-align: center;
        }

        .header {
            margin-bottom: 3rem;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .context-status {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            color: var(--text);
        }

        .context-status strong {
            color: var(--accent);
        }

        .clear-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            text-decoration: underline;
            cursor: pointer;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }

        .clear-btn:hover {
            color: var(--text);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            padding: 2.5rem;
            border-radius: 1.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5);
        }

        .icon {
            width: 64px;
            height: 64px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .card p {
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--text-muted);
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: background 0.2s;
            width: 100%;
            text-align: center;
        }

        .card:hover .btn {
            background: var(--primary-hover);
        }

        .tenant-section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 2.5rem;
            text-align: left;
        }

        .tenant-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .tenant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .tenant-item {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 1.25rem;
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .tenant-name {
            font-weight: 600;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tenant-domain {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .select-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            text-align: center;
        }

        .select-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        .select-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            pointer-events: none;
        }

        .footer {
            margin-top: 4rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .footer-link:hover {
            color: var(--text);
            text-decoration: underline;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.5rem;
            background: var(--accent);
            color: var(--bg);
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AtomSuit Central Admin</h1>
            <p class="subtitle">Manage application performance and monitor system analytics.</p>
        </div>

        @if(session('admin_tenant_id'))
            @php $currentTenant = \App\Models\Tenant::find(session('admin_tenant_id')); @endphp
            <div class="context-status">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Currently Viewing: <strong>{{ $currentTenant?->name ?? 'Unknown Tenant' }}</strong>
                <form action="{{ route('admin.tenant-context.clear') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="clear-btn">(Reset to Central)</button>
                </form>
            </div>
        @else
            <div class="context-status">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                Showing: <strong>Central Application Data</strong>
            </div>
        @endif

        <div class="grid">
            <a href="/telescope" class="card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 12L12 8l-4 4M12 16V9"/></svg>
                </div>
                <h2>Laravel Telescope</h2>
                <p>Monitor requests, exceptions, database queries, and more in real-time.</p>
                <div class="btn">Launch Telescope</div>
            </a>


            <a href="/analytics" class="card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                </div>
                <h2>Request Analytics</h2>
                <p>Detailed insights into visitor behavior, page views, and geographic data.</p>
                <div class="btn">View Analytics Dashboard</div>
            </a>
        </div>

        <div class="tenant-section">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Scope to Tenant
            </h2>
            <div class="tenant-grid">
                @foreach($tenants as $tenant)
                    <div class="tenant-item">
                        <div>
                            <div class="tenant-name">{{ $tenant->name }}</div>
                            <div class="tenant-domain">{{ $tenant->domains->first()?->domain }}</div>
                        </div>
                        <form action="{{ route('admin.tenant-context.set') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                            @if(session('admin_tenant_id') == $tenant->id)
                                <button type="button" class="select-btn active">Selected</button>
                            @else
                                <button type="submit" class="select-btn">Select Tenant</button>
                            @endif
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="footer">
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="footer-link" style="background:none; border:none; cursor:pointer;">Sign out from Central Admin</button>
            </form>
            <a href="/" class="footer-link">Back to Website</a>
        </div>
    </div>
</body>
</html>
