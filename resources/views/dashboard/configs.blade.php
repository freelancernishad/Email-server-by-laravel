@extends('layouts.dashboard')

@section('content')
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

    <!-- Create/Edit Form -->
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
@endsection

@push('scripts')
<script>
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

    let allConfigs = [];

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

    fetchConfigs();
</script>
@endpush
