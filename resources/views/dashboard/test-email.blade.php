@extends('layouts.dashboard')

@section('content')
<div id="section-test-email">
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
