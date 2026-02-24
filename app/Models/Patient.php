<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Patient Model
 * 
 * Model ini merepresentasikan data pasien dalam sistem.
 * Berisi informasi dasar pasien seperti nama, jenis kelamin, tanggal lahir, dll.
 */
class Patient extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'patients';

    /**
     * Atribut yang dapat diisi secara mass assignment
     */
    protected $fillable = [
        'name',
        'gender',
        'date_of_birth',
        'phone_number',
        'address',
    ];

    /**
     * Cast tipe data untuk atribut
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Relasi ke Booking
     * Satu pasien dapat memiliki banyak booking/appointment
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'patient_id', 'id');
    }

    /**
     * Relasi ke MedicalRecord
     * Satu pasien dapat memiliki banyak rekam medis
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'id');
    }
}
