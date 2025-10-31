@extends('Purchasing.layout.app')

@section('title', 'Procurement Reports - WellKenz ERP')

@section('breadcrumb', 'Procurement Reports')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Procurement Analytics & Reports</h1>
                <p class="text-text-muted mt-2">Comprehensive procurement insights and performance metrics.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-text-dark font-semibold">{{ date('F j, Y') }}</p>
                <p class="text-xs text-text-muted mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Spending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">$2.4M</p>
            <p class="text-xs text-green-600 mt-1">↑ 12% vs last year</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Purchase Orders</p>
            <p class="text-3xl font-bold text-text-dark mt-2">1,245</p>
            <p class="text-xs text-green-600 mt-1">↑ 8% this quarter</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-percentage text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Cost Savings</p>
            <p class="text-3xl font-bold text-text-dark mt-2">18.5%</p>
            <p class="text-xs text-green-600 mt-1">↑ 3.2% vs target</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-truck text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Late Deliveries</p>
            <p class="text-3xl font-bold text-text-dark mt-2">5.2%</p>
            <p class="text-xs text-red-600 mt-1">↑ 1.1% this month</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Spending Analysis -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Spending Analysis</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>Last 30 Days</option>
                        <option>Last Quarter</option>
                        <option>Last Year</option>
                        <option>Year to Date</option>
                    </select>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Spending by Category -->
                <div>
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Spending by Category</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-text-muted">Raw Materials</span>
                                <span class="text-sm font-bold text-text-dark">$856,420</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full" style="width: 45%"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">45% of total spending</p>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-text-muted">Equipment & Machinery</span>
                                <span class="text-sm font-bold text-text-dark">$523,180</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-600 h-3 rounded-full" style="width: 28%"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">28% of total spending</p>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-text-muted">IT & Technology</span>
                                <span class="text-sm font-bold text-text-dark">$289,750</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-600 h-3 rounded-full" style="width: 15%"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">15% of total spending</p>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-text-muted">Office Supplies</span>
                                <span class="text-sm font-bold text-text-dark">$156,320</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-orange-500 h-3 rounded-full" style="width: 8%"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">8% of total spending</p>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-text-muted">Other</span>
                                <span class="text-sm font-bold text-text-dark">$74,330</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gray-400 h-3 rounded-full" style="width: 4%"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">4% of total spending</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Spending Trend -->
                <div>
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Monthly Spending Trend</h4>
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="flex items-end justify-between h-32">
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-blue-500 rounded-t" style="height: 60px"></div>
                                <span class="text-xs text-text-muted mt-1">Jan</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-blue-500 rounded-t" style="height: 75px"></div>
                                <span class="text-xs text-text-muted mt-1">Feb</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-blue-500 rounded-t" style="height: 85px"></div>
                                <span class="text-xs text-text-muted mt-1">Mar</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-blue-500 rounded-t" style="height: 92px"></div>
                                <span class="text-xs text-text-muted mt-1">Apr</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-blue-500 rounded-t" style="height: 78px"></div>
                                <span class="text-xs text-text-muted mt-1">May</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-blue-500 rounded-t" style="height: 95px"></div>
                                <span class="text-xs text-text-muted mt-1">Jun</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 bg-caramel rounded-t" style="height: 110px"></div>
                                <span class="text-xs text-text-muted mt-1">Jul</span>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <p class="text-sm text-text-muted">Spending in thousands ($)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Reports & Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Report Generator</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                    Spending Report
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-star mr-2"></i>
                    Supplier Performance
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-truck mr-2 text-chocolate"></i>
                    Delivery Performance
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-chart-pie mr-2 text-chocolate"></i>
                    Category Analysis
                </button>
            </div>

            <!-- Export Options -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Export Reports</h4>
                <div class="grid grid-cols-2 gap-2">
                    <button class="p-3 bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 transition text-center">
                        <i class="fas fa-file-pdf text-red-600 mb-1"></i>
                        <p class="text-xs font-bold">PDF</p>
                    </button>
                    <button class="p-3 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 transition text-center">
                        <i class="fas fa-file-excel text-green-600 mb-1"></i>
                        <p class="text-xs font-bold">Excel</p>
                    </button>
                    <button class="p-3 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition text-center">
                        <i class="fas fa-file-csv text-blue-600 mb-1"></i>
                        <p class="text-xs font-bold">CSV</p>
                    </button>
                    <button class="p-3 bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100 transition text-center">
                        <i class="fas fa-chart-bar text-gray-600 mb-1"></i>
                        <p class="text-xs font-bold">Charts</p>
                    </button>
                </div>
            </div>

            <!-- Report Schedule -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Scheduled Reports</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Weekly Spending</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">ACTIVE</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Monthly Supplier Perf.</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">ACTIVE</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Quarterly Analytics</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded">PENDING</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Supplier Performance Report -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-trophy text-caramel mr-2"></i>
                Supplier Performance Dashboard
            </h3>
            
            <div class="space-y-4">
                <!-- Supplier Performance Item -->
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Global Materials Inc.</p>
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <p class="text-sm font-bold text-text-dark">96.2%</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <p class="text-sm font-bold text-text-dark">4.8/5</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Total Spending</p>
                                    <p class="text-sm font-bold text-text-dark">$458,240</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-xs text-text-muted">Cost Savings: $42,180 (9.2%)</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">EXCELLENT</span>
                    </div>
                </div>

                <!-- Supplier Performance Item -->
                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Tech Solutions Ltd.</p>
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <p class="text-sm font-bold text-text-dark">91.5%</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <p class="text-sm font-bold text-text-dark">4.5/5</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Total Spending</p>
                                    <p class="text-sm font-bold text-text-dark">$289,750</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-xs text-text-muted">Cost Savings: $25,320 (8.7%)</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold">GOOD</span>
                    </div>
                </div>

                <!-- Supplier Performance Item -->
                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Office Pro Supplies</p>
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <p class="text-sm font-bold text-text-dark">85.3%</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <p class="text-sm font-bold text-text-dark">4.2/5</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Total Spending</p>
                                    <p class="text-sm font-bold text-text-dark">$156,320</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-xs text-text-muted">Cost Savings: $8,450 (5.4%)</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-yellow-600 text-white text-xs font-bold">AVERAGE</span>
                    </div>
                </div>

                <!-- Supplier Performance Item -->
                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Industrial Tools Co.</p>
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <p class="text-sm font-bold text-text-dark">78.9%</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <p class="text-sm font-bold text-text-dark">3.7/5</p>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">Total Spending</p>
                                    <p class="text-sm font-bold text-text-dark">$89,450</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-xs text-text-muted">Cost Overruns: $3,250 (3.6%)</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold">NEEDS REVIEW</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procurement KPIs -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-chart-line text-caramel mr-2"></i>
                Key Performance Indicators
            </h3>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-green-50 border-l-4 border-green-500">
                    <p class="text-sm font-bold text-text-muted">Purchase Order Cycle Time</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">3.2</p>
                    <p class="text-xs text-text-muted">days</p>
                </div>
                
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500">
                    <p class="text-sm font-bold text-text-muted">Supplier Lead Time</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">7.5</p>
                    <p class="text-xs text-text-muted">days</p>
                </div>
                
                <div class="p-4 bg-purple-50 border-l-4 border-purple-500">
                    <p class="text-sm font-bold text-text-muted">Cost Avoidance</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">$124K</p>
                    <p class="text-xs text-text-muted">this quarter</p>
                </div>
                
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500">
                    <p class="text-sm font-bold text-text-muted">Emergency Purchases</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">4.8%</p>
                    <p class="text-xs text-text-muted">of total POs</p>
                </div>
            </div>

            <!-- Cost Savings Analysis -->
            <div class="mt-6 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Cost Savings Analysis</h4>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Volume Discounts</span>
                            <span class="text-sm font-bold text-text-dark">$68,420</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Contract Negotiations</span>
                            <span class="text-sm font-bold text-text-dark">$42,150</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 28%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Supplier Competition</span>
                            <span class="text-sm font-bold text-text-dark">$28,730</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 19%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Process Efficiency</span>
                            <span class="text-sm font-bold text-text-dark">$10,450</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: 8%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Report Activity -->
            <div class="mt-6 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Recent Report Activity</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Monthly Spending Report</span>
                        <span class="text-text-muted text-xs">Generated: Today, 09:30 AM</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Supplier Performance Q3</span>
                        <span class="text-text-muted text-xs">Generated: Yesterday, 03:15 PM</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Cost Savings Analysis</span>
                        <span class="text-text-muted text-xs">Generated: Nov 25, 2024</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Delivery Performance</span>
                        <span class="text-text-muted text-xs">Generated: Nov 22, 2024</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    
    .font-display {
        font-family: 'Playfair Display', serif;
    }

    .cream-bg {
        background-color: #faf7f3;
    }
    
    .text-text-dark {
        color: #1a1410;
    }
    
    .text-text-muted {
        color: #8b7355;
    }
    
    .bg-chocolate {
        background-color: #3d2817;
    }
    
    .hover\:bg-chocolate-dark:hover {
        background-color: #2a1a0f;
    }
    
    .bg-caramel {
        background-color: #c48d3f;
    }
    
    .hover\:bg-caramel-dark:hover {
        background-color: #a67332;
    }
    
    .border-border-soft {
        border-color: #e8dfd4;
    }
</style>
@endsection