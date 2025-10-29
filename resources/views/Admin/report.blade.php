@extends('Admin.layout.app')

@section('title', 'Reports - WellKenz ERP')

@section('breadcrumb', 'Reports')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Reports & Analytics</h1>
                <p class="text-text-muted mt-2">View purchase and usage summaries</p>
            </div>
            <button class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
                <i class="fas fa-download mr-2"></i>
                Export Report
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Purchases</p>
                <p class="text-3xl font-bold text-text-dark mt-2">$18,250</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Usage</p>
                <p class="text-3xl font-bold text-text-dark mt-2">1,245</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Value</p>
                <p class="text-3xl font-bold text-text-dark mt-2">$84,520</p>
            </div>

            <div class="bg-white border-2 border-red-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock</p>
                <p class="text-3xl font-bold text-text-dark mt-2">23</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Purchase Trend Chart -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Purchase Trend</h3>
                <div class="h-80"> <!-- Fixed height container -->
                    <canvas id="purchaseChart"></canvas>
                </div>
            </div>

            <!-- Stock Distribution Chart -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Stock Distribution</h3>
                <div class="h-80"> <!-- Fixed height container -->
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Purchase Summary Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <h3 class="font-display text-xl font-bold text-text-dark">Purchase Summary</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">PO Count</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Avg Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-text-dark">Baker's Supply Co.</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">3 POs</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$8,450</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">2.3 days</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">EXCELLENT</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-text-dark">PackPro Inc.</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">2 POs</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$3,280</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">4.1 days</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold">GOOD</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-text-dark">Fresh Ingredients Co.</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">4 POs</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$2,150</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">5.7 days</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold">DELAYED</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category Usage -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Usage by Category</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-text-dark">Baking Ingredients</span>
                            <span class="font-bold">45%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2">
                            <div class="bg-caramel h-2" style="width: 45%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-text-dark">Packaging</span>
                            <span class="font-bold">28%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2">
                            <div class="bg-chocolate h-2" style="width: 28%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-text-dark">Dairy & Eggs</span>
                            <span class="font-bold">15%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2">
                            <div class="bg-green-500 h-2" style="width: 15%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-text-dark">Equipment</span>
                            <span class="font-bold">8%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2">
                            <div class="bg-blue-500 h-2" style="width: 8%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Top Moving Items</h3>
                <div class="space-y-3">
                    <div class="flex justify-between p-3 bg-cream-bg">
                        <span class="text-sm text-text-dark">All-Purpose Flour</span>
                        <span class="text-sm font-bold text-red-600">-125 kg</span>
                    </div>
                    <div class="flex justify-between p-3 bg-cream-bg">
                        <span class="text-sm text-text-dark">Fresh Eggs</span>
                        <span class="text-sm font-bold text-red-600">-89 trays</span>
                    </div>
                    <div class="flex justify-between p-3 bg-cream-bg">
                        <span class="text-sm text-text-dark">Cake Boxes</span>
                        <span class="text-sm font-bold text-red-600">-67 units</span>
                    </div>
                    <div class="flex justify-between p-3 bg-cream-bg">
                        <span class="text-sm text-text-dark">Sugar</span>
                        <span class="text-sm font-bold text-red-600">-45 kg</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Purchase Trend Chart
            const purchaseCtx = document.getElementById('purchaseChart');
            if (purchaseCtx) {
                new Chart(purchaseCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Purchases ($)',
                            data: [2400, 3100, 2800, 3500, 2900, 3300, 2600],
                            borderColor: '#c48d3f',
                            backgroundColor: 'rgba(196, 141, 63, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Usage ($)',
                            data: [1800, 2200, 1900, 2400, 2000, 2300, 1900],
                            borderColor: '#3d2817',
                            backgroundColor: 'rgba(61, 40, 23, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Important for fixed container
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Stock Distribution Chart
            const stockCtx = document.getElementById('stockChart');
            if (stockCtx) {
                new Chart(stockCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['In Stock', 'Medium', 'Low Stock'],
                        datasets: [{
                            data: [1856, 468, 23],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(234, 179, 8, 0.8)',
                                'rgba(239, 68, 68, 0.8)'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Important for fixed container
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
