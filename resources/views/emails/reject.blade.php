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
            <h1>Application Rejected</h1>
        </div>
        <div class="content">
            <p>Dear Applicant,</p>
            <p>We regret to inform you that your application has been rejected.</p>
            <p><strong>Reason for Rejection:</strong> {{ $reason }}</p>
            <p>You still have the opportunity to reapply in the future and make improvements based on the feedback provided.</p>
            <p>If you have any questions or need further clarification, please feel free to contact our support team.</p>
            <p>We appreciate your interest and effort, and we wish you the best in your future endeavors.</p>
        </div>
        <div class="footer">
            <p>Best regards,<br>The {{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>
