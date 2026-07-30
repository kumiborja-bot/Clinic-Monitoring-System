<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public bool $isFollowUp = false
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->isFollowUp
            ? 'Follow-up Appointment Reminder - MMACI Clinic System'
            : 'Upcoming Appointment Reminder - MMACI Clinic System';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
            with: [
                'appointment' => $this->appointment,
                'isFollowUp' => $this->isFollowUp,
                'patientName' => $this->appointment->patient?->name ?? 'Patient',
                'appointmentDate' => $this->appointment->appointment_date?->format('F d, Y \a\t h:i A'),
                'clinicLocation' => $this->appointment->clinic_location,
                'providerName' => $this->appointment->assigned_provider_name ?? 'Assigned Medical Staff',
                'reason' => $this->appointment->reason_for_visit,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
