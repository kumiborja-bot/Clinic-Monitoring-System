<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Update</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #333333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .header { background: #132639; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #fbbf24; }
        .content { padding: 30px 24px; }
        .greeting { font-size: 16px; font-weight: bold; color: #111827; margin-bottom: 12px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
        .badge-rescheduled { background-color: #fef3c7; color: #92400e; }
        .info-card { background: #f8fafc; border-left: 4px solid #475569; padding: 16px; margin: 20px 0; border-radius: 4px; }
        .info-row { margin-bottom: 10px; font-size: 14px; }
        .label { font-weight: bold; color: #475569; }
        .value { color: #0f172a; margin-top: 2px; }
        .footer { background: #f1f5f9; padding: 16px 24px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Merchant Marine Academy of Caraga, Inc.</h1>
            <p>Clinic Health Monitoring System</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $patientName }},</div>

            @if($type === 'cancelled')
                <div class="badge badge-cancelled">Appointment Cancelled</div>
                <p style="margin-top: 0; line-height: 1.5; color: #4b5563;">
                    Your appointment scheduled for <strong>{{ $appointmentDate }}</strong> at <strong>{{ $clinicLocation }}</strong> has been cancelled.
                </p>
            @else
                <div class="badge badge-rescheduled">Appointment Rescheduled</div>
                <p style="margin-top: 0; line-height: 1.5; color: #4b5563;">
                    Your appointment has been rescheduled. Please review your updated schedule below:
                </p>
            @endif

            <div class="info-card">
                <div class="info-row">
                    <div class="label">Date & Time:</div>
                    <div class="value">{{ $appointmentDate }}</div>
                </div>
                <div class="info-row">
                    <div class="label">Clinic Location:</div>
                    <div class="value">{{ $clinicLocation }}</div>
                </div>
                <div class="info-row">
                    <div class="label">Assigned Healthcare Provider:</div>
                    <div class="value">{{ $providerName }}</div>
                </div>
                <div class="info-row">
                    <div class="label">Reason / Remarks:</div>
                    <div class="value">{{ $reason }}</div>
                </div>
            </div>

            <p style="line-height: 1.5; color: #4b5563; font-size: 13px;">
                If you have questions or wish to book a new appointment, please reach out to the clinic staff.
            </p>
        </div>
        <div class="footer">
            Merchant Marine Academy of Caraga, Inc. &bull; Health Services Office
            <br>This is an automated notification from the Clinic Monitoring System.
        </div>
    </div>
</body>
</html>
