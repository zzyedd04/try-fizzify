<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    /**
     * transfer = tarik saldo langsung (status done)
     * request  = ajukan penarikan, saldo ditahan sampai ditandai selesai (status pending)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:transfer,request'],
            'amount' => ['required', 'integer', 'min:1000'],
            'note' => ['nullable', 'string', 'max:120'],
        ], [
            'amount.min' => 'Minimal Rp. 1.000',
        ]);

        if ($data['amount'] > $this->balance()) {
            return back()->with('error', 'Saldo tidak cukup. Saldo aktif: '.rupiah($this->balance()));
        }

        Withdrawal::create([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'status' => $data['type'] === 'transfer' ? 'done' : 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', $data['type'] === 'transfer'
            ? rupiah($data['amount']).' berhasil ditransfer.'
            : 'Request '.rupiah($data['amount']).' tercatat dan menunggu diselesaikan.');
    }

    public function complete(Withdrawal $withdrawal)
    {
        if ($withdrawal->status === 'pending') {
            $withdrawal->update(['status' => 'done']);
        }

        return back()->with('success', 'Request penarikan ditandai selesai.');
    }

    public function destroy(Withdrawal $withdrawal)
    {
        // Hanya request yang masih pending yang boleh dibatalkan
        if ($withdrawal->status === 'pending') {
            $withdrawal->delete();
        }

        return back()->with('success', 'Request penarikan dibatalkan, saldo dikembalikan.');
    }

    private function balance(): int
    {
        return (int) Order::revenue()->sum('total') - (int) Withdrawal::sum('amount');
    }
}
