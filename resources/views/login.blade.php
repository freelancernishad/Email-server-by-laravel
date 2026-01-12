<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Email Server Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card p-4">
                    <h4 class="mb-3 text-center">Login to Email Manager</h4>
                    <form onsubmit="handleLogin(event)">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" id="login-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" id="login-password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div id="login-msg" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '/api';

        async function handleLogin(e) {
            e.preventDefault();
            try {
                const res = await axios.post(`${API_URL}/login`, {
                    email: document.getElementById('login-email').value,
                    password: document.getElementById('login-password').value
                });
                localStorage.setItem('token', res.data.access_token);
                window.location.href = '/dashboard';
            } catch (err) {
                document.getElementById('login-msg').innerHTML = `<div class="alert alert-danger">${err.response?.data?.message || err.message}</div>`;
            }
        }

        // Redirect if already logged in
        if(localStorage.getItem('token')) {
            window.location.href = '/dashboard';
        }
    </script>
</body>
</html>
