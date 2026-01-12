<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Server Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .hidden { display: none !important; }
        .sidebar { min-height: 100vh; border-right: 1px solid #dee2e6; }
        .nav-link { cursor: pointer; color: #333; }
        .nav-link:hover { background-color: #f8f9fa; }
        .nav-link.active { background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar bg-light py-4">
                <h4 class="px-3 mb-4">Email Server</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">Manage Configs</a>
                    </li>
                    <li class="nav-item">
                        <a href="/dashboard/test-email" class="nav-link {{ request()->is('dashboard/test-email') ? 'active' : '' }}">Send Test Email</a>
                    </li>
                    <li class="nav-item">
                        <a href="/dashboard/logs" class="nav-link {{ request()->is('dashboard/logs') ? 'active' : '' }}">Email Logs</a>
                    </li>
                    <li class="nav-item">
                        <a href="/dashboard/docs" class="nav-link {{ request()->is('dashboard/docs') ? 'active' : '' }}">API Docs</a>
                    </li>
                    <li class="nav-item">
                        <a href="/dashboard/users" class="nav-link {{ request()->is('dashboard/users') ? 'active' : '' }}">Manage Users</a>
                    </li>
                    <li class="nav-item mt-4">
                        <a href="#" onclick="logout()" class="nav-link text-danger">Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Content Area -->
            <div class="col-md-10 py-4 px-5">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        const API_URL = '/api';

        // Auth Check
        function checkAuth() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/'; // Redirect to login
            } else {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            }
        }

        function logout() {
            localStorage.removeItem('token');
            window.location.href = '/';
        }

        // Run auth check on load
        checkAuth();
    </script>
    
    @stack('scripts')
</body>
</html>
