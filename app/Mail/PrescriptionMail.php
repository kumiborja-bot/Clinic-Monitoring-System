<?php

namespace App\Mail;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrescriptionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Prescription $prescription)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Medical Prescription Issued - MMACI Clinic System',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prescription-issued',
            with: [
                'prescription' => $this->prescription,
                'patientName' => $this->prescription->patient?->name ?? 'Patient',
                'prescriberName' => $this->prescription->prescriber_name ?? $this->prescription->prescriber?->name ?? 'Healthcare Provider',
                'issuedAt' => $this->prescription->issued_at?->format('F d, Y h:i A'),
                'medicationDetails' => $this->prescription->medication_details ?? [],
                'notes' => $this->prescription->notes,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
