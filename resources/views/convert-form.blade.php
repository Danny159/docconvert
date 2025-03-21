<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Converter</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="file"] {
            display: block;
            margin-bottom: 1rem;
            padding: 0.5rem;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #4a5568;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #2d3748;
        }
        .progress {
            height: 20px;
            margin: 1rem 0;
            background: #edf2f7;
            border-radius: 4px;
            overflow: hidden;
            display: none;
        }
        .progress-bar {
            height: 100%;
            background: #4299e1;
            width: 0%;
            transition: width 0.3s ease;
        }
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        .alert-success {
            background: #c6f6d5;
            color: #2f855a;
        }
        .alert-danger {
            background: #fed7d7;
            color: #c53030;
        }
        #downloadLink {
            display: none;
            margin: 1rem 0;
            padding: 0.75rem 1.5rem;
            background: #48bb78;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PDF to Word Converter</h1>

        <div id="messageArea"></div>

        <form id="convertForm" method="POST" action="{{ route('convert.pdf-to-word') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="pdf_file">Select PDF File</label>
                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" required>
            </div>

            <div class="progress" id="progress">
                <div class="progress-bar" id="progressBar"></div>
            </div>

            <button type="submit" id="convertBtn">Convert to Word</button>
        </form>

        <a href="#" id="downloadLink" download>Download Converted Document</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('convertForm');
            const progressBar = document.getElementById('progressBar');
            const progress = document.getElementById('progress');
            const messageArea = document.getElementById('messageArea');
            const downloadLink = document.getElementById('downloadLink');
            const convertBtn = document.getElementById('convertBtn');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous messages
                messageArea.innerHTML = '';
                downloadLink.style.display = 'none';

                // Show progress bar
                progress.style.display = 'block';
                progressBar.style.width = '0%';

                // Disable button during processing
                convertBtn.disabled = true;
                convertBtn.textContent = 'Converting...';

                const formData = new FormData(form);
                const xhr = new XMLHttpRequest();

                xhr.open('POST', form.action);
                xhr.responseType = 'blob'; // Expect binary data response

                // Track progress
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Create download link
                        const blob = xhr.response;
                        const url = window.URL.createObjectURL(blob);

                        // Get filename from Content-Disposition header if possible
                        const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                        let filename = 'converted-document.docx';

                        if (contentDisposition) {
                            const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                            if (filenameMatch) {
                                filename = filenameMatch[1];
                            }
                        }

                        // Set download link properties
                        downloadLink.href = url;
                        downloadLink.download = filename;
                        downloadLink.style.display = 'block';
                        downloadLink.textContent = 'Download ' + filename;

                        // Auto-trigger download
                        downloadLink.click();

                        // Show success message
                        messageArea.innerHTML = '<div class="alert alert-success">Conversion successful! Your download should start automatically.</div>';
                    } else {
                        // Handle error response
                        messageArea.innerHTML = '<div class="alert alert-danger">Error: Could not convert the document. Please try again.</div>';
                    }

                    // Reset UI
                    convertBtn.disabled = false;
                    convertBtn.textContent = 'Convert to Word';
                    progress.style.display = 'none';
                };

                xhr.onerror = function() {
                    messageArea.innerHTML = '<div class="alert alert-danger">Network error occurred. Please check your connection and try again.</div>';
                    convertBtn.disabled = false;
                    convertBtn.textContent = 'Convert to Word';
                    progress.style.display = 'none';
                };

                // Send the form data
                xhr.send(formData);
            });
        });
    </script>
</body>
</html>
