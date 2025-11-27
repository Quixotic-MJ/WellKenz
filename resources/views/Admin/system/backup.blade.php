@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Backup & Restore</h1>
            <p class="text-sm text-gray-500">Manage database snapshots and system file archives to ensure data safety.</p>
        </div>
        <div>
            <button onclick="createBackup()" id="createBackupBtn" 
                class="inline-flex items-center justify-center px-6 py-3 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-database mr-2"></i> Create Backup Now
            </button>
        </div>
    </div>

    {{-- 2. STATUS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="bg-white p-6 rounded-xl border border-border-soft shadow-sm flex items-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600 mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Last Success</p>
                <p class="font-display text-xl font-bold text-chocolate mt-1">{{ $lastBackupTime ?? 'Never' }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide font-bold">System Backup</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-border-soft shadow-sm flex items-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-full w-1 bg-caramel"></div>
            <div class="w-12 h-12 rounded-full bg-cream-bg flex items-center justify-center text-caramel mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-hdd text-xl"></i>
            </div>
            <div class="storage-info" data-backup-count="{{ $totalBackups ?? 0 }}">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Storage Used</p>
                <p class="font-display text-xl font-bold text-chocolate mt-1">{{ $storageUsed ?? '0 MB' }} <span class="text-xs text-gray-400 font-sans font-normal">/ {{ $storageLimit ?? '5 GB' }}</span></p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide font-bold">{{ $totalBackups ?? 0 }} Active Files</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-border-soft shadow-sm flex items-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-full w-1 bg-chocolate"></div>
            <div class="w-12 h-12 rounded-full bg-chocolate/10 flex items-center justify-center text-chocolate mr-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Next Run</p>
                <p class="font-display text-xl font-bold text-chocolate mt-1">{{ $nextScheduled ?? 'Not Scheduled' }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide font-bold">{{ $timeUntilNext ?? 'N/A' }}</p>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- 3. BACKUP HISTORY TABLE (Left 2 Columns) --}}
        <div class="lg:col-span-2 bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                <h3 class="font-display text-lg font-bold text-chocolate">Backup Archives</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white border border-border-soft text-gray-500">
                    <i class="fas fa-history mr-1.5 text-caramel"></i> 30-Day Retention
                </span>
            </div>
            <div class="overflow-x-auto flex-grow">
                <table class="min-w-full divide-y divide-border-soft">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">File Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Size</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Type</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-border-soft">
                        @forelse($backups ?? [] as $backup)
                        <tr class="hover:bg-cream-bg transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-white border border-border-soft rounded-lg flex items-center justify-center text-chocolate shadow-sm">
                                        <i class="fas fa-file-archive text-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-chocolate">{{ $backup['filename'] }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $backup['formatted_date'] ?? \Carbon\Carbon::createFromTimestamp($backup['created_at'])->format('M j, Y • g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                {{ $backup['formatted_size'] ?? '0 MB' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 inline-flex text-[10px] font-bold uppercase tracking-wide rounded-full 
                                    @if($backup['type'] === 'automated') bg-chocolate/10 text-chocolate border border-chocolate/20
                                    @elseif($backup['type'] === 'manual') bg-caramel/10 text-caramel border border-caramel/20
                                    @else bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                    {{ ucfirst($backup['type']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <button onclick="downloadBackup('{{ $backup['filename'] }}')" class="text-chocolate hover:text-white hover:bg-chocolate p-2 rounded-lg transition-all tooltip" title="Download">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button onclick="showRestoreModal('{{ $backup['filename'] }}')" class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-lg transition-all tooltip" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-archive text-gray-400"></i>
                                    </div>
                                    <p>No backups found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. MANUAL RESTORE CARD (Right 1 Column) --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6 sticky top-6">
                <h3 class="font-display text-lg font-bold text-chocolate mb-4">Manual Upload</h3>
                <p class="text-xs text-gray-500 mb-4">Upload a .sql or .zip backup file from a local source to restore the system state.</p>
                
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="relative group">
                        <div id="uploadZone" class="border-2 border-dashed border-border-soft rounded-xl p-8 text-center transition-all duration-300 hover:border-caramel hover:bg-cream-bg bg-gray-50">
                            <input type="file" id="backupFile" name="backup_file" accept=".sql,.zip" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            
                            <div class="relative z-0 space-y-3">
                                <div id="uploadIcon" class="mx-auto h-12 w-12 rounded-full bg-white border border-border-soft flex items-center justify-center text-caramel shadow-sm group-hover:scale-110 transition-transform">
                                    <i class="fas fa-cloud-upload-alt text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-chocolate" id="uploadText">Click or Drag File</p>
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-wide">Max 100MB • SQL/ZIP</p>
                                    <p class="text-xs text-caramel font-medium mt-2 break-all" id="fileInfo"></p>
                                </div>
                            </div>

                            <div id="dragOverlay" class="absolute inset-0 bg-caramel/10 rounded-xl border-2 border-caramel flex items-center justify-center opacity-0 transition-opacity pointer-events-none">
                                <span class="font-bold text-caramel">Drop File Here</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <button type="button" id="uploadBtn" class="hidden w-full inline-flex items-center justify-center px-4 py-2 border border-border-soft text-sm font-bold rounded-lg text-gray-600 bg-white hover:bg-gray-50 focus:outline-none transition-all">
                            Select File
                        </button>
                        <button type="submit" id="restoreBtn" class="hidden w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-chocolate hover:bg-chocolate-dark shadow-md transition-all">
                            <i class="fas fa-undo mr-2"></i> Confirm Restore
                        </button>
                    </div>
                </form>

                <div id="uploadProgress" class="hidden mt-4 bg-cream-bg p-4 rounded-lg border border-border-soft">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-chocolate" id="uploadStatus">Uploading...</span>
                        <i class="fas fa-circle-notch fa-spin text-caramel text-xs"></i>
                    </div>
                    <div class="w-full bg-white rounded-full h-2 border border-border-soft overflow-hidden">
                        <div id="uploadProgressBar" class="bg-caramel h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL: RESTORE CONFIRMATION --}}
<div id="restoreModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="document.getElementById('restoreModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-50 border border-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="modal-title">
                            Confirm System Restore
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                You are about to restore the database to a previous state.
                                <br><br>
                                <span class="font-bold text-red-600 block bg-red-50 p-2 rounded border border-red-100"><i class="fas fa-hand-paper mr-1"></i> Warning: Current data created after this backup will be permanently lost.</span>
                            </p>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Type "RESTORE" to confirm:</label>
                            <input type="text" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" id="confirmRestoreBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Yes, Restore Database
                </button>
                <button type="button" onclick="document.getElementById('restoreModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
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
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
    
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
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// File upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const backupFileInput = document.getElementById('backupFile');
    const restoreBtn = document.getElementById('restoreBtn');
    const fileInfo = document.getElementById('fileInfo');
    const uploadForm = document.getElementById('uploadForm');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStatus = document.getElementById('uploadStatus');
    const dragOverlay = document.getElementById('dragOverlay');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.getElementById('uploadText');

    // File input change
    backupFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileSelection(file);
        }
    });

    // Drag and drop functionality
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadZone.classList.add('border-caramel', 'bg-cream-bg');
        dragOverlay.style.opacity = '1';
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!uploadZone.contains(e.relatedTarget)) {
            uploadZone.classList.remove('border-caramel', 'bg-cream-bg');
            dragOverlay.style.opacity = '0';
        }
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        uploadZone.classList.remove('border-caramel', 'bg-cream-bg');
        dragOverlay.style.opacity = '0';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            handleFileSelection(file);
            
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            backupFileInput.files = dataTransfer.files;
        }
    });

    // Handle file selection
    function handleFileSelection(file) {
        const validExtensions = ['.sql', '.zip'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        // Simple client-side validation logic (adjust based on real needs)
        if (!validExtensions.includes(fileExtension) && file.type !== 'application/sql' && file.type !== 'application/zip') {
             // In a real scenario, you'd check MIME types strictly
        }

        if (file.size > 100 * 1024 * 1024) {
            showNotification('File size must be less than 100MB', 'error');
            resetUploadState();
            return;
        }

        fileInfo.textContent = `Selected: ${file.name} (${formatBytes(file.size)})`;
        restoreBtn.classList.remove('hidden');
        
        // Visual Update
        uploadZone.classList.add('border-green-400', 'bg-green-50');
        uploadIcon.innerHTML = '<i class="fas fa-file-check text-green-500 text-xl"></i>';
        uploadIcon.classList.remove('text-caramel');
        uploadText.textContent = 'File Ready';
        uploadText.classList.add('text-green-700');
        
        showNotification(`File selected successfully`, 'success');
    }

    function resetUploadState() {
        fileInfo.textContent = '';
        restoreBtn.classList.add('hidden');
        uploadZone.classList.remove('border-green-400', 'bg-green-50');
        uploadIcon.innerHTML = '<i class="fas fa-cloud-upload-alt text-xl"></i>';
        uploadIcon.classList.add('text-caramel');
        uploadText.textContent = 'Click or Drag File';
        uploadText.classList.remove('text-green-700');
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

        uploadProgress.classList.remove('hidden');
        uploadStatus.textContent = 'Uploading...';
        restoreBtn.disabled = true;

        const formData = new FormData();
        formData.append('backup_file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Simulated Progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 90) progress = 90;
            uploadProgressBar.style.width = progress + '%';
        }, 300);

        fetch('{{ route("admin.backups.restore") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            uploadProgressBar.style.width = '100%';
            uploadStatus.textContent = 'Complete!';
            
            setTimeout(() => {
                uploadProgress.classList.add('hidden');
                uploadProgressBar.style.width = '0%';
                
                if (data.success) {
                    showNotification(data.message, 'success');
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
            console.error('Error:', error);
            showNotification('An error occurred during upload', 'error');
            resetUploadState();
        })
        .finally(() => {
            restoreBtn.disabled = false;
        });
    });
});

function downloadBackup(filename) {
    window.location.href = `{{ route('admin.backups.download', '__filename__') }}`.replace('__filename__', filename);
}

function showRestoreModal(filename) {
    selectedBackupFile = filename;
    document.getElementById('restoreModal').classList.remove('hidden');
    document.getElementById('modal-title').textContent = `Restore: ${filename}`;
}

document.getElementById('confirmRestoreBtn')?.addEventListener('click', function() {
    const confirmInput = document.querySelector('input[type="text"]');
    const confirmationText = confirmInput.value.trim();
    
    if (confirmationText !== 'RESTORE') {
        showNotification('Please type "RESTORE" to confirm', 'error');
        return;
    }
    
    document.getElementById('restoreModal').classList.add('hidden');
    showNotification('Starting database restore process...', 'info');
    
    // Simulate backend call
    setTimeout(() => {
        showNotification('System is restoring (Backend logic placeholder)', 'warning');
    }, 2000);
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Simple Notification Toaster
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-chocolate');
    
    notification.className = `fixed top-5 right-5 p-4 rounded-lg shadow-xl z-50 text-white flex items-center gap-3 animate-fade-in-down ${bgClass}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
        <span class="font-bold text-sm">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

// Auto-refresh backup list
setInterval(() => {
    if (document.visibilityState === 'visible') {
        fetch('{{ route("admin.backups.history") }}')
            .then(response => response.json())
            .then(data => {
                if (data.summary) {
                    const info = document.querySelector('.storage-info');
                    if(info) info.setAttribute('data-backup-count', data.summary.total_backups);
                }
            })
            .catch(error => console.log('Auto-refresh failed:', error));
    }
}, 30000);          
</script>
@endpush
@endsection 