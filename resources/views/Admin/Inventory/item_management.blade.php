@extends('Admin.layout.app')

@section('title', 'Category & Item Management - WellKenz ERP')
@section('breadcrumb', 'Category & Item Management')

@section('content')
    <div class="space-y-6">

        <!-- toast -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- header card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Category & Item Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Core bakery-supply tracking: categories, items, stock, reorder,
                        expiry</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="openCategoryModal()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                        <i class="fas fa-tags mr-2"></i>New Category
                    </button>
                    <button onclick="openCreateModal()"
                        class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                        <i class="fas fa-plus-circle mr-2"></i>New Item
                    </button>
                </div>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Categories</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $categoriesCount ?? 0 }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Items</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalItems ?? 0 }}</p>
            </div>
            <div class="bg-white border border-amber-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Low-Stock</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $lowStockCount ?? 0 }}</p>
            </div>
            <div class="bg-white border border-rose-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Expiring ≤ 30 d</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ $expiringCount ?? 0 }}
                </p>
            </div>
        </div>

        <!-- items table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">All Items</h3>
                <div class="flex items-center space-x-3">
                    <select onchange="filterTable(this.value)"
                        class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="all">All</option>
                        <option value="low">Low-Stock</option>
                        <option value="expiry">Expiring ≤ 30 d</option>
                    </select>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search items…" onkeyup="searchTable(this.value)"
                            class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <button type="button" onclick="clearSearch()" id="clearBtn"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                                class="fas fa-times text-xs"></i></button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="itemsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('name')">Item <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reorder</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="itemsTableBody">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50 transition item-row" data-name="{{ strtolower($item->item_name) }}"
                                data-low="{{ ($item->item_stock ?? 0) <= ($item->reorder_level ?? 0) ? '1' : '0' }}"
                                data-expiry="{{ isset($item->item_expire_date) && $item->item_expire_date && \Carbon\Carbon::parse($item->item_expire_date)->diffInDays(now(), false) <= 30 ? '1' : '0' }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $item->item_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->cat_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_unit }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_stock }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $item->reorder_level }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if (!empty($item->item_expire_date))
                                        {{ \Carbon\Carbon::parse($item->item_expire_date)->format('M d, Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if (($item->item_stock ?? 0) <= ($item->reorder_level ?? 0))
                                        <span
                                            class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Low</span>
                                    @elseif(!empty($item->item_expire_date) && \Carbon\Carbon::parse($item->item_expire_date)->diffInDays(now(), false) <= 30)
                                        <span
                                            class="inline-block px-2 py-1 bg-rose-100 text-rose-700 text-xs font-semibold rounded">Expiring</span>
                                    @else
                                        <span
                                            class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">OK</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openEditModal({{ $item->item_id }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="Edit">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <button data-id="{{ $item->item_id }}" data-name="{{ e($item->item_name) }}" data-stock="{{ (int)($item->item_stock ?? 0) }}" onclick="openStockFromBtn(this)"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded transition" title="Adjust stock">
                                            <i class="fas fa-balance-scale text-sm"></i>
                                        </button>
                                        <button data-id="{{ $item->item_id }}" data-name="{{ e($item->item_name) }}" onclick="openDeleteModalFromBtn(this)"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded transition" title="Delete">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $items->count() }}</span> of {{ $items->count() }} items
            </div>
        </div>

        <!-- ====== MODALS  ====== -->
        @include('Admin.Inventory.Item.category')
        @include('Admin.Inventory.Item.create')
        @include('Admin.Inventory.Item.edit')
        @include('Admin.Inventory.Item.stock')
        @include('Admin.Inventory.Item.delete')

    </div>

    <script>
        /* light helpers */
        let currentId = null;
        const ADMIN_BASE = "{{ url('/admin') }}";

        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        function closeModals() {
            ['categoryModal', 'createItemModal', 'editItemModal', 'stockItemModal', 'deleteItemModal'].forEach(id =>
                document.getElementById(id)?.classList.add('hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search / filter */
        function filterTable(val) {
            const rows = document.querySelectorAll('.item-row');
            let visible = 0;
            rows.forEach(r => {
                let ok = val === 'all';
                if (!ok && val === 'low') ok = r.dataset.low === '1';
                if (!ok && val === 'expiry') ok = r.dataset.expiry === '1';
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
        }

        function searchTable(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.item-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.dataset.name.includes(Q) || r.textContent.toLowerCase().includes(Q);
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
            const btn = document.getElementById('clearBtn');
            Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchTable('');
            document.getElementById('clearBtn').classList.add('hidden');
        }

        /* sort */
        let sortField = 'name',
            sortDir = 'asc';

        function sortTable(f) {
            if (sortField === f) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else {
                sortField = f;
                sortDir = 'asc';
            }
            const tbody = document.getElementById('itemsTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            rows.sort((a, b) => {
                const A = a.dataset[sortField].toLowerCase(),
                    B = b.dataset[sortField].toLowerCase();
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

        /* modal openers */
        function openCategoryModal() {
            closeModals();
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function openCreateModal() {
            closeModals();
            document.getElementById('createItemModal').classList.remove('hidden');
        }

        function openEditModal(id) {
            currentId = id;
            /* ajax fetch then fill form */
            fetch(`${ADMIN_BASE}/items/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    const form = document.getElementById('editItemForm');
                    form.action = `${ADMIN_BASE}/items/${id}`;
                    form.querySelector('input[name="item_name"]').value = data.item_name || '';
                    form.querySelector('select[name="cat_id"]').value = data.cat_id || '';
                    form.querySelector('input[name="item_unit"]').value = data.item_unit || '';
                    form.querySelector('input[name="reorder_level"]').value = data.reorder_level || '';
                    form.querySelector('input[name="item_expire_date"]').value = data.item_expire_date || '';
                    form.querySelector('textarea[name="item_description"]').value = data.item_description || '';
                    document.getElementById('editItemModal').classList.remove('hidden');
                })
                .catch(() => {
                    showMessage('Failed to load item details', 'error');
                });
        }

        function openStockModal(id, name, current) {
            currentId = id;
            document.getElementById('stockItemName').textContent = name;
            document.getElementById('currentStock').value = current;
            const form = document.querySelector('#stockItemModal form');
            if (form) form.action = `${ADMIN_BASE}/items/${id}/stock`;
            document.getElementById('stockItemModal').classList.remove('hidden');
        }

        function openStockFromBtn(btn) {
            const id = btn.dataset.id;
            const name = btn.dataset.name || '';
            const current = parseInt(btn.dataset.stock || '0', 10);
            openStockModal(id, name, current);
        }

        function openDeleteModal(id, name) {
            currentId = id;
            document.getElementById('deleteItemName').textContent = name;
            const form = document.getElementById('deleteItemForm');
            if (form) form.action = `${ADMIN_BASE}/items/${id}`;
            document.getElementById('deleteItemModal').classList.remove('hidden');
        }

        function openDeleteModalFromBtn(btn) {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            openDeleteModal(id, name);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Category form
            document.querySelector('#categoryModal form').addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Category created successfully!', 'success');
                            closeModals();
                            location.reload();
                        } else {
                            showMessage('Failed to create category.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred.', 'error');
                    });
            });

            // Create form
            document.querySelector('#createItemModal form').addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Item created successfully!', 'success');
                            closeModals();
                            location.reload();
                        } else {
                            showMessage('Failed to create item.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred.', 'error');
                    });
            });

            // Edit form
            document.getElementById('editItemForm').addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Item updated successfully!', 'success');
                            closeModals();
                            location.reload();
                        } else {
                            showMessage('Failed to update item.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred.', 'error');
                    });
            });

            // Stock form
            document.querySelector('#stockItemModal form').addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Stock adjusted successfully!', 'success');
                            closeModals();
                            location.reload();
                        } else {
                            showMessage('Failed to adjust stock.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred.', 'error');
                    });
            });

            // Delete form
            document.getElementById('deleteItemForm').addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message || 'Item deleted successfully!', 'success');
                            closeModals();
                            location.reload();
                        } else {
                            showMessage(data.message || 'Failed to delete item.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred.', 'error');
                    });
            });
        });
    </script>
@endsection
