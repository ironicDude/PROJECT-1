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
            <h1>Welcome to Remedya!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $name }},</p>
            <p>We're absolutely thrilled to have you join our family at Remedya. Congratulations on being accepted for the position – your talent and dedication truly shone through.</p>
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Password:</strong> {{ $password }}</p>
            <p>With these credentials, you're all set to log in to your Remedya account and embark on this exciting new journey with us.</p>
            <p>As you settle into your new role, remember that your success is our success. Your contributions are valued and will play an essential part in our shared accomplishments.</p>
            <p>We can't wait to see the positive impact you'll make and the ideas you'll bring to the table. Here's to a future full of achievements and growth!</p>
            <p>If you have any questions or need assistance, please don't hesitate to contact us at <a href="mailto:support@remedya.com">support@remedya.com</a>. We're here to support you every step of the way.</p>
        </div>
        <div class="footer">
            <p>Best regards,<br>The Remedya Team</p>
        </div>
    </div>
</body>
</html>
