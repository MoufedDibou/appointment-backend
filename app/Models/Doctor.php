<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'specialty_id', 'license_number', 'qualifications',
        'years_of_experience', 'consultation_fee', 'available_days',
        'available_from', 'available_to', 'is_active'
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function getAvailableDaysArrayAttribute()
    {
        return $this->available_days ? explode(',', $this->available_days) : [];
    }
}
