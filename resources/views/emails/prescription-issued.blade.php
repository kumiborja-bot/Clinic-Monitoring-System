<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Digital Medical Prescription</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #333333; margin: 0; padding: 20px; }
        .container { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .header { background: #132639; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #fbbf24; }
        .content { padding: 30px 24px; }
        .rx-title { font-size: 18px; font-weight: bold; color: #095d86; border-bottom: 2px solid #095d86; padding-bottom: 8px; margin-bottom: 16px; }
        .patient-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; margin-bottom: 20px; font-size: 13px; }
        .med-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        .med-table th { background: #f1f5f9; color: #334155; font-size: 12px; text-transform: uppercase; text-align: left; padding: 10px; border-bottom: 2px solid #cbd5e1; }
        .med-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b; }
        .rx-symbol { font-size: 24px; font-weight: bold; color: #095d86; font-family: serif; }
        .provider-box { margin-top: 25px; padding-top: 15px; border-top: 1px dashed #cbd5e1; font-size: 13px; color: #475569; }
        .footer { background: #f1f5f9; padding: 16px 24px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Merchant Marine Academy of Caraga, Inc.</h1>
            <p>Clinic Health Monitoring System - Health Services Office</p>
        </div>
        <div class="content">
            <div class="rx-title">
                <span class="rx-symbol">Rx</span> Medical Prescription
            </div>

            <div class="patient-box">
                <strong>Patient Name:</strong> {{ $patientName }}<br>
                <strong>Date Issued:</strong> {{ $issuedAt }}<br>
                <strong>Prescribing Provider:</strong> {{ $prescriberName }}
            </div>

            <p style="font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #1e293b;">Prescribed Medications:</p>

            <table class="med-table">
                <thead>
                    <tr>
                        <th>Medication Name</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th>Instructions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicationDetails as $item)
                    <tr>
                        <td><strong>{{ $item['medicine_name'] ?? 'N/A' }}</strong></td>
                        <td>{{ $item['dosage'] ?? '-' }}</td>
                        <td>{{ $item['frequency'] ?? '-' }}</td>
                        <td>{{ $item['duration'] ?? '-' }}</td>
                        <td>{{ $item['instructions'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($notes)
            <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 12px; border-radius: 6px; font-size: 13px; color: #92400e; margin-bottom: 20px;">
                <strong>Special Remarks / Precautions:</strong><br>
                {{ $notes }}
            </div>
            @endif

            <div class="provider-box">
                Issued by: <strong>{{ $prescriberName }}</strong><br>
                Institutional Clinic Services &bull; Official Digital Record
            </div>
        </div>
        <div class="footer">
            Merchant Marine Academy of Caraga, Inc. &bull; Health Services Office
            <br>This digital prescription has been recorded in the central health monitoring system.
        </div>
    </div>
</body>
</html>
