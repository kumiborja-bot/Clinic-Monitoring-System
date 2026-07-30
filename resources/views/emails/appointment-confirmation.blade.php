<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Confirmation</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #333333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .header { background: #132639; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #fbbf24; }
        .content { padding: 30px 24px; }
        .greeting { font-size: 16px; font-weight: bold; color: #111827; margin-bottom: 12px; }
        .info-card { background: #f8fafc; border-left: 4px solid #095d86; padding: 16px; margin: 20px 0; border-radius: 4px; }
        .info-row { margin-bottom: 10px; display: flex; font-size: 14px; }
        .info-row:last-child { margin-bottom: 0; }
        .label { font-weight: bold; width: 180px; color: #475569; }
        .value { color: #0f172a; flex: 1; }
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
            <p style="margin-top: 0; line-height: 1.5; color: #4b5563;">
                Your clinic appointment has been successfully scheduled. Please review the details of your appointment below:
            </p>

            <div class="info-card">
                <div class="info-row">
                    <div class="label">Appointment Date & Time:</div>
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
                @if($reason)
                <div class="info-row">
                    <div class="label">Reason for Visit:</div>
                    <div class="value">{{ $reason }}</div>
                </div>
                @endif
            </div>

            <p style="line-height: 1.5; color: #4b5563; font-size: 13px;">
                <strong>Important Reminders:</strong>
                <br>&bull; Please arrive 10 minutes prior to your scheduled time.
                <br>&bull; Bring your institutional ID and any relevant medical records.
                <br>&bull; If you need to reschedule or cancel, please contact the clinic staff promptly.
            </p>
        </div>
        <div class="footer">
            Merchant Marine Academy of Caraga, Inc. &bull; Health Services Office
            <br>This is an automated email notification from the Clinic Monitoring System.
        </div>
    </div>
</body>
</html>
