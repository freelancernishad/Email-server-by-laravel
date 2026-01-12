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
        <!-- Auth Section -->
        <div id="auth-section" class="row justify-content-center mt-5">
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

        <!-- Dashboard Section -->
        <div id="dashboard-section" class="row hidden">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar bg-light py-4">
                <h4 class="px-3 mb-4">Email Server</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a onclick="showSection('configs')" id="nav-configs" class="nav-link active">Manage Configs</a>
                    </li>
                    <li class="nav-item">
                        <a onclick="showSection('test-email')" id="nav-test-email" class="nav-link">Send Test Email</a>
                    </li>
                    <li class="nav-item">
                        <a onclick="showSection('logs')" id="nav-logs" class="nav-link">Email Logs</a>
                    </li>
                    <li class="nav-item">
                        <a onclick="showSection('api-docs')" id="nav-api-docs" class="nav-link">API Docs</a>
                    </li>
                    <li class="nav-item">
                        <a onclick="showSection('users')" id="nav-users" class="nav-link">Manage Users</a>
                    </li>
                    <li class="nav-item mt-4">
                        <a onclick="logout()" class="nav-link text-danger">Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Content Area -->
            <div class="col-md-10 py-4 px-5">
                
                <!-- Manage Configs Section -->
                <div id="section-configs">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Email Configurations</h3>
                        <button class="btn btn-primary" onclick="showCreateForm()">+ New Config</button>
                    </div>

                    <!-- Config List -->
                    <div class="card p-4 mb-4">
                        <h5>Existing Configurations</h5>
                        <div class="table-responsive">
                            <table class="table table-hover mt-2">
                                <thead>
                                    <tr>
                                        <th>Provider/Host</th>
                                        <th>From Address</th>
                                        <th>Key (click to copy)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="config-list-body">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Create/Edit Form (Modal-like behavior but inline for now) -->
                    <div id="config-form-container" class="card p-4 hidden">
                        <h5 id="form-title" class="mb-3">Create New Configuration</h5>
                        
                        <div class="mb-3 p-3 bg-light rounded">
                            <label class="fw-bold">Quick Fill Provider</label>
                            <select id="provider-select" class="form-select" onchange="autoFillProvider()">
                                <option value="">-- Select a Provider --</option>
                                <optgroup label="Gmail">
                                    <option value="gmail_tls">Gmail (TLS - 587)</option>
                                    <option value="gmail_ssl">Gmail (SSL - 465)</option>
                                </optgroup>
                                <optgroup label="Outlook / Office 365">
                                    <option value="outlook_tls">Outlook (TLS - 587)</option>
                                </optgroup>
                                <optgroup label="Mailtrap">
                                    <option value="mailtrap">Mailtrap (TLS - 2525)</option>
                                </optgroup>
                                <optgroup label="SendGrid">
                                    <option value="sendgrid_tls">SendGrid (TLS - 587)</option>
                                    <option value="sendgrid_ssl">SendGrid (SSL - 465)</option>
                                </optgroup>
                                <optgroup label="Yahoo Mail">
                                    <option value="yahoo_ssl">Yahoo (SSL - 465)</option>
                                    <option value="yahoo_tls">Yahoo (TLS - 587)</option>
                                </optgroup>
                                <optgroup label="GoDaddy">
                                    <option value="godaddy_ssl">GoDaddy (SSL - 465)</option>
                                    <option value="godaddy_tls">GoDaddy (TLS - 587)</option>
                                </optgroup>
                                <optgroup label="Namecheap">
                                    <option value="namecheap_ssl">Namecheap (SSL - 465)</option>
                                    <option value="namecheap_tls">Namecheap (TLS - 587)</option>
                                </optgroup>
                                <optgroup label="Zoho Mail">
                                    <option value="zoho_ssl">Zoho (SSL - 465)</option>
                                    <option value="zoho_tls">Zoho (TLS - 587)</option>
                                </optgroup>
                                <optgroup label="Others">
                                    <option value="icloud">iCloud (TLS - 587)</option>
                                    <option value="fastmail">Fastmail (SSL - 465)</option>
                                </optgroup>
                            </select>
                        </div>

                        <form onsubmit="handleSaveConfig(event)">
                            <input type="hidden" id="conf-id">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label>Host</label>
                                    <input type="text" id="conf-host" class="form-control" required placeholder="smtp.example.com">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Port</label>
                                    <input type="number" id="conf-port" class="form-control" required placeholder="587">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Username</label>
                                    <input type="text" id="conf-user" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Password</label>
                                    <input type="text" id="conf-pass" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Encryption</label>
                                    <input type="text" id="conf-enc" class="form-control" placeholder="tls">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>From Address</label>
                                    <input type="email" id="conf-from-addr" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>From Name</label>
                                    <input type="text" id="conf-from-name" class="form-control">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" id="save-btn" class="btn btn-success flex-grow-1">Save Configuration</button>
                                <button type="button" onclick="hideConfigForm()" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                        <div id="conf-msg-box" class="mt-3"></div>
                    </div>
                </div>

                <!-- Test Email Section -->
                <div id="section-test-email" class="hidden">
                    <h3 class="mb-4">Send Test Email</h3>
                    <div class="card p-4">
                        <form onsubmit="handleSendTestEmail(event)">
                            <div class="mb-3">
                                <label class="fw-bold">Select Configuration</label>
                                <select id="test-conf-select" class="form-select" required>
                                    <option value="">-- Select Saved Config --</option>
                                    <!-- Populated via JS -->
                                </select>
                                <small class="text-muted">Select which API Key/Config to use for sending.</small>
                            </div>

                            <div class="mb-3">
                                <label>To Email</label>
                                <input type="email" id="test-to" class="form-control" required placeholder="recipient@example.com">
                            </div>

                            <div class="mb-3">
                                <label>Subject</label>
                                <input type="text" id="test-subject" class="form-control" required placeholder="Test Subject">
                            </div>

                            <div class="mb-3">
                                <label>Body (HTML supported)</label>
                                <textarea id="test-body" class="form-control" rows="5" required><h3>Hello!</h3><p>This is a test email sent from the Laravel Email Server.</p></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>From Email (Optional)</label>
                                    <input type="email" id="test-from-email" class="form-control" placeholder="Override Config Default">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>From Name (Optional)</label>
                                    <input type="text" id="test-from-name" class="form-control" placeholder="Override Config Default">
                                </div>
                            </div>



                            <div class="mb-3">
                                <label>Attachments (Optional)</label>
                                <input type="file" id="test-attachments" class="form-control" multiple>
                            </div>

                            <button type="submit" id="send-test-btn" class="btn btn-primary">Send Test Email</button>
                        </form>
                        <div id="test-msg-box" class="mt-3"></div>
                    </div>
                </div>

                <!-- Email Logs Section -->
                <div id="section-logs" class="hidden">
                    <h3 class="mb-4">Email Logs</h3>
                    <div class="card p-4">
                        <!-- Toolbar -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2 flex-grow-1">
                                <input type="text" id="log-search" class="form-control" style="max-width: 300px;" placeholder="Search (To, Subject)" onkeyup="fetchLogs()">
                                <select id="log-config-key" class="form-select" style="max-width: 200px;" onchange="fetchLogs()">
                                    <option value="">All Configurations</option>
                                    <!-- Populated via JS -->
                                </select>
                                <select id="log-status" class="form-select" style="max-width: 150px;" onchange="fetchLogs()">
                                    <option value="">All Status</option>
                                    <option value="success">Success</option>
                                    <option value="failed">Failed</option>
                                </select>
                                <input type="date" id="log-date" class="form-control" style="max-width: 160px;" onchange="fetchLogs()">
                                <button class="btn btn-outline-secondary" onclick="resetLogFilters()" title="Reset Filters">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="dropdown">
                                <button class="btn btn-danger dropdown-toggle" type="button" id="deleteMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    Bulk Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="deleteMenuButton">
                                    <li><a class="dropdown-item" href="#" onclick="deleteSelectedLogs()">Delete Selected</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Danger Zone</h6></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="clearLogs('config')">Clear Current Config Logs</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="clearLogs('all')">Clear ALL Logs</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-logs" onchange="toggleSelectAll(this)"></th>
                                        <th>Date</th>
                                        <th>To</th>
                                        <th>Subject</th>
                                        <th>From</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="logs-table-body">
                                </tbody>
                            </table>
                        </div>
                        <div id="logs-pagination" class="d-flex justify-content-between align-items-center mt-3">
                            <!-- Simple Pagination Controls -->
                            <button class="btn btn-sm btn-outline-primary" id="prev-page-btn" onclick="changeLogPage(-1)">Previous</button>
                            <span id="page-indicator">Page 1</span>
                            <button class="btn btn-sm btn-outline-primary" id="next-page-btn" onclick="changeLogPage(1)">Next</button>
                        </div>
                    </div>
                </div>

                <!-- API Docs Section -->
                <div id="section-api-docs" class="hidden">
                    <h3 class="mb-4">API Documentation</h3>
                    
                    <div class="card p-4 mb-4">
                        <h4>Endpoint</h4>
                        <div class="alert alert-info">
                            <span class="badge bg-success me-2">POST</span> <span class="font-monospace">{{ url('/api/send-email') }}</span>
                        </div>
                        
                        <h5 class="mt-4">Parameters</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>config_key</code></td>
                                    <td>String</td>
                                    <td>Yes</td>
                                    <td>The unique key of your email configuration.</td>
                                </tr>
                                <tr>
                                    <td><code>to</code></td>
                                    <td>String (Email)</td>
                                    <td>Yes</td>
                                    <td>Recipient's email address.</td>
                                </tr>
                                <tr>
                                    <td><code>subject</code></td>
                                    <td>String</td>
                                    <td>Yes</td>
                                    <td>Email subject line.</td>
                                </tr>
                                <tr>
                                    <td><code>body</code></td>
                                    <td>String (HTML)</td>
                                    <td>Yes</td>
                                    <td>Email content (supports HTML).</td>
                                </tr>
                                <tr>
                                    <td><code>from_email</code></td>
                                    <td>String (Email)</td>
                                    <td>No</td>
                                    <td>Override the "From" address defined in config.</td>
                                </tr>
                                <tr>
                                    <td><code>from_name</code></td>
                                    <td>String</td>
                                    <td>No</td>
                                    <td>Override the "From" name defined in config.</td>
                                </tr>
                                <tr>
                                    <td><code>attachments[]</code></td>
                                    <td>File (Array)</td>
                                    <td>No</td>
                                    <td>Multiple file attachments.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Code Examples</h4>
                            <div class="w-50">
                                <select id="docs-conf-select" class="form-select" onchange="updateCodeSnippets()">
                                    <option value="">-- Select Config to Fill Key --</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="codeTabs">
                            <li class="nav-item"><a class="nav-link active" onclick="showCodeTab('curl')">cURL</a></li>
                            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('php')">PHP</a></li>
                            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('python')">Python</a></li>
                            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('node')">Node.js</a></li>
                            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('java')">Java</a></li>
                            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('go')">Go</a></li>
                            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('ruby')">Ruby</a></li>
                        </ul>

                        <div class="bg-dark text-white p-3 rounded-bottom position-relative">
                             <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" onclick="copyCode()">Copy</button>
                             <pre><code id="code-display" class="language-bash">Select a config to generate code...</code></pre>
                        </div>
                    </div>
                </div>

                <!-- User Management Section -->
                <div id="section-users" class="hidden">
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

            </div>
        </div>
    </div>

    <script>
        const API_URL = '/api';

        // --- Providers Data ---
        const providers = {
            gmail_tls: { host: 'smtp.gmail.com', port: 587, enc: 'tls' },
            gmail_ssl: { host: 'smtp.gmail.com', port: 465, enc: 'ssl' },
            outlook_tls: { host: 'smtp.office365.com', port: 587, enc: 'tls' },
            mailtrap: { host: 'smtp.mailtrap.io', port: 2525, enc: 'tls' },
            sendgrid_tls: { host: 'smtp.sendgrid.net', port: 587, enc: 'tls' },
            sendgrid_ssl: { host: 'smtp.sendgrid.net', port: 465, enc: 'ssl' },
            yahoo_ssl: { host: 'smtp.mail.yahoo.com', port: 465, enc: 'ssl' },
            yahoo_tls: { host: 'smtp.mail.yahoo.com', port: 587, enc: 'tls' },
            godaddy_ssl: { host: 'smtpout.secureserver.net', port: 465, enc: 'ssl' },
            godaddy_tls: { host: 'smtpout.secureserver.net', port: 587, enc: 'tls' },
            namecheap_ssl: { host: 'mail.privateemail.com', port: 465, enc: 'ssl' },
            namecheap_tls: { host: 'mail.privateemail.com', port: 587, enc: 'tls' },
            zoho_ssl: { host: 'smtp.zoho.com', port: 465, enc: 'ssl' },
            zoho_tls: { host: 'smtp.zoho.com', port: 587, enc: 'tls' },
            icloud: { host: 'smtp.mail.me.com', port: 587, enc: 'tls' },
            fastmail: { host: 'smtp.fastmail.com', port: 465, enc: 'ssl' }
        };

        // --- Auth & Init ---
        function checkAuth() {
            const token = localStorage.getItem('token');
            if (token) {
                document.getElementById('auth-section').classList.add('hidden');
                document.getElementById('dashboard-section').classList.remove('hidden');
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                fetchConfigs(); 
            } else {
                document.getElementById('auth-section').classList.remove('hidden');
                document.getElementById('dashboard-section').classList.add('hidden');
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            try {
                const res = await axios.post(`${API_URL}/login`, {
                    email: document.getElementById('login-email').value,
                    password: document.getElementById('login-password').value
                });
                localStorage.setItem('token', res.data.access_token);
                checkAuth();
            } catch (err) {
                document.getElementById('login-msg').innerHTML = `<div class="alert alert-danger">${err.response?.data?.message || err.message}</div>`;
            }
        }

        function logout() {
            localStorage.removeItem('token');
            checkAuth();
        }

        // --- Navigation ---
        function showSection(section) {
            // Hide all sections
            document.getElementById('section-configs').classList.add('hidden');
            document.getElementById('section-test-email').classList.add('hidden');
            document.getElementById('section-api-docs').classList.add('hidden');
            document.getElementById('section-users').classList.add('hidden');
            
            // Deactivate navs
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

            // Show target
            document.getElementById(`section-${section}`).classList.remove('hidden');
            document.getElementById(`nav-${section}`).classList.add('active');

            if(section === 'test-email') {
                populateTestSelect();
            }
            if(section === 'api-docs') {
                populateDocsSelect();
                updateCodeSnippets();
            }
            if(section === 'users') {
                fetchUsers();
            }
            if(section === 'logs') {
                populateLogConfigSelect();
                fetchLogs();
            }
        }

        // --- Config Manager Logic ---
        let allConfigs = []; // Store fetched configs

        // --- Logs Logic ---
        let currentLogPage = 1;

        function populateLogConfigSelect() {
            const select = document.getElementById('log-config-key');
            const currentVal = select.value;
            select.innerHTML = '<option value="">All Configurations</option>';
            allConfigs.forEach(conf => {
                select.innerHTML += `<option value="${conf.key}">${conf.host} (${conf.username || conf.from_address})</option>`;
            });
            if(currentVal) select.value = currentVal;
        }

        async function fetchLogs(page = 1) {
            currentLogPage = page;
            const search = document.getElementById('log-search').value;
            const status = document.getElementById('log-status').value;
            const date = document.getElementById('log-date').value;
            const config_key = document.getElementById('log-config-key').value;

            try {
                const res = await axios.get(`${API_URL}/email-logs`, {
                    params: { page, search, status, date, config_key }
                });
                renderLogs(res.data);
            } catch (err) {
                console.error(err);
                if(err.response && err.response.status === 401) logout();
            }
        }

        function renderLogs(data) {
            const tbody = document.getElementById('logs-table-body');
            tbody.innerHTML = '';
            
            if(data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No logs found.</td></tr>';
                return;
            }

            data.data.forEach(log => {
                const date = new Date(log.created_at).toLocaleString();
                const statusBadge = log.status === 'success' 
                    ? '<span class="badge bg-success">Success</span>' 
                    : '<span class="badge bg-danger">Failed</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td><input type="checkbox" class="log-checkbox" value="${log.id}"></td>
                        <td><small>${date}</small></td>
                        <td>${log.to_email}</td>
                        <td>${log.subject}</td>
                        <td><small>${log.from_email || '-'}</small></td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick='showLogDetails(${JSON.stringify(log)})'>Details</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLog(${log.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });

            // Pagination UI
            document.getElementById('page-indicator').innerText = `Page ${data.current_page} of ${data.last_page}`;
            document.getElementById('prev-page-btn').disabled = !data.prev_page_url;
            document.getElementById('next-page-btn').disabled = !data.next_page_url;
        }

        function changeLogPage(direction) {
            fetchLogs(currentLogPage + direction);
        }

        function resetLogFilters() {
            document.getElementById('log-search').value = '';
            document.getElementById('log-status').value = '';
            document.getElementById('log-date').value = '';
            document.getElementById('log-config-key').value = '';
            fetchLogs(1);
        }

        function showLogDetails(log) {
            // Ideally use a Bootstrap modal, but alert for specific request
            // Just beware body might be HTML and long. for alert we might strip tags or just show raw.
            // Let's show a cleaner version.
            const bodyPreview = log.body ? log.body.replace(/<[^>]*>?/gm, '').substring(0, 200) + '...' : 'No Content';
            
            alert(`
Config Key: ${log.config_key}
IP Address: ${log.ip_address}
Error Message: ${log.error_message || 'None'}

Body Preview:
${bodyPreview}
            `);
        }

        async function deleteLog(id) {
            if(!confirm('Are you sure you want to delete this log?')) return;
            try {
                await axios.delete(`${API_URL}/email-logs/${id}`);
                fetchLogs(currentLogPage);
            } catch (err) {
                alert('Failed to delete log.');
            }
        }

        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.log-checkbox');
            for(let i=0; i<checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        async function deleteSelectedLogs() {
            const checkboxes = document.querySelectorAll('.log-checkbox:checked');
            if(checkboxes.length === 0) {
                alert('Please select logs delete.');
                return;
            }
            if(!confirm(`Delete ${checkboxes.length} selected logs?`)) return;

            const ids = Array.from(checkboxes).map(cb => cb.value);
            try {
                await axios.post(`${API_URL}/email-logs/bulk-delete`, { ids });
                fetchLogs(currentLogPage);
                document.getElementById('select-all-logs').checked = false;
            } catch (err) {
                alert('Failed to delete selected logs.');
            }
        }

        async function clearLogs(type) {
            let confirmMsg = 'Are you sure you want to clear logs?';
            let payload = {};

            if (type === 'all') {
                confirmMsg = 'WARNING: This will delete ALL email logs in the system. Continue?';
                payload = { all: true };
            } else if (type === 'config') {
                const configKey = document.getElementById('log-config-key').value;
                if(!configKey) {
                    alert('Please select a configuration first to clear its logs.');
                    return;
                }
                confirmMsg = 'This will delete all logs for the currently selected configuration. Continue?';
                payload = { config_key: configKey };
            }

            if(!confirm(confirmMsg)) return;

            try {
                await axios.post(`${API_URL}/email-logs/bulk-delete`, payload);
                fetchLogs(1);
            } catch (err) {
                alert('Failed to clear logs.');
            }
        }

        async function fetchConfigs() {
            try {
                const res = await axios.get(`${API_URL}/email-config`);
                allConfigs = res.data;
                renderConfigList();
            } catch (err) {
                console.error(err);
                if(err.response && err.response.status === 401) logout();
            }
        }


        function renderConfigList() {
             const tbody = document.getElementById('config-list-body');
             tbody.innerHTML = '';
             if(allConfigs.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="4" class="text-center">No configurations found.</td></tr>';
                 return;
             }
             allConfigs.forEach(conf => {
                 tbody.innerHTML += `
                     <tr>
                         <td><strong>${conf.host}</strong><br><small class="text-muted">${conf.username || 'No Auth'}</small></td>
                         <td>${conf.from_address}</td>
                         <td>
                             <div class="input-group input-group-sm" style="width: 150px;">
                                <input type="text" class="form-control" value="${conf.key}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyConfigKey('${conf.key}', this)">Copy</button>
                             </div>
                         </td>
                         <td>
                             <button class="btn btn-sm btn-info text-white" onclick='editConfig(${JSON.stringify(conf)})'>Edit</button>
                         </td>
                     </tr>
                 `;
             });
        }


        function showCreateForm() {
            resetForm();
            document.getElementById('config-form-container').classList.remove('hidden');
            window.scrollTo(0, document.getElementById('config-form-container').offsetTop);
        }

        function hideConfigForm() {
            document.getElementById('config-form-container').classList.add('hidden');
        }

        function editConfig(conf) {
            showCreateForm();
            document.getElementById('conf-id').value = conf.id;
            document.getElementById('conf-host').value = conf.host;
            document.getElementById('conf-port').value = conf.port;
            document.getElementById('conf-user').value = conf.username;
            document.getElementById('conf-pass').value = conf.password;
            document.getElementById('conf-enc').value = conf.encryption;
            document.getElementById('conf-from-addr').value = conf.from_address;
            document.getElementById('conf-from-name').value = conf.from_name;
            
            document.getElementById('form-title').innerText = 'Edit Configuration';
            document.getElementById('save-btn').innerText = 'Update Configuration';
        }

        function resetForm() {
            document.querySelector('#config-form-container form').reset();
            document.getElementById('conf-id').value = '';
            document.getElementById('form-title').innerText = 'Create New Configuration';
            document.getElementById('save-btn').innerText = 'Save Configuration';
            document.getElementById('conf-msg-box').innerHTML = '';
        }

        function autoFillProvider() {
            const provider = document.getElementById('provider-select').value;
            if(provider && providers[provider]) {
                const data = providers[provider];
                document.getElementById('conf-host').value = data.host;
                document.getElementById('conf-port').value = data.port;
                document.getElementById('conf-enc').value = data.enc;
            }
        }

        async function handleSaveConfig(e) {
            e.preventDefault();
            const id = document.getElementById('conf-id').value;
            const payload = {
                host: document.getElementById('conf-host').value,
                port: document.getElementById('conf-port').value,
                username: document.getElementById('conf-user').value,
                password: document.getElementById('conf-pass').value,
                encryption: document.getElementById('conf-enc').value,
                from_address: document.getElementById('conf-from-addr').value,
                from_name: document.getElementById('conf-from-name').value,
            };

            try {
                let res;
                if (id) {
                    res = await axios.put(`${API_URL}/email-config/${id}`, payload);
                    document.getElementById('conf-msg-box').innerHTML = `<div class="alert alert-success">Updated Successfully!</div>`;
                } else {
                    res = await axios.post(`${API_URL}/email-config`, payload);
                    document.getElementById('conf-msg-box').innerHTML = `<div class="alert alert-success">Created Successfully!</div>`;
                }
                fetchConfigs(); 
                if(!id) e.target.reset();
                setTimeout(() => {
                    document.getElementById('conf-msg-box').innerHTML = '';
                    if(id) hideConfigForm();
                }, 1500);
            } catch (err) {
                document.getElementById('conf-msg-box').innerHTML = `<div class="alert alert-danger">Error: ${err.response?.data?.message || err.message}</div>`;
            }
        }

        // --- Test Email Logic ---
        function populateTestSelect() {
            const select = document.getElementById('test-conf-select');
            select.innerHTML = '<option value="">-- Select Saved Config --</option>';
            allConfigs.forEach(conf => {
                select.innerHTML += `<option value="${conf.key}">${conf.host} (${conf.from_address})</option>`;
            });
        }

        async function handleSendTestEmail(e) {
            e.preventDefault();
            const btn = document.getElementById('send-test-btn');
            const msgBox = document.getElementById('test-msg-box');
            
            const formData = new FormData();
            formData.append('config_key', document.getElementById('test-conf-select').value);
            formData.append('to', document.getElementById('test-to').value);
            formData.append('subject', document.getElementById('test-subject').value);
            formData.append('body', document.getElementById('test-body').value);
            formData.append('from_email', document.getElementById('test-from-email').value);
            formData.append('from_name', document.getElementById('test-from-name').value);

            formData.append('from_name', document.getElementById('test-from-name').value);

            const fileInput = document.getElementById('test-attachments');
            if (fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('attachments[]', fileInput.files[i]);
                }
            }

            btn.disabled = true;
            btn.innerText = 'Sending...';
            msgBox.innerHTML = '';

            try {
                const res = await axios.post(`${API_URL}/send-email`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                msgBox.innerHTML = `<div class="alert alert-success">${res.data.message}</div>`;
            } catch (err) {
                let errorMsg = err.message;
                if (err.response && err.response.data) {
                    // Combine the main message with the specific error detail
                    errorMsg = err.response.data.message || errorMsg;
                    if (err.response.data.error) {
                        errorMsg += `<br><small>${err.response.data.error}</small>`;
                    }
                }
                msgBox.innerHTML = `<div class="alert alert-danger"><strong>Failed:</strong> ${errorMsg}</div>`;
            } finally {
                btn.disabled = false;
                btn.innerText = 'Send Test Email';
            }
        }

        // --- Docs Logic ---
        function populateDocsSelect() {
             const select = document.getElementById('docs-conf-select');
             // Preserve selected value if resizing/re-rendering (though here we fully rebuild)
             const currentVal = select.value; 
             select.innerHTML = '<option value="">-- Select Config --</option>';
             allConfigs.forEach(conf => {
                 select.innerHTML += `<option value="${conf.key}">${conf.host} (${conf.username || conf.from_address})</option>`;
             });
             if(currentVal) select.value = currentVal;
        }

        let currentLang = 'curl';

        function showCodeTab(lang) {
            currentLang = lang;
            document.querySelectorAll('#codeTabs .nav-link').forEach(l => l.classList.remove('active'));
            event.target.classList.add('active');
            updateCodeSnippets();
        }

        function updateCodeSnippets() {
            const key = document.getElementById('docs-conf-select').value || 'YOUR_CONFIG_KEY';
            const url = window.location.origin + '/api/send-email';
            
            const snippets = {
                curl: `curl -X POST "${url}" \\
 -F "config_key=${key}" \\
 -F "to=recipient@example.com" \\
 -F "subject=Test Email" \\
 -F "body=<h1>It Works!</h1>" \\
 -F "attachments[]=@/path/to/file1.pdf" \\
 -F "attachments[]=@/path/to/file2.jpg"`,
                php: `&lt;?php
$client = new GuzzleHttp\\Client();
$response = $client->post('${url}', [
    'multipart' => [
        [ 'name' => 'config_key', 'contents' => '${key}' ],
        [ 'name' => 'to', 'contents' => 'recipient@example.com' ],
        [ 'name' => 'subject', 'contents' => 'Test Email' ],
        [ 'name' => 'body', 'contents' => '<h1>It Works!</h1>' ],
        [ 'name' => 'attachments[]', 'contents' => fopen('/path/to/file1.pdf', 'r') ],
        [ 'name' => 'attachments[]', 'contents' => fopen('/path/to/file2.jpg', 'r') ]
    ]
]);
echo $response->getBody();`,
                python: `import requests

url = "${url}"
files = [
    ('attachments[]', ('file1.pdf', open('/path/to/file1.pdf', 'rb'), 'application/pdf')),
    ('attachments[]', ('file2.jpg', open('/path/to/file2.jpg', 'rb'), 'image/jpeg'))
]
payload = {
    'config_key': '${key}',
    'to': 'recipient@example.com',
    'subject': 'Test Email',
    'body': '<h1>It Works!</h1>'
}

response = requests.post(url, data=payload, files=files)
print(response.text)`,
                node: `const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

let data = new FormData();
data.append('config_key', '${key}');
data.append('to', 'recipient@example.com');
data.append('subject', 'Test Email');
data.append('body', '<h1>It Works!</h1>');
data.append('attachments[]', fs.createReadStream('/path/to/file1.pdf'));
data.append('attachments[]', fs.createReadStream('/path/to/file2.jpg'));

axios.post('${url}', data, {
    headers: {
        ...data.getHeaders()
    }
})
.then(res => console.log(res.data))
.catch(err => console.error(err));`,
                java: `// Java 11 HttpClient doesn't built-in support for Multipart.
// You might need a helper class or library like Apache HttpClient.`,
                go: `package main

import (
    "bytes"
    "fmt"
    "mime/multipart"
    "net/http"
    "os"
    "io"
)

func main() {
    url := "${url}"
    method := "POST"

    payload := &bytes.Buffer{}
    writer := multipart.NewWriter(payload)
    
    // ... add fields ...
    
    file1, _ := os.Open("/path/to/file1.pdf")
    defer file1.Close()
    part1, _ := writer.CreateFormFile("attachments[]", "/path/to/file1.pdf")
    io.Copy(part1, file1)

    // ... repeat for file2 ...
    
    writer.Close()

    client := &http.Client{}
    req, _ := http.NewRequest(method, url, payload)
    req.Header.Set("Content-Type", writer.FormDataContentType())
    
    res, _ := client.Do(req)
    defer res.Body.Close()
    
    fmt.Println(res.Status)
}`,
                ruby: `require 'net/http'
require 'uri'
require 'json'

uri = URI('${url}')
request = Net::HTTP::Post.new(uri)
form_data = [
  ['config_key', '${key}'],
  ['to', 'recipient@example.com'],
  ['subject', 'Test Email'],
  ['body', '<h1>It Works!</h1>'],
  ['attachments[]', File.open('/path/to/file1.pdf')],
  ['attachments[]', File.open('/path/to/file2.jpg')]
]
request.set_form(form_data, 'multipart/form-data')

req_options = {
  use_ssl: uri.scheme == "https",
}

response = Net::HTTP.start(uri.hostname, uri.port, req_options) do |http|
  http.request(request)
end

puts response.body`
            };
            
            document.getElementById('code-display').innerText = snippets[currentLang];
        }

        function copyCode() {
            const code = document.getElementById('code-display').innerText;
            navigator.clipboard.writeText(code);
            const btn = event.target;
            const original = btn.innerText;
            btn.innerText = 'Copied!';
            setTimeout(() => btn.innerText = original, 1500);
        }



        function copyConfigKey(key, btn) {
            navigator.clipboard.writeText(key);
            const originalText = btn.innerText;
            btn.innerText = 'Copied!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 1500);
        }

        // --- User Management Logic ---
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
        checkAuth();

    </script>
</body>
</html>