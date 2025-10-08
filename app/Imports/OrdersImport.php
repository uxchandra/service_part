<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class OrdersImport implements ToCollection, WithHeadingRow
{
    /**
     * Proses seluruh baris import dalam transaksi database
     * Kolom yang didukung: no_transaksi, part_no, qty, delivery_date
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $noTransaksi = trim((string)($row['no_transaksi'] ?? $row['no transaksi'] ?? $row['no-transaksi'] ?? ''));
                $partNo = trim((string)($row['part_no'] ?? $row['part no'] ?? ''));
                $qty = (int)($row['qty'] ?? $row['quantity'] ?? 0);
                $deliveryRaw = $row['delivery_date'] ?? $row['delivery date'] ?? $row['delivery-date'] ?? null;

                if ($noTransaksi === '' || $partNo === '' || $qty <= 0) {
                    // Skip baris yang tidak valid
                    continue;
                }

                // Normalisasi tanggal dengan support untuk berbagai format
                $deliveryDate = null;
                if (!empty($deliveryRaw)) {
                    $deliveryDate = $this->parseDeliveryDate($deliveryRaw);
                }

                // Upsert Order berdasarkan no_transaksi (agar user tidak perlu tahu order_id)
                $order = Order::firstOrCreate(
                    ['no_transaksi' => $noTransaksi],
                    [
                        'delivery_date' => $deliveryDate,
                        'status' => 'planning',
                    ]
                );

                // Jika ada delivery_date baru di baris, update jika order belum punya
                if ($deliveryDate && empty($order->delivery_date)) {
                    $order->delivery_date = $deliveryDate;
                    $order->save();
                }

                // Tambahkan item (boleh duplikat per part_no jika dibutuhkan)
                OrderItem::create([
                    'order_id' => $order->id,
                    'part_no' => $partNo,
                    'quantity' => $qty,
                ]);
            }
        });
    }

    /**
     * Parse tanggal dari berbagai format yang didukung:
     * - 15/08/2025
     * - 15-08-2025
     * - 2025-08-15
     * - Excel date number (jika dari Excel)
     */
    private function parseDeliveryDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Jika angka (Excel serial date number)
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            }

            // Konversi ke string
            $dateString = trim((string)$date);

            // Format: dd/mm/yyyy atau dd-mm-yyyy
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateString, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                return Carbon::createFromFormat('d/m/Y', "$day/$month/$year");
            }

            // Format: yyyy-mm-dd atau yyyy/mm/dd
            if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $dateString, $matches)) {
                return Carbon::createFromFormat('Y-m-d', $dateString);
            }

            // Fallback: gunakan Carbon::parse untuk format standar lainnya
            return Carbon::parse($dateString);

        } catch (\Exception $e) {
            // Jika gagal parsing, return null
            return null;
        }
    }
}