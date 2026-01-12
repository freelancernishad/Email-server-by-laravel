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
             select.innerHTML += `<option value="${conf.key}">${conf.host} (${conf.username || conf.from_address})</option>`;
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
    
    // Init
    fetchConfigs();
</script>
@endpush
