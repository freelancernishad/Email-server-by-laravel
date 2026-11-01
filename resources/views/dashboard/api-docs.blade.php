@extends('layouts.dashboard')

@section('content')
<div id="section-api-docs">
    <h3 class="mb-4">API Documentation</h3>
    
    <div class="card p-4 mb-4">
        <h4>Endpoint</h4>
        <div class="alert alert-info">
            <span class="badge bg-success me-2">POST</span> <span class="font-monospace">{{ url('/api/send-email') }}</span>
        </div>
        
        <h5 class="mt-4">Parameters</h5>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
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
                    <td><span class="badge bg-danger">Yes</span></td>
                    <td>The unique key of your email configuration.</td>
                </tr>
                <tr>
                    <td><code>to</code></td>
                    <td>String (Email)</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                    <td>Recipient email address (or comma-separated list of addresses).</td>
                </tr>
                <tr>
                    <td><code>subject</code></td>
                    <td>String</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                    <td>Email subject line.</td>
                </tr>
                <tr>
                    <td><code>body</code></td>
                    <td>String (HTML)</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                    <td>Email content (supports HTML).</td>
                </tr>
                <tr>
                    <td><code>from_email</code></td>
                    <td>String (Email)</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
                    <td>Override the "From" address defined in configuration.</td>
                </tr>
                <tr>
                    <td><code>from_name</code></td>
                    <td>String</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
                    <td>Override the "From" sender name defined in configuration.</td>
                </tr>
                <tr>
                    <td><code>reply_to</code></td>
                    <td>String (Email)</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
                    <td>Override the "Reply-To" email defined in configuration (also accepts <code>reply_to_email</code>).</td>
                </tr>
                <tr>
                    <td><code>reply_to_name</code></td>
                    <td>String</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
                    <td>Override the "Reply-To" name defined in configuration.</td>
                </tr>
                <tr>
                    <td><code>cc</code></td>
                    <td>String</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
                    <td>Comma-separated CC email addresses (e.g. <code>cc1@example.com, cc2@example.com</code>).</td>
                </tr>
                <tr>
                    <td><code>bcc</code></td>
                    <td>String</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
                    <td>Comma-separated BCC email addresses (e.g. <code>bcc1@example.com, bcc2@example.com</code>).</td>
                </tr>
                <tr>
                    <td><code>attachments[]</code></td>
                    <td>File (Array)</td>
                    <td><span class="badge bg-secondary">Optional</span></td>
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
            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('go')">Go</a></li>
            <li class="nav-item"><a class="nav-link" onclick="showCodeTab('ruby')">Ruby</a></li>
        </ul>

        <div class="bg-dark text-white p-3 rounded-bottom position-relative">
             <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" onclick="copyCode()">Copy</button>
             <pre><code id="code-display" class="language-bash">Select a config to generate code...</code></pre>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentLang = 'curl';
    let allConfigs = [];

    async function fetchConfigs() {
        try {
            const res = await axios.get(`${API_URL}/email-config`);
            allConfigs = res.data;
            populateDocsSelect();
            updateCodeSnippets();
        } catch (err) {
            console.error(err);
        }
    }

    function populateDocsSelect() {
         const select = document.getElementById('docs-conf-select');
         const currentVal = select.value; 
         select.innerHTML = '<option value="">-- Select Config --</option>';
         allConfigs.forEach(conf => {
             select.innerHTML += `<option value="${conf.key}">${conf.host || 'Brevo API'} (${conf.username || conf.from_address})</option>`;
         });
         if(currentVal) select.value = currentVal;
    }

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
 -F "body=<h1>Hello World</h1><p>Sent from Email Server</p>" \\
 -F "reply_to=support@example.com" \\
 -F "reply_to_name=Customer Support" \\
 -F "cc=supervisor@example.com" \\
 -F "bcc=audit@example.com" \\
 -F "attachments[]=@/path/to/invoice.pdf"`,
            php: '<' + `?php
require 'vendor/autoload.php';

use GuzzleHttp\\Client;

$client = new Client();
$response = $client->post('${url}', [
    'multipart' => [
        [ 'name' => 'config_key',    'contents' => '${key}' ],
        [ 'name' => 'to',            'contents' => 'recipient@example.com' ],
        [ 'name' => 'subject',       'contents' => 'Test Email' ],
        [ 'name' => 'body',          'contents' => '<h1>Hello World</h1><p>Sent from Email Server</p>' ],
        [ 'name' => 'reply_to',      'contents' => 'support@example.com' ],
        [ 'name' => 'reply_to_name', 'contents' => 'Customer Support' ],
        [ 'name' => 'cc',            'contents' => 'supervisor@example.com' ],
        [ 'name' => 'bcc',           'contents' => 'audit@example.com' ],
        [ 'name' => 'attachments[]', 'contents' => fopen('/path/to/invoice.pdf', 'r') ],
    ]
]);

echo $response->getBody();`,
            python: `import requests

url = "${url}"
files = [
    ('attachments[]', ('invoice.pdf', open('/path/to/invoice.pdf', 'rb'), 'application/pdf'))
]
payload = {
    'config_key': '${key}',
    'to': 'recipient@example.com',
    'subject': 'Test Email',
    'body': '<h1>Hello World</h1><p>Sent from Email Server</p>',
    'reply_to': 'support@example.com',
    'reply_to_name': 'Customer Support',
    'cc': 'supervisor@example.com',
    'bcc': 'audit@example.com'
}

response = requests.post(url, data=payload, files=files)
print(response.json())`,
            node: `const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

let data = new FormData();
data.append('config_key', '${key}');
data.append('to', 'recipient@example.com');
data.append('subject', 'Test Email');
data.append('body', '<h1>Hello World</h1><p>Sent from Email Server</p>');
data.append('reply_to', 'support@example.com');
data.append('reply_to_name', 'Customer Support');
data.append('cc', 'supervisor@example.com');
data.append('bcc', 'audit@example.com');
data.append('attachments[]', fs.createReadStream('/path/to/invoice.pdf'));

axios.post('${url}', data, {
    headers: {
        ...data.getHeaders()
    }
})
.then(res => console.log(res.data))
.catch(err => console.error(err.response ? err.response.data : err.message));`,
            go: `package main

import (
    "bytes"
    "fmt"
    "io"
    "mime/multipart"
    "net/http"
    "os"
)

func main() {
    url := "${url}"
    payload := &bytes.Buffer{}
    writer := multipart.NewWriter(payload)

    _ = writer.WriteField("config_key", "${key}")
    _ = writer.WriteField("to", "recipient@example.com")
    _ = writer.WriteField("subject", "Test Email")
    _ = writer.WriteField("body", "<h1>Hello World</h1>")
    _ = writer.WriteField("reply_to", "support@example.com")
    _ = writer.WriteField("reply_to_name", "Customer Support")
    _ = writer.WriteField("cc", "supervisor@example.com")
    _ = writer.WriteField("bcc", "audit@example.com")

    file, err := os.Open("/path/to/invoice.pdf")
    if err == nil {
        defer file.Close()
        part, _ := writer.CreateFormFile("attachments[]", "invoice.pdf")
        _, _ = io.Copy(part, file)
    }

    _ = writer.Close()

    req, _ := http.NewRequest("POST", url, payload)
    req.Header.Set("Content-Type", writer.FormDataContentType())

    client := &http.Client{}
    res, err := client.Do(req)
    if err != nil {
        panic(err)
    }
    defer res.Body.Close()

    fmt.Println("Status:", res.Status)
}`,
            ruby: `require 'net/http'
require 'uri'

uri = URI('${url}')
request = Net::HTTP::Post.new(uri)
form_data = [
  ['config_key', '${key}'],
  ['to', 'recipient@example.com'],
  ['subject', 'Test Email'],
  ['body', '<h1>Hello World</h1>'],
  ['reply_to', 'support@example.com'],
  ['reply_to_name', 'Customer Support'],
  ['cc', 'supervisor@example.com'],
  ['bcc', 'audit@example.com'],
  ['attachments[]', File.open('/path/to/invoice.pdf')]
]
request.set_form(form_data, 'multipart/form-data')

response = Net::HTTP.start(uri.hostname, uri.port, use_ssl: uri.scheme == 'https') do |http|
  http.request(request)
end

puts response.body`
        };
        
        document.getElementById('code-display').innerText = snippets[currentLang] || '';
    }

    function copyCode() {
        const code = document.getElementById('code-display').innerText;
        navigator.clipboard.writeText(code);
        const btn = event.target;
        const original = btn.innerText;
        btn.innerText = 'Copied!';
        setTimeout(() => btn.innerText = original, 1500);
    }
    
    // Init
    fetchConfigs();
</script>
@endpush
