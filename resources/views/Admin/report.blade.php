@extends('Admin.layout.app')

@section('title', 'Reports & Analytics - WellKenz ERP')

@section('breadcrumb')
<div class="flex items-center space-x-2 text-sm">
    <span class="text-text-muted">Analytics</span>
    <span class="text-border-soft">/</span>
    <span class="text-text-dark font-semibold">Reports & Analytics</span>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Reports & Analytics</h1>
            <p class="text-text-muted mt-2">Generate summaries of purchases, usage, and remaining stocks</p>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Date Range Selector -->
            <div class="flex items-center space-x-2 bg-white border-2 border-border-soft rounded-lg px-3 py-2">
                <i class="fas fa-calendar text-text-muted"></i>
                <select id="reportPeriod" onchange="updateReportData()" class="bg-transparent border-none focus:outline-none text-text-dark font-semibold">
                    <option value="daily">Daily Report</option>
                    <option value="weekly" selected>Weekly Report</option>
                    <option value="monthly">Monthly Report</option>
                    <option value="quarterly">Quarterly Report</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            
            <!-- Export Button -->
            <button class="flex items-center space-x-2 px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition-colors rounded-lg">
                <i class="fas fa-download"></i>
                <span class="font-semibold">Export Report</span>
            </button>
            
            <!-- Generate Report -->
            <button class="flex items-center space-x-2 px-4 py-2 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition-colors rounded-lg">
                <i class="fas fa-refresh"></i>
                <span class="font-semibold">Generate</span>
            </button>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Purchases -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Purchases</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1" id="totalPurchases">$18,250</p>
                    <p class="text-xs text-green-600 mt-1 font-semibold" id="purchaseTrend">↑ 12% vs last period</p>
                </div>
                <div class="w-10 h-10 bg-green-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-shopping-cart text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Stock Usage -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Usage</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1" id="stockUsage">1,245 units</p>
                    <p class="text-xs text-blue-600 mt-1 font-semibold" id="usageTrend">↑ 8% vs last period</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-chart-line text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Remaining Stock Value -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Value</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1" id="stockValue">$84,520</p>
                    <p class="text-xs text-caramel mt-1 font-semibold" id="valueTrend">↓ 3% vs last period</p>
                </div>
                <div class="w-10 h-10 bg-caramel/20 flex items-center justify-center rounded-full">
                    <i class="fas fa-dollar-sign text-caramel"></i>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock Alerts</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1" id="lowStockCount">23</p>
                    <p class="text-xs text-red-600 mt-1 font-semibold" id="alertTrend">↑ 5 new alerts</p>
                </div>
                <div class="w-10 h-10 bg-red-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Purchases vs Usage Trend -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-6 rounded-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Purchases vs Usage Trend</h3>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 bg-caramel text-white text-xs font-semibold rounded">Weekly</button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate">Monthly</button>
                </div>
            </div>
            <div class="h-80 flex items-center justify-center bg-cream-bg rounded-lg border border-border-soft">
                <div class="text-center">
                    <i class="fas fa-chart-bar text-4xl text-text-muted mb-3"></i>
                    <p class="text-text-muted font-semibold">Purchases vs Usage Chart</p>
                    <p class="text-sm text-text-muted mt-1">Visualization of purchase orders and stock usage over time</p>
                </div>
                <!-- In a real application, this would be a chart from Chart.js or similar -->
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-sm text-text-muted">Total Purchases</p>
                    <p class="text-lg font-bold text-green-600">$18,250</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-text-muted">Total Usage Value</p>
                    <p class="text-lg font-bold text-blue-600">$12,480</p>
                </div>
            </div>
        </div>

        <!-- Stock Level Distribution -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-6 rounded-lg">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Stock Level Distribution</h3>
                <div class="text-sm text-text-muted">
                    <span class="font-semibold" id="totalItems">2,347</span> Total Items
                </div>
            </div>
            <div class="h-80 flex items-center justify-center bg-cream-bg rounded-lg border border-border-soft">
                <div class="text-center">
                    <i class="fas fa-chart-pie text-4xl text-text-muted mb-3"></i>
                    <p class="text-text-muted font-semibold">Stock Distribution Chart</p>
                    <p class="text-sm text-text-muted mt-1">Breakdown of items by stock status</p>
                </div>
                <!-- In a real application, this would be a pie chart -->
            </div>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="p-2 bg-green-50 rounded">
                    <p class="text-xs text-text-muted">In Stock</p>
                    <p class="text-sm font-bold text-green-600">1,856</p>
                </div>
                <div class="p-2 bg-yellow-50 rounded">
                    <p class="text-xs text-text-muted">Medium</p>
                    <p class="text-sm font-bold text-yellow-600">468</p>
                </div>
                <div class="p-2 bg-red-50 rounded">
                    <p class="text-xs text-text-muted">Low Stock</p>
                    <p class="text-sm font-bold text-red-600">23</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Purchase Summary -->
        <div class="lg:col-span-2 bg-white shadow-sm border-2 border-border-soft rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold text-text-dark">Purchase Summary</h3>
                    <div class="flex items-center space-x-2 text-sm text-text-muted">
                        <span>Period:</span>
                        <span class="font-semibold text-text-dark" id="purchasePeriod">Dec 8-14, 2024</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">PO Count</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Avg. Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <tr class="hover:bg-cream-bg transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-text-dark">Baker's Supply Co.</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-text-dark">3 POs</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-text-dark">$8,450</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-text-dark">2.3 days</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">Excellent</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-cream-bg transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-text-dark">PackPro Inc.</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-text-dark">2 POs</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-text-dark">$3,280</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-text-dark">4.1 days</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded">Good</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-cream-bg transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-text-dark">Fresh Ingredients Co.</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-text-dark">4 POs</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-text-dark">$2,150</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-text-dark">5.7 days</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">Delayed</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Metrics -->
        <div class="space-y-6">
            <!-- Usage by Category -->
            <div class="bg-white shadow-sm border-2 border-border-soft p-6 rounded-lg">
                <h3 class="font-display text-lg font-bold text-text-dark mb-4">Usage by Category</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-text-dark">Baking Ingredients</span>
                            <span class="font-semibold">45%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-text-dark">Packaging</span>
                            <span class="font-semibold">28%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 28%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-text-dark">Dairy & Eggs</span>
                            <span class="font-semibold">15%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 15%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-text-dark">Equipment</span>
                            <span class="font-semibold">8%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full" style="width: 8%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-text-dark">Finishing Items</span>
                            <span class="font-semibold">4%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-pink-500 h-2 rounded-full" style="width: 4%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Moving Items -->
            <div class="bg-white shadow-sm border-2 border-border-soft p-6 rounded-lg">
                <h3 class="font-display text-lg font-bold text-text-dark mb-4">Top Moving Items</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-text-dark">All-Purpose Flour</div>
                        <div class="text-sm font-semibold text-red-600">-125 kg</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-text-dark">Fresh Eggs</div>
                        <div class="text-sm font-semibold text-red-600">-89 trays</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-text-dark">Cake Boxes (Large)</div>
                        <div class="text-sm font-semibold text-red-600">-67 units</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-text-dark">Granulated Sugar</div>
                        <div class="text-sm font-semibold text-red-600">-45 kg</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-text-dark">Butter</div>
                        <div class="text-sm font-semibold text-red-600">-32 kg</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Report -->
    <div class="bg-white shadow-sm border-2 border-border-soft rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-bold text-text-dark">Stock Movement Report</h3>
                <div class="flex items-center space-x-3">
                    <select class="border border-border-soft px-3 py-1 rounded text-sm text-text-dark focus:outline-none focus:border-caramel">
                        <option>All Movements</option>
                        <option>Receipts Only</option>
                        <option>Usage Only</option>
                        <option>Adjustments Only</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Unit Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Total Value</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 15, 2024</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">All-Purpose Flour</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-text-muted">Baking</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">Usage</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-red-600">-25 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">$1.20</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$30.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Batch #247</div>
                        </td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 14, 2024</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">Cake Boxes (Large)</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-text-muted">Packaging</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">Receipt</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-green-600">+200 units</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">$2.50</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$500.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">PO-2024-0456</div>
                        </td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 14, 2024</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">Fresh Eggs</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-text-muted">Dairy</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">Usage</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-red-600">-15 trays</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">$4.50</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$67.50</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Morning Baking</div>
                        </td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 13, 2024</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">Chocolate Chips</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-text-muted">Baking</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">Adjustment</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-blue-600">-3 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">$8.75</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$26.25</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Spoilage</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Report Actions -->
    <div class="bg-white shadow-sm border-2 border-border-soft p-6 rounded-lg">
        <h3 class="font-display text-xl font-bold text-text-dark mb-4">Report Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button class="p-4 border-2 border-border-soft hover:border-caramel hover:bg-cream-bg transition-colors rounded-lg text-center">
                <i class="fas fa-file-invoice-dollar text-2xl text-caramel mb-2"></i>
                <p class="font-semibold text-text-dark">Purchase Report</p>
                <p class="text-xs text-text-muted mt-1">Detailed purchase analysis</p>
            </button>
            <button class="p-4 border-2 border-border-soft hover:border-caramel hover:bg-cream-bg transition-colors rounded-lg text-center">
                <i class="fas fa-chart-line text-2xl text-caramel mb-2"></i>
                <p class="font-semibold text-text-dark">Usage Report</p>
                <p class="text-xs text-text-muted mt-1">Stock consumption trends</p>
            </button>
            <button class="p-4 border-2 border-border-soft hover:border-caramel hover:bg-cream-bg transition-colors rounded-lg text-center">
                <i class="fas fa-boxes text-2xl text-caramel mb-2"></i>
                <p class="font-semibold text-text-dark">Stock Report</p>
                <p class="text-xs text-text-muted mt-1">Current inventory status</p>
            </button>
            <button class="p-4 border-2 border-border-soft hover:border-caramel hover:bg-cream-bg transition-colors rounded-lg text-center">
                <i class="fas fa-exclamation-triangle text-2xl text-caramel mb-2"></i>
                <p class="font-semibold text-text-dark">Alert Report</p>
                <p class="text-xs text-text-muted mt-1">Low stock warnings</p>
            </button>
        </div>
    </div>
</div>

<style>
    .font-display {
        font-family: 'Playfair Display', serif;
    }
</style>

<script>
    // Report data for different periods
    const reportData = {
        daily: {
            totalPurchases: '$2,450',
            purchaseTrend: '↑ 8% vs yesterday',
            stockUsage: '156 units',
            usageTrend: '↑ 5% vs yesterday',
            stockValue: '$84,520',
            valueTrend: '↓ 1% vs yesterday',
            lowStockCount: '23',
            alertTrend: '↑ 2 new alerts',
            purchasePeriod: 'Dec 15, 2024',
            totalItems: '2,347'
        },
        weekly: {
            totalPurchases: '$18,250',
            purchaseTrend: '↑ 12% vs last week',
            stockUsage: '1,245 units',
            usageTrend: '↑ 8% vs last week',
            stockValue: '$84,520',
            valueTrend: '↓ 3% vs last week',
            lowStockCount: '23',
            alertTrend: '↑ 5 new alerts',
            purchasePeriod: 'Dec 8-14, 2024',
            totalItems: '2,347'
        },
        monthly: {
            totalPurchases: '$78,420',
            purchaseTrend: '↑ 15% vs last month',
            stockUsage: '5,280 units',
            usageTrend: '↑ 12% vs last month',
            stockValue: '$84,520',
            valueTrend: '↓ 8% vs last month',
            lowStockCount: '23',
            alertTrend: '↑ 12 new alerts',
            purchasePeriod: 'December 2024',
            totalItems: '2,347'
        },
        quarterly: {
            totalPurchases: '$245,680',
            purchaseTrend: '↑ 18% vs last quarter',
            stockUsage: '15,840 units',
            usageTrend: '↑ 14% vs last quarter',
            stockValue: '$84,520',
            valueTrend: '↓ 12% vs last quarter',
            lowStockCount: '23',
            alertTrend: '↑ 25 new alerts',
            purchasePeriod: 'Q4 2024',
            totalItems: '2,347'
        }
    };

    function updateReportData() {
        const period = document.getElementById('reportPeriod').value;
        const data = reportData[period];
        
        // Update all metric cards
        document.getElementById('totalPurchases').textContent = data.totalPurchases;
        document.getElementById('purchaseTrend').textContent = data.purchaseTrend;
        document.getElementById('stockUsage').textContent = data.stockUsage;
        document.getElementById('usageTrend').textContent = data.usageTrend;
        document.getElementById('stockValue').textContent = data.stockValue;
        document.getElementById('valueTrend').textContent = data.valueTrend;
        document.getElementById('lowStockCount').textContent = data.lowStockCount;
        document.getElementById('alertTrend').textContent = data.alertTrend;
        document.getElementById('purchasePeriod').textContent = data.purchasePeriod;
        document.getElementById('totalItems').textContent = data.totalItems;
        
        // In a real application, you would also update charts here
        console.log(`Updated report data for ${period} period`);
    }

    // Set reports as active by default
    document.addEventListener('DOMContentLoaded', function() {
        setActiveMenu('menu-reports');
        // Initialize with weekly data
        updateReportData();
    });
</script>
@endsection