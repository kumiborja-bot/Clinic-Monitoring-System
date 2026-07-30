<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $type = 'rescheduled' // 'rescheduled' or 'cancelled'
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'cancelled'
            ? 'Appointment Cancellation Notice - MMACI Clinic System'
            : 'Appointment Rescheduled Notice - MMACI Clinic System';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-status',
            with: [
                'appointment' => $this->appointment,
                'type' => $this->type,
                'patientName' => $this->appointment->patient?->name ?? 'Patient',
                'appointmentDate' => $this->appointment->appointment_date?->format('F d, Y \a\t h:i A'),
                'clinicLocation' => $this->appointment->clinic_location,
                'providerName' => $this->appointment->assigned_provider_name ?? 'Assigned Medical Staff',
                'reason' => $this->appointment->cancellation_reason ?? 'Administrative Update',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
