@extends('layouts.app')

@section('content')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-area, #printable-area * {
            visibility: visible;
        }
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
<div class="py-12" id="printable-area">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 no-print">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">&larr; Kembali ke Dashboard</a>
        </div>
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Laporan Keuangan</h2>
        </div>


        <!-- Main Financials -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Total Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                <div class="text-gray-500 text-sm font-medium uppercase">Total Pendapatan</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Pendapatan Seumur Hidup</div>
            </div>

            <!-- Net Profit Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                <div class="text-gray-500 text-sm font-medium uppercase">Laba Bersih</div>
                <div class="mt-2 text-3xl font-bold {{ $netProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Pendapatan - Pengeluaran</div>
            </div>

            <!-- Total Expenses Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                <div class="text-gray-500 text-sm font-medium uppercase">Total Pengeluaran</div>
                <div class="mt-2 text-3xl font-bold text-red-600">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 mt-1">Disetujui & Dalam Proses</div>
            </div>
        </div>

        <!-- Period Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Monthly Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-sm font-medium uppercase">Pendapatan Bulanan</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">
                            Rp {{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ now()->translatedFormat('F Y') }}</div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-sm font-medium uppercase">Pendapatan Hari Ini</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">
                            Rp {{ number_format($dailyRevenue ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ now()->translatedFormat('d M Y') }}</div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Performing Routes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Rute Terlaris</h3>
                    <div class="overflow-x-auto">
                        @if(isset($topRoutes) && $topRoutes->isNotEmpty())
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemesanan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topRoutes as $route)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="font-medium">{{ $route->route_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $route->source_name ?? $route->source }} &rarr; {{ $route->destination_name ?? $route->destination }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $route->total_bookings }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">Rp {{ number_format($route->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                            <p class="text-gray-500 text-sm italic">Tidak ada data rute.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Transaksi Terbaru</h3>
                        <a href="{{ route('admin.transactions') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Lihat Transaksi Lengkap &rarr;
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan/Akun</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">#{{ $transaction->id }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $transaction->account->first_name ?? 'Tamu' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->translatedFormat('d M') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm text-gray-500 text-center">Tidak ada transaksi ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
