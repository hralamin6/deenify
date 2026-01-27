<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoiceNumber }}</title>
</head>
<body style="font-family: 'examplefont', monospace; font-size: 11pt; color: #000; margin: 0; padding: 20px; max-width: 400px;">
    @php
        $appName = setting('app.name', 'Deenify');
        $appEmail = setting('app.email', 'support@deenify.com');
        $appPhone = setting('app.phone', '+880 1234-567890');
        
        // Get logo absolute path for mPDF
        $logoSetting = \App\Models\Setting::where('key', 'iconImage')->first();
        $logoMedia = $logoSetting?->getFirstMedia('icon');
        $logo = $logoMedia ? $logoMedia->getPath() : null;
        
        $status = strtolower($donation->status);
    @endphp

    <!-- HEADER -->
    <div style="text-align: center; border-bottom: 2px dashed #000; padding-bottom: 15px; margin-bottom: 15px;">
        @if($logo && file_exists($logo))
            <img src="{{ $logo }}" style="width: 50px; height: 50px; border-radius: 6px;">
        @endif
        <div style="font-size: 16pt; font-weight: bold; margin-bottom: 3px;">{{ strtoupper($appName) }}</div>
        <div style="font-size: 9pt;">{{ $appEmail }}</div>
        <div style="font-size: 9pt;">{{ $appPhone }}</div>
    </div>

    <!-- RECEIPT TITLE -->
    <div style="text-align: center; margin-bottom: 15px;">
        <div style="font-size: 14pt; font-weight: bold; letter-spacing: 2px;">DONATION RECEIPT</div>
        <div style="font-size: 9pt; margin-top: 5px;">#{{ $invoiceNumber }}</div>
    </div>

    <!-- DETAILS -->
    <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin-bottom: 10px;">
        <table style="width: 100%; font-size: 10pt;">
            <tr>
                <td style="padding: 3px 0;">Date:</td>
                <td style="text-align: right; font-weight: bold;">{{ $donation->created_at->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;">Time:</td>
                <td style="text-align: right; font-weight: bold;">{{ $donation->created_at->format('h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;">Donor:</td>
                <td style="text-align: right; font-weight: bold;">{{ Str::limit($donation->donor_name, 20) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;">Email:</td>
                <td style="text-align: right; font-size: 9pt;">{{ Str::limit($donation->donor_email ?: 'Guest', 25) }}</td>
            </tr>
        </table>
    </div>

    <!-- CAMPAIGN -->
    <div style="margin-bottom: 10px;">
        <div style="font-size: 9pt; font-weight: bold; margin-bottom: 5px;">CAMPAIGN:</div>
        <div style="font-size: 10pt; padding-left: 10px;">{{ $campaign->title }}</div>
    </div>

    <!-- PAYMENT INFO -->
    <div style="border-top: 1px dashed #000; padding-top: 10px; margin-bottom: 10px;">
        <table style="width: 100%; font-size: 10pt;">
            <tr>
                <td style="padding: 3px 0;">Gateway:</td>
                <td style="text-align: right;">{{ $paymentGateway ?: 'N/A' }}</td>
            </tr>
            @if($transactionId)
            <tr>
                <td style="padding: 3px 0;">TXN ID:</td>
                <td style="text-align: right; font-size: 8pt; font-family: monospace;">{{ Str::limit($transactionId, 18) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 3px 0;">Status:</td>
                <td style="text-align: right; font-weight: bold;">{{ strtoupper($status) }}</td>
            </tr>
        </table>
    </div>

    <!-- AMOUNT -->
    <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 12px 0; margin: 15px 0; text-align: center;">
        <div style="font-size: 10pt; margin-bottom: 5px;">TOTAL AMOUNT</div>
        <div style="font-size: 20pt; font-weight: bold; letter-spacing: 1px;">
            {{ $donation->currency === 'BDT' ? '৳' : $donation->currency }} {{ number_format($donation->amount, 2) }}
        </div>
    </div>

    <!-- FOOTER -->
    <div style="text-align: center; font-size: 9pt; margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000;">
        <div style="font-weight: bold; margin-bottom: 5px;">THANK YOU!</div>
        <div style="margin-bottom: 8px;">Your contribution makes a difference</div>
        <div style="font-size: 8pt; color: #666;">
            Generated: {{ now()->format('Y-m-d H:i:s') }}<br>
            ID: #{{ $donation->id }}
        </div>
    </div>

    <div style="text-align: center; font-size: 7pt; margin-top: 15px; color: #999;">
        This is a computer-generated receipt
    </div>
</body>
</html>