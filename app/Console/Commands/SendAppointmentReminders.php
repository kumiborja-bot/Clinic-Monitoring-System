<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Treatment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send automated email reminders for upcoming appointments and follow-up clinic visits';

    public function handle(): int
    {
        $now = now();
        $tomorrow = now()->addDay();

        // 1. Upcoming appointments in the next 24 hours
        $upcomingAppointments = Appointment::where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereBetween('appointment_date', [$now, $tomorrow])
            ->get();

        $count = 0;
        foreach ($upcomingAppointments as $appointment) {
            $email = $appointment->email ?? $appointment->patient?->email;
            if ($email) {
                try {
                    Mail::to($email)->send(new AppointmentReminderMail($appointment, false));
                    $appointment->update(['reminder_sent' => true]);
                    $count++;
                } catch (\Throwable $e) {
                    $this->error("Failed sending reminder for appointment #{$appointment->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Sent {$count} appointment reminder emails.");

        return Command::SUCCESS;
    }
}
