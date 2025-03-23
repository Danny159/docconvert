<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Conversion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Document Converter
                </h1>
                <p class="mt-3 text-xl text-gray-500">
                    Convert your documents to different formats easily
                </p>
            </div>

            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="px-6 py-8">
                    @if (session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 101.414 1.414L10 11.414l1.293 1.293a1 1 001.414-1.414L11.414 10l1.293-1.293a1 1 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        {{ session('error') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form id="conversion-form" action="{{ route('file.convert') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">Select File</label>
                            <div id="dropzone" class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex flex-col text-sm text-gray-600">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                            <span id="file-prompt">Upload a file</span>
                                            <input id="file" name="file" type="file" class="sr-only" required>
                                        </label>
                                        <p class="pt-1">or drag and drop</p>
                                    </div>
                                    <p id="file-name" class="mt-2 text-sm text-indigo-700 font-medium hidden"></p>
                                    <p class="text-xs text-gray-500">
                                        DOC, DOCX, XLS, XLSX, PPT, PPTX up to 10MB
                                    </p>
                                </div>
                            </div>
                            @error('file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="format" class="block text-sm font-medium text-gray-700">Convert To</label>
                            <select id="format" name="format" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="pdf">PDF</option>
                                <option value="docx" selected>DOCX</option>
                                <option value="xlsx">XLSX</option>
                                <option value="pptx">PPTX</option>
                                <option value="txt">TXT</option>
                            </select>
                            @error('format')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button id="convert-button" type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                Convert File
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Supported formats: PDF, DOCX, XLSX, PPTX, and TXT</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('file');
            const fileNameDisplay = document.getElementById('file-name');
            const filePrompt = document.getElementById('file-prompt');
            const form = document.getElementById('conversion-form');
            const convertButton = document.getElementById('convert-button');

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileNameDisplay.textContent = `Selected: ${file.name}`;
                    fileNameDisplay.classList.remove('hidden');
                    filePrompt.textContent = 'Change file';
                    dropzone.classList.add('border-indigo-500');
                    dropzone.classList.add('bg-indigo-50');
                }
            });

            // Handle form submission - add loading state
            form.addEventListener('submit', function() {
                convertButton.disabled = true;
                convertButton.classList.add('bg-indigo-400');
                convertButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Converting...
                `;
            });

            // Drag and drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropzone.classList.add('border-indigo-500');
                dropzone.classList.add('bg-indigo-50');
            }

            function unhighlight() {
                dropzone.classList.remove('border-indigo-500');
                dropzone.classList.remove('bg-indigo-50');
            }

            dropzone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files && files.length) {
                    fileInput.files = files;
                    const file = files[0];
                    fileNameDisplay.textContent = `Selected: ${file.name}`;
                    fileNameDisplay.classList.remove('hidden');
                    filePrompt.textContent = 'Change file';
                    highlight();
                }
            }
        });
    </script>
</body>
</html>