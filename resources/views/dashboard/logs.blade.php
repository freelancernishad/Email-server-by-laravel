@extends('layouts.dashboard')

@section('content')
<div id="section-logs">
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
@endsection

@push('scripts')
<script>
    let currentLogPage = 1;
    let allConfigs = [];

    // Ideally we would fetch configs just for the filter if needed, or if we want to show config names.
    async function fetchConfigsForFilter() {
        try {
            const res = await axios.get(`${API_URL}/email-config`);
            allConfigs = res.data;
            populateLogConfigSelect();
        } catch (err) {
            console.error(err);
        }
    }

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

    // Init
    fetchConfigsForFilter();
    fetchLogs();
</script>
@endpush
