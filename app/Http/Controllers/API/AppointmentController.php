<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Appointment::with(['patient', 'doctor.user']);
        
        if ($user->role === 'doctor') {
            $query->where('doctor_id', $user->doctor->id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        
        $appointments = $query->orderBy('appointment_date', 'desc')->paginate(15);
        
        return response()->json($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Check if doctor is available at that time
        $doctor = Doctor::find($request->doctor_id);
        $appointmentTime = Carbon::parse($request->appointment_date);
        
        // Check if slot is already taken
        $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->whereNotIn('status', ['cancelled'])
            ->exists();
            
        if ($existingAppointment) {
            return response()->json(['message' => 'This time slot is already booked'], 422);
        }
        
        $appointment = Appointment::create($request->all());
        
        return response()->json($appointment->load(['patient', 'doctor.user']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor.user', 'medicalRecord'])
        ->findOrFail($id);
    
    return response()->json($appointment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $appointment = Appointment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'appointment_date' => 'sometimes|date|after:now',
            'status' => 'sometimes|in:scheduled,confirmed,completed,cancelled,no_show',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment->update($request->all());

        return response()->json($appointment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json(['message' => 'Appointment cancelled successfully']);
    }

    public function getAvailableSlots(Request $request, $doctorId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $doctor = Doctor::findOrFail($doctorId);
        $date = Carbon::parse($request->date);
        
        // Generate available time slots (e.g., every 30 minutes)
        $startTime = Carbon::parse($doctor->available_from);
        $endTime = Carbon::parse($doctor->available_to);
        
        $slots = [];
        $currentSlot = clone $startTime;
        
        while ($currentSlot < $endTime) {
            $slotTime = $date->copy()->setTime($currentSlot->hour, $currentSlot->minute);
            
            // Check if slot is not booked
            $isBooked = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $slotTime)
                ->whereNotIn('status', ['cancelled'])
                ->exists();
                
            if (!$isBooked && $slotTime > now()) {
                $slots[] = $slotTime->format('H:i');
            }
            
            $currentSlot->addMinutes(30);
        }
        
        return response()->json(['slots' => $slots]);
    }
}
