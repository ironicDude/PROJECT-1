<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Rejected</h1>
        </div>
        <div class="content">
            <p>Dear Customer,</p>
            <p>We regret to inform you that your order with ID: {{ $orderId }} has been rejected.</p>
            <p><strong>Reason for Rejection:</strong> {{ $reason }}</p>
            <p>You still have the opportunity to address the issue and fix the missing files. Please edit your order and upload the required files to proceed with the processing.</p>
            <p>If you have any questions or concerns, please feel free to contact our customer support.</p>
            <p>We apologize for any inconvenience this may have caused and appreciate your understanding.</p>
        </div>
        <div class="footer">
            <p>Best regards,<br>The {{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>
