<?php

namespace App\Imports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class BarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, Importable;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Ambil nilai keypoint dari berbagai kemungkinan header dan normalisasi
        $keypointRaw = $row['keypoint']
            ?? $row['key_point']
            ?? $row['key point']
            ?? null;

        // Simpan apa adanya (tanpa memaksa file harus sudah ada), trim spasi ekstra
        $keypoint = is_string($keypointRaw) ? trim($keypointRaw) : null;

        return new Barang([
            'qr_label'      => $row['qr_label'] ?? $row['qr label'] ?? null,
            'part_no'       => $row['part_no'] ?? $row['part no'] ?? null,
            'customer'      => $row['customer'] ?? null,
            'part_name'     => $row['part_name'] ?? $row['part name'] ?? null,
            'size_plastic'  => $row['size_plastic'] ?? $row['size plastic'] ?? null,
            'part_color'    => $row['part_color'] ?? $row['part color'] ?? null,
            'keypoint'      => $keypoint, // Simpan path relatif (contoh: keypoint/678-987.jpg)
        ]);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'qr_label' => 'required|string|max:255|unique:barang,qr_label',
            'part_no' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'size_plastic' => 'nullable|string|max:255',
            'part_color' => 'nullable|string|max:255',
            'keypoint' => 'nullable|string|max:255', // Tambahkan validasi keypoint
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'qr_label.required' => 'QR Label wajib diisi',
            'qr_label.unique' => 'QR Label sudah digunakan',
            'part_no.required' => 'Part No wajib diisi',
        ];
    }
}