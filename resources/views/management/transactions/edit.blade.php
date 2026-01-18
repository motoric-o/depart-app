@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Edit Transaksi #{{ $transaction->id }}</h2>
                    <a href="{{ route('admin.transactions') }}" class="text-gray-600 hover:text-gray-900">Kembali ke Daftar</a>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.transactions.update', $transaction->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Detail Transaksi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Pelanggan</p>
                                <p class="font-medium">{{ $transaction->account ? $transaction->account->first_name . ' ' . $transaction->account->last_name : $transaction->customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Tanggal</p>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Tagihan</p>
                                <p class="font-bold text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Booking Ref</p>
                                <p class="font-medium">{{ $transaction->booking_id ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                        <select name="payment_method" id="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                             <option value="Cash" {{ $transaction->payment_method == 'Cash' ? 'selected' : '' }}>Cash</option>
                             <option value="Transfer" {{ $transaction->payment_method == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                             <option value="QRIS" {{ $transaction->payment_method == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                             <option value="Other" {{ $transaction->payment_method == 'Other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div>
                         <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                         <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                             <option value="Pending" {{ $transaction->status == 'Pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                             <option value="Success" {{ $transaction->status == 'Success' ? 'selected' : '' }}>Success (Sukses)</option>
                             <option value="Failed" {{ $transaction->status == 'Failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                         </select>
                         <p class="text-xs text-gray-500 mt-1">Mengubah status menjadi "Success" juga akan memperbarui status Tiket terkait.</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
