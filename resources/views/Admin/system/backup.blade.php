@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Backup & Restore</h1>
            <p class="text-sm text-gray-500 mt-1">Manage database snapshots and system file archives to ensure data safety.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="createBackup()" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm" id="createBackupBtn">
                <i class="fas fa-database mr-2"></i> Create Backup Now
            </button>
        </div>
    </div>

    {{-- 2. STATUS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Last Successful Backup -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex items-center">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-4">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Last Successful Backup</p>
                <p class="text-lg font-bold text-gray-900">{{ $lastBackupTime ?? 'Never' }}</p>
                <p class="text-xs text-gray-500">System Backup</p>
            </div>
        </div>

        <!-- Total Storage -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex items-center">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4">
                <i class="fas fa-hdd text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Storage Used</p>
                <p class="text-lg font-bold text-gray-900">{{ $storageUsed ?? '0 MB' }} / {{ $storageLimit ?? '5 GB' }}</p>
                <p class="text-xs text-gray-500">{{ $totalBackups ?? 0 }} Active Backups</p>
            </div>
        </div>

        <!-- Next Scheduled -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex items-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 mr-4">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Next Scheduled Run</p>
                <p class="text-lg font-bold text-gray-900">{{ $nextScheduled ?? 'Not Scheduled' }}</p>
                <p class="text-xs text-gray-500">{{ $timeUntilNext ?? 'N/A' }}</p>
            </div>
        </div>

    </div>

    {{-- 3. BACKUP HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Existing Backups</h3>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">Auto-retention: Keeps last 30 days</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($backups ?? [] as $backup)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-file-archive text-gray-400 mr-3 text-lg"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $backup['filename'] }}</div>
                                    <span class="text-xs text-green-600 flex items-center mt-0.5">
                                        <i class="fas fa-check mr-1"></i> 
                                        @if($backup['is_verified']) Verified @else Pending @endif
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $backup['formatted_date'] ?? \Carbon\Carbon::createFromTimestamp($backup['created_at'])->format('M j, Y') }} 
                            <span class="text-xs text-gray-400">({{ \Carbon\Carbon::createFromTimestamp($backup['created_at'])->format('g:i A') }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $backup['formatted_size'] ?? '0 MB' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($backup['type'] === 'automated') bg-blue-100 text-blue-800
                                @elseif($backup['type'] === 'manual') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($backup['type']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button onclick="downloadBackup('{{ $backup['filename'] }}')" class="text-gray-600 hover:text-chocolate transition bg-white border border-gray-200 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-download mr-1"></i> Download
                            </button>
                            <button onclick="showRestoreModal('{{ $backup['filename'] }}')" class="text-red-600 hover:text-red-900 transition bg-red-50 border border-red-100 px-3 py-1.5 rounded shadow-sm text-xs font-bold">
                                <i class="fas fa-undo mr-1"></i> Restore
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl text-gray-300 mb-3 block"></i>
                            No backups found yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
            <span>Showing {{ count($backups ?? []) }} backup(s)</span>
            <a href="#" class="text-chocolate hover:underline">View All History</a>
        </div>
    </div>

    {{-- 4. MANUAL RESTORE CARD --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Upload & Restore</h3>
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 relative" id="uploadZone">
                <!-- Hidden file input -->
                <input type="file" id="backupFile" name="backup_file" accept=".sql,.zip" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                
                <!-- Upload zone content -->
                <div class="relative z-0">
                    <div class="mx-auto h-12 w-12 text-gray-400" id="uploadIcon">
                        <i class="fas fa-cloud-upload-alt text-3xl"></i>
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-900" id="uploadText">Upload a backup file to restore</p>
                    <p class="mt-1 text-xs text-gray-500">SQL or ZIP files only (Max 100MB)</p>
                    <p class="mt-1 text-xs text-gray-400" id="fileInfo"></p>
                    
                    <!-- Action buttons -->
                    <div class="mt-4 space-y-2">
                        <button type="button" id="uploadBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-upload mr-2"></i> Select File
                        </button>
                        <button type="submit" id="restoreBtn" class="hidden w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                            <i class="fas fa-undo mr-2"></i> Restore Database
                        </button>
                    </div>
                </div>
                
                <!-- Drag and drop overlay -->
                <div class="absolute inset-0 border-2 border-dashed border-blue-400 bg-blue-50 rounded-lg flex items-center justify-center opacity-0 transition-opacity duration-200 pointer-events-none" id="dragOverlay">
                    <div class="text-center">
                        <i class="fas fa-cloud-upload-alt text-2xl text-blue-500 mb-2"></i>
                        <p class="text-sm font-medium text-blue-700">Drop your backup file here</p>
                    </div>
                </div>
            </div>
        </form>
        <div id="uploadProgress" class="hidden mt-4">
            <div class="bg-blue-100 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-spinner fa-spin text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800" id="uploadStatus">Uploading backup file...</p>
                        <div class="mt-2 w-full bg-blue-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" id="uploadProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- RESTORE CONFIRMATION MODAL -->
<div id="restoreModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('restoreModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirm System Restore
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to restore the database to this snapshot? 
                                <br><br>
                                <span class="font-bold text-red-600">Warning:</span> All current data created after the selected backup will be lost forever. The system will be in maintenance mode during this process.
                            </p>
                        </div>
                        <!-- Confirm Input -->
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Type "RESTORE" to confirm:</label>
                            <input type="text" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmRestoreBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Yes, Restore Database
                </button>
                <button type="button" onclick="document.getElementById('restoreModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global variable to store the selected backup filename for restore
let selectedBackupFile = '';

// Create a new backup
function createBackup() {
    const button = document.getElementById('createBackupBtn');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Backup...';
    
    fetch('{{ route("admin.backups.create") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Reload the page to show the new backup
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to create backup', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while creating backup', 'error');
    })
    .finally(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// File upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const backupFileInput = document.getElementById('backupFile');
    const uploadBtn = document.getElementById('uploadBtn');
    const restoreBtn = document.getElementById('restoreBtn');
    const fileInfo = document.getElementById('fileInfo');
    const uploadForm = document.getElementById('uploadForm');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStatus = document.getElementById('uploadStatus');
    const dragOverlay = document.getElementById('dragOverlay');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.getElementById('uploadText');

    // Click to upload
    uploadBtn.addEventListener('click', function() {
        backupFileInput.click();
    });

    // File input change
    backupFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileSelection(file);
        }
    });

    // Drag and drop functionality with improved event handling
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Add visual feedback
        uploadZone.classList.add('border-blue-400', 'bg-blue-50');
        dragOverlay.style.opacity = '1';
        
        // Change cursor
        uploadZone.style.cursor = 'copy';
    });

    uploadZone.addEventListener('dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadZone.classList.add('border-blue-400', 'bg-blue-50');
        dragOverlay.style.opacity = '1';
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Only remove if we're leaving the upload zone entirely
        if (!uploadZone.contains(e.relatedTarget)) {
            uploadZone.classList.remove('border-blue-400', 'bg-blue-50');
            dragOverlay.style.opacity = '0';
            uploadZone.style.cursor = 'pointer';
        }
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Remove visual feedback
        uploadZone.classList.remove('border-blue-400', 'bg-blue-50');
        dragOverlay.style.opacity = '0';
        uploadZone.style.cursor = 'pointer';
        
        // Handle dropped files
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            handleFileSelection(file);
            
            // Set the file to the hidden input for form submission
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            backupFileInput.files = dataTransfer.files;
        }
    });

    // Handle file selection
    function handleFileSelection(file) {
        // Validate file type
        const validTypes = ['application/sql', 'text/sql', 'application/zip', 'application/x-zip-compressed', ''];
        const validExtensions = ['.sql', '.zip'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
            showNotification('Please select a valid backup file (SQL or ZIP)', 'error');
            resetUploadState();
            return;
        }

        // Validate file size (100MB max)
        if (file.size > 100 * 1024 * 1024) {
            showNotification('File size must be less than 100MB', 'error');
            resetUploadState();
            return;
        }

        // Display file info and update UI
        fileInfo.textContent = `Selected: ${file.name} (${formatBytes(file.size)})`;
        restoreBtn.classList.remove('hidden');
        
        // Update upload button appearance
        uploadBtn.innerHTML = '<i class="fas fa-check mr-2"></i> File Selected';
        uploadBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        uploadBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        
        // Update upload zone appearance
        uploadZone.classList.remove('border-gray-300');
        uploadZone.classList.add('border-green-400', 'bg-green-50');
        
        // Update icon and text
        uploadIcon.innerHTML = '<i class="fas fa-file-check text-green-500 text-3xl"></i>';
        uploadText.textContent = 'File ready for restoration';
        uploadText.classList.add('text-green-700');
        
        showNotification(`File "${file.name}" selected successfully`, 'success');
    }

    // Reset upload state
    function resetUploadState() {
        fileInfo.textContent = '';
        restoreBtn.classList.add('hidden');
        uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i> Select File';
        uploadBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
        uploadBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        uploadZone.classList.remove('border-green-400', 'bg-green-50', 'border-blue-400', 'bg-blue-50');
        uploadZone.classList.add('border-gray-300');
        uploadIcon.innerHTML = '<i class="fas fa-cloud-upload-alt text-3xl"></i>';
        uploadText.textContent = 'Upload a backup file to restore';
        uploadText.classList.remove('text-green-700');
        dragOverlay.style.opacity = '0';
        uploadZone.style.cursor = 'pointer';
    }

    // Form submission
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('backupFile');
        const file = fileInput.files[0];
        
        if (!file) {
            showNotification('Please select a backup file to upload', 'error');
            return;
        }

        // Show progress
        uploadProgress.classList.remove('hidden');
        uploadStatus.textContent = 'Uploading backup file...';
        uploadBtn.disabled = true;
        restoreBtn.disabled = true;

        // Create FormData
        const formData = new FormData();
        formData.append('backup_file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Simulate progress (for demonstration)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            uploadProgressBar.style.width = progress + '%';
        }, 200);

        // Upload file
        fetch('{{ route("admin.backups.restore") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            uploadProgressBar.style.width = '100%';
            uploadStatus.textContent = 'Upload complete!';
            
            setTimeout(() => {
                uploadProgress.classList.add('hidden');
                uploadProgressBar.style.width = '0%';
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reset form using our reset function
                    uploadForm.reset();
                    resetUploadState();
                } else {
                    showNotification(data.message || 'Failed to restore backup', 'error');
                    resetUploadState();
                }
            }, 1000);
        })
        .catch(error => {
            clearInterval(progressInterval);
            uploadProgress.classList.add('hidden');
            uploadProgressBar.style.width = '0%';
            console.error('Error:', error);
            showNotification('An error occurred during upload', 'error');
            resetUploadState();
        })
        .finally(() => {
            uploadBtn.disabled = false;
            restoreBtn.disabled = false;
        });
    });
});

// Download a backup file
function downloadBackup(filename) {
    window.location.href = `{{ route('admin.backups.download', '__filename__') }}`.replace('__filename__', filename);
}

// Show restore modal for specific backup
function showRestoreModal(filename) {
    selectedBackupFile = filename;
    document.getElementById('restoreModal').classList.remove('hidden');
    
    // Update modal title with filename
    const modalTitle = document.getElementById('modal-title');
    modalTitle.textContent = `Confirm System Restore - ${filename}`;
}

// Handle restore confirmation
document.getElementById('confirmRestoreBtn')?.addEventListener('click', function() {
    const confirmInput = document.querySelector('input[type="text"]');
    const confirmationText = confirmInput.value.trim();
    
    if (confirmationText !== 'RESTORE') {
        showNotification('Please type "RESTORE" to confirm', 'error');
        return;
    }
    
    // Close modal
    document.getElementById('restoreModal').classList.add('hidden');
    
    // Show loading notification
    showNotification('Starting database restore process...', 'info');
    
    // Here you would implement the actual restore logic
    // For now, we'll just show a message
    setTimeout(() => {
        showNotification('Restore functionality will be implemented with backend support', 'warning');
    }, 2000);
});

// Format bytes to human readable format
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-black' :
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' :
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-lg">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Auto-refresh backup list every 30 seconds if page is visible
setInterval(() => {
    if (document.visibilityState === 'visible') {
        fetch('{{ route("admin.backups.history") }}')
            .then(response => response.json())
            .then(data => {
                // Update backup count in the status card
                if (data.summary) {
                    document.querySelector('.storage-info')?.setAttribute('data-backup-count', data.summary.total_backups);
                }
            })
            .catch(error => console.log('Auto-refresh failed:', error));
    }
}, 30000);
</script>
@endpush

@endsection