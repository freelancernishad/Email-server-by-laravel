@extends('layouts.dashboard')

@section('content')
<div id="section-test-email">
    <h3 class="mb-4">Send Test Email</h3>
    <div class="card p-4">
        <form onsubmit="handleSendTestEmail(event)">
            <div class="mb-3">
                <label class="fw-bold">Select Configuration</label>
                <select id="test-conf-select" class="form-select" onchange="onConfigSelected()" required>
                    <option value="">-- Select Saved Config --</option>
                    <!-- Populated via JS -->
                </select>
                <small class="text-muted">Select which API Key/Config to use for sending.</small>
            </div>

            <div class="mb-3">
                <label>To Email</label>
                <input type="text" id="test-to" class="form-control" required placeholder="recipient@example.com (comma-separated for multiple)">
                <small class="text-muted">Single email address or multiple comma-separated addresses.</small>
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Reply-To Email (Optional)</label>
                    <input type="email" id="test-reply-to" class="form-control" placeholder="Override Config Default">
                    <small class="text-muted">Direct replies will be sent to this email address.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Reply-To Name (Optional)</label>
                    <input type="text" id="test-reply-to-name" class="form-control" placeholder="Override Config Default">
                    <small class="text-muted">Name displayed for the reply-to address.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>CC (Optional)</label>
                    <input type="text" id="test-cc" class="form-control" placeholder="cc1@example.com, cc2@example.com">
                    <small class="text-muted">Carbon copy recipients (comma-separated).</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label>BCC (Optional)</label>
                    <input type="text" id="test-bcc" class="form-control" placeholder="bcc1@example.com, bcc2@example.com">
                    <small class="text-muted">Blind carbon copy recipients (comma-separated).</small>
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
@endsection

@push('scripts')
<script>
    let allConfigs = [];

    async function fetchConfigs() {
        try {
            const res = await axios.get(`${API_URL}/email-config`);
            allConfigs = res.data;
            populateTestSelect();
        } catch (err) {
            console.error(err);
        }
    }

    function populateTestSelect() {
        const select = document.getElementById('test-conf-select');
        select.innerHTML = '<option value="">-- Select Saved Config --</option>';
        allConfigs.forEach(conf => {
            select.innerHTML += `<option value="${conf.key}">${conf.host || 'Brevo API'} (${conf.from_address})</option>`;
        });
    }

    function onConfigSelected() {
        const key = document.getElementById('test-conf-select').value;
        const conf = allConfigs.find(c => c.key === key);
        if (conf) {
            document.getElementById('test-from-email').placeholder = conf.from_address ? `Default: ${conf.from_address}` : 'Override Config Default';
            document.getElementById('test-from-name').placeholder = conf.from_name ? `Default: ${conf.from_name}` : 'Override Config Default';
            document.getElementById('test-reply-to').placeholder = conf.reply_to_address ? `Default: ${conf.reply_to_address}` : 'Override Config Default';
            document.getElementById('test-reply-to-name').placeholder = conf.reply_to_name ? `Default: ${conf.reply_to_name}` : 'Override Config Default';
        } else {
            document.getElementById('test-from-email').placeholder = 'Override Config Default';
            document.getElementById('test-from-name').placeholder = 'Override Config Default';
            document.getElementById('test-reply-to').placeholder = 'Override Config Default';
            document.getElementById('test-reply-to-name').placeholder = 'Override Config Default';
        }
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

        const fromEmail = document.getElementById('test-from-email').value;
        if (fromEmail) formData.append('from_email', fromEmail);

        const fromName = document.getElementById('test-from-name').value;
        if (fromName) formData.append('from_name', fromName);

        const replyTo = document.getElementById('test-reply-to').value;
        if (replyTo) formData.append('reply_to', replyTo);

        const replyToName = document.getElementById('test-reply-to-name').value;
        if (replyToName) formData.append('reply_to_name', replyToName);

        const cc = document.getElementById('test-cc').value;
        if (cc) formData.append('cc', cc);

        const bcc = document.getElementById('test-bcc').value;
        if (bcc) formData.append('bcc', bcc);

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
    
    fetchConfigs();
</script>
@endpush
