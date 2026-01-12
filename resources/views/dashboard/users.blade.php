@extends('layouts.dashboard')

@section('content')
<div id="section-users">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>User Management</h3>
        <button class="btn btn-primary" onclick="showUserForm()">+ Add User</button>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4">
                <h5>System Users</h5>
                <table class="table table-hover mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody id="user-list-body"></tbody>
                </table>
            </div>
        </div>
        <div class="col-md-4 hidden" id="user-form-container">
            <div class="card p-4">
                <h5>Add New User</h5>
                <form onsubmit="handleCreateUser(event)">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" id="new-user-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" id="new-user-email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" id="new-user-pass" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Create User</button>
                    <button type="button" class="btn btn-secondary w-100 mt-2" onclick="hideUserForm()">Cancel</button>
                </form>
                <div id="user-msg-box" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function fetchUsers() {
        try {
            const res = await axios.get(`${API_URL}/users`);
            const tbody = document.getElementById('user-list-body');
            tbody.innerHTML = '';
            res.data.forEach(user => {
                tbody.innerHTML += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
        } catch (err) {
            console.error('Failed to fetch users', err);
            if(err.response && err.response.status === 401) logout();
        }
    }

    function showUserForm() {
        document.getElementById('user-form-container').classList.remove('hidden');
    }

    function hideUserForm() {
        document.getElementById('user-form-container').classList.add('hidden');
        document.getElementById('user-msg-box').innerHTML = '';
    }

    async function handleCreateUser(e) {
        e.preventDefault();
        const msgBox = document.getElementById('user-msg-box');
        msgBox.innerHTML = '';
        
        const payload = {
            name: document.getElementById('new-user-name').value,
            email: document.getElementById('new-user-email').value,
            password: document.getElementById('new-user-pass').value
        };

        try {
            await axios.post(`${API_URL}/users`, payload);
            msgBox.innerHTML = '<div class="alert alert-success">User Created!</div>';
            e.target.reset();
            fetchUsers();
            setTimeout(hideUserForm, 1500);
        } catch (err) {
            msgBox.innerHTML = `<div class="alert alert-danger">${err.response?.data?.message || err.message}</div>`;
        }
    }

    // Init
    fetchUsers();
</script>
@endpush
