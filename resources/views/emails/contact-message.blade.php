<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Message from {{ $contactData['name'] }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #000;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: 300;
            color: #000;
            letter-spacing: 2px;
        }
        .message-info {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #000;
        }
        .message-info h3 {
            margin-top: 0;
            color: #000;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: 600;
            color: #000;
        }
        .message-content {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        .footer {
            border-top: 1px solid #ddd;
            margin-top: 30px;
            padding-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">JESICA RIAD</div>
    </div>

    <h2>New Contact Message</h2>

    <div class="message-info">
        <h3>Contact Information</h3>
        <div class="info-row">
            <span class="label">Name:</span> {{ $contactData['name'] }}
        </div>
        <div class="info-row">
            <span class="label">Email:</span> {{ $contactData['email'] }}
        </div>
        <div class="info-row">
            <span class="label">Subject:</span> {{ $contactData['subject'] }}
        </div>
        <div class="info-row">
            <span class="label">Date:</span> {{ now()->format('F j, Y \a\t g:i A') }}
        </div>
    </div>

    <div class="message-content">
        <h3>Message:</h3>
        <p>{{ nl2br(e($contactData['message'])) }}</p>
    </div>

    <div class="footer">
        <p>This message was sent through the contact form on your website.</p>
        <p>You can reply directly to this email to respond to {{ $contactData['name'] }}.</p>
    </div>
</body>
</html>
