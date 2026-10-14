<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\MedicalRecordController;
use App\Http\Controllers\API\SpecialtyController;
use App\Http\Controllers\API\DashboardController;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group( function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

      // Dashboard
      Route::get('/dashboard', [DashboardController::class, 'index']);
    
      // Patients
      Route::apiResource('patients', PatientController::class);
      
      // Doctors
      Route::apiResource('doctors', DoctorController::class);
      
      // Specialties
      Route::apiResource('specialties', SpecialtyController::class);
      
      // Appointments
      Route::apiResource('appointments', AppointmentController::class);
      Route::get('doctors/{doctorId}/available-slots', [AppointmentController::class, 'getAvailableSlots']);
      
      // Medical Records
      Route::apiResource('medical-records', MedicalRecordController::class);
   //   Route::get('patients/{patientId}/medical-records', [MedicalRecordController::class, 'getByPatient']);


});