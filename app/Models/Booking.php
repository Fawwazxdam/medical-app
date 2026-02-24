<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Booking Model
 * 
 * Model ini merepresentasikan janji temu antara pasien dan dokter.
 * Status booking: Waiting, InProgress, Finished, Cancelled
 */
class Booking extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bookings';

    /**
     * Atribut yang dapat diisi secara mass assignment
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_time',
        'status',
        'started_at',
        'finished_at',
    ];

    /**
     * Cast tipe data untuk atribut timestamp
     */
    protected function casts(): array
    {
        return [
            'appointment_time' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke Patient
     * Satu booking dimiliki oleh satu pasien
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Relasi ke User (sebagai Doctor)
     * Satu booking dimiliki oleh satu dokter
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }

    /**
     * Relasi ke MedicalRecord
     * Satu booking memiliki satu medical record
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id', 'id');
    }
}
