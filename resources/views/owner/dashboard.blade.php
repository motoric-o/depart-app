@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Dashboard Pemilik</h3>
                <p>{{ __("Anda masuk sebagai Pemilik!") }}</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Fleet Management (Unified Admin Access) -->
                    <a href="{{ route('admin.schedules') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Jadwal</div>
                        <div class="text-sm opacity-80 mt-2">Manajemen Jadwal Terpadu.</div>
                    </a>
                    <a href="{{ route('admin.buses') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Bus</div>
                        <div class="text-sm opacity-80 mt-2">Manajemen Bus Terpadu.</div>
                    </a>
                    <a href="{{ route('admin.routes') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Rute</div>
                        <div class="text-sm opacity-80 mt-2">Manajemen Rute Terpadu.</div>
                    </a>

                    <a href="{{ route('admin.destinations') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Destinations</div>
                        <div class="text-sm opacity-80 mt-2">Kelola Kota & Tujuan.</div>
                    </a>

                    <a href="{{ route('admin.bookings') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Pemesanan</div>
                        <div class="text-sm opacity-80 mt-2">Kelola pemesanan tiket.</div>
                    </a>

                    <!-- Owner Specific -->
                    <a href="{{ route('admin.users') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Pengguna</div>
                        <div class="text-sm opacity-80 mt-2">Manajemen Pengguna Terpadu.</div>
                    </a>
                    <a href="{{ route('admin.financial.reports') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Keuangan</div>
                        <div class="text-sm opacity-80 mt-2">Lihat pendapatan, pengeluaran & rute terlaris.</div>
                    </a>
                    <a href="{{ route('admin.expenses') }}" class="block bg-blue-600 rounded-lg shadow p-6 text-white hover:bg-blue-700 transition">
                        <div class="text-3xl font-bold">Pengeluaran</div>
                        <div class="text-sm opacity-80 mt-2">Lacak penggantian & biaya.</div>
                    </a>
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-bold mb-3">Ringkasan Keuangan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white border rounded-lg p-4 shadow-sm">
                            <h5 class="text-gray-500 font-medium">Total Pendapatan</h5>
                            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($dashboardStats->total_revenue ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white border rounded-lg p-4 shadow-sm">
                            <h5 class="text-gray-500 font-medium">Total Pengeluaran</h5>
                            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white border rounded-lg p-4 shadow-sm">
                            <h5 class="text-gray-500 font-medium">Laba Bersih</h5>
                            <p class="text-2xl font-bold {{ ($netProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h4 class="text-lg font-bold mb-3">Rute Terlaris</h4>
                    <div class="bg-gray-50 border rounded-lg p-4">
                        @if($routeStats->isEmpty())
                            <p class="text-gray-500">Belum ada data tersedia.</p>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemesanan</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Rata-rata</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($routeStats as $stat)
                                        <tr>
                                            <td class="px-3 py-2 text-sm font-semibold text-gray-900">{{ $stat->route_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $stat->source_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $stat->destination_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $stat->total_bookings }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">Rp {{ number_format($stat->total_revenue, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">Rp {{ number_format($stat->average_ticket_price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
