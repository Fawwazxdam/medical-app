<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MedicalRecord Model
 * 
 * Model ini merepresentasikan rekam medis pasien dari suatu pemeriksaan.
 * Berisi diagnosis dan catatan medis dari dokter.
 */
class MedicalRecord extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'medical_records';

    /**
     * Atribut yang dapat diisi secara mass assignment
     */
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'diagnosis',
        'notes',
    ];

    /**
     * Cast tipe data untuk atribut
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke Booking (Appointment)
     * Satu medical record terkait dengan satu booking/appointment
     */
    public function appointment()
    {
        return $this->belongsTo(Booking::class, 'appointment_id', 'id');
    }

    /**
     * Relasi ke Patient
     * Satu medical record dimiliki oleh satu pasien
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Relasi ke User (sebagai Doctor)
     * Satu medical record ditulis oleh satu dokter
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }
}
