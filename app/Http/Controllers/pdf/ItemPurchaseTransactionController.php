<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
use App\Models\ItemPurchaseTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;

class ItemPurchaseTransactionController extends Controller
{
    public function export(Request $request)
    {
        try {
            $from = $request->input('from');
            $to = $request->input('to');

            $itemPurchaseTransaction = ItemPurchaseTransaction::with('inventory')
                ->when(
                    empty($from) && empty($to),
                    function ($query) {
                        return $query->whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month);
                    },
                    function ($query) use ($from, $to) {
                        return $query
                            ->when($from, function ($q) use ($from) {
                                return $q->whereDate('created_at', '>=', $from);
                            })
                            ->when($to, function ($q) use ($to) {
                                return $q->whereDate('created_at', '<=', $to);
                            });
                    }
                )
                ->orderBy('created_at', 'desc')
                ->get();

            $totalTransactions = $itemPurchaseTransaction->count();
            $totalSupplier = $itemPurchaseTransaction->unique('supplier')->count();
            $grandTotal = $itemPurchaseTransaction->sum(function ($transaction) {
                return $transaction->qty * $transaction->unitPrice;
            });
            $periode = "";
            if (!empty($from) && !empty($to)) {
                $formattedFrom = Carbon::parse($from)->isoFormat('D MMMM YYYY');
                $formattedTo = Carbon::parse($to)->isoFormat('D MMMM YYYY');
                $periode = "{$formattedFrom} - {$formattedTo}";

            } elseif (!empty($from)) {
                $formattedFrom = Carbon::parse($from)->isoFormat('D MMMM YYYY');
                $periode = "Dari {$formattedFrom}";

            } elseif (!empty($to)) {
                $formattedTo = Carbon::parse($to)->isoFormat('D MMMM YYYY');
                $periode = "Sampai {$formattedTo}";

            } else {
                $periode = now()->isoFormat('MMMM YYYY');
            }

            return Pdf::view('pdf.itemPurchaseTransaction', compact("itemPurchaseTransaction", "totalTransactions", "totalSupplier", "periode", "grandTotal"))
                ->name('laporan-transaksi-pembelian-barang.pdf')
                ->format('a4')
                ->margins(top: 15, right: 15, bottom: 20, left: 15)
                ->download();
        } catch (\Exception $e) {
            if (config('app.debug')) {
                dd($e);
            }

            return response()->json([
                'error' => 'Ada kesalahan pada server!'
            ], 500);
        }
    }
}
