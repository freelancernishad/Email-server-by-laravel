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
    <!-- Log Details Modal -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Status:</strong> <span id="modal-log-status"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> <span id="modal-log-date"></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Config Key:</strong> <code id="modal-log-config"></code>
                    </div>
                    <div class="mb-3">
                        <strong>IP Address:</strong> <span id="modal-log-ip"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Subject:</strong> <span id="modal-log-subject"></span>
                    </div>
                    <div class="mb-3">
                        <strong>To:</strong> <span id="modal-log-to"></span>
                    </div>
                     <div class="mb-3">
                        <strong>From:</strong> <span id="modal-log-from"></span>
                    </div>
                    <div id="modal-error-container" class="alert alert-danger hidden mb-3">
                        <strong>Error:</strong> <span id="modal-log-error"></span>
                    </div>
                    <div>
                        <strong>Email Body (Preview):</strong>
                        <div class="border p-3 bg-light rounded" style="max-height: 300px; overflow-y: auto;">
                            <!-- Changed from pre to div for HTML rendering -->
                            <div id="modal-log-body"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentLogPage = 1;
    let allConfigs = [];
    let logModal;

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
        document.getElementById('modal-log-status').innerHTML = log.status === 'success' 
            ? '<span class="badge bg-success">Success</span>' 
            : '<span class="badge bg-danger">Failed</span>';
        document.getElementById('modal-log-date').innerText = new Date(log.created_at).toLocaleString();
        document.getElementById('modal-log-config').innerText = log.config_key;
        document.getElementById('modal-log-ip').innerText = log.ip_address;
        document.getElementById('modal-log-subject').innerText = log.subject;
        document.getElementById('modal-log-to').innerText = log.to_email;
        document.getElementById('modal-log-from').innerText = log.from_email || 'Default';
        
        const errorContainer = document.getElementById('modal-error-container');
        if(log.error_message) {
            errorContainer.classList.remove('hidden');
            document.getElementById('modal-log-error').innerText = log.error_message;
        } else {
            errorContainer.classList.add('hidden');
        }

        // Body Preview (Render as HTML)
        const content = log.body || 'No Content';
        // We use a shadow root or iframe usually for total isolation, but for this preview:
        document.getElementById('modal-log-body').innerHTML = content;

        if(!logModal) {
            logModal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
        }
        logModal.show();
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
