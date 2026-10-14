<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $data = [];
        
        if ($user->role === 'admin') {
            // Admin dashboard stats
            $data['total_patients'] = Patient::count();
            $data['total_doctors'] = Doctor::count();
            $data['total_appointments'] = Appointment::count();
            $data['today_appointments'] = Appointment::whereDate('appointment_date', Carbon::today())->count();
            $data['upcoming_appointments'] = Appointment::where('appointment_date', '>', now())
                ->where('status', 'scheduled')
                ->count();
                
            // Recent appointments
            $data['recent_appointments'] = Appointment::with(['patient', 'doctor.user'])
                ->orderBy('appointment_date', 'desc')
                ->limit(10)
                ->get();
                
            // Appointments by status
            $data['appointments_by_status'] = [
                'scheduled' => Appointment::where('status', 'scheduled')->count(),
                'confirmed' => Appointment::where('status', 'confirmed')->count(),
                'completed' => Appointment::where('status', 'completed')->count(),
                'cancelled' => Appointment::where('status', 'cancelled')->count(),
            ];
            
        } elseif ($user->role === 'doctor') {
            // Doctor dashboard stats
            $doctorId = $user->doctor->id;
            $data['today_appointments'] = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', Carbon::today())
                ->count();
            $data['total_patients'] = Appointment::where('doctor_id', $doctorId)
                ->distinct('patient_id')
                ->count('patient_id');
            $data['upcoming_appointments'] = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', '>', now())
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->count();
                
            // Today's appointments
            $data['today_appointments_list'] = Appointment::with('patient')
                ->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', Carbon::today())
                ->orderBy('appointment_date')
                ->get();
        }
        
        return response()->json($data);
    }
}
