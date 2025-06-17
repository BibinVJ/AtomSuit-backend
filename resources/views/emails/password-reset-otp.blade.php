<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
            color: #212529;
        }

        .email-container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .info {
            margin-bottom: 12px;
        }

        .info strong {
            display: inline-block;
            width: 140px;
            color: #495057;
        }

        .message {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <p>Your OTP for password reset is: <strong>{{ $otp }}</strong></p>
        <p>This OTP is valid for 10 minutes.</p>
    </div>
</body>
</html>
