<!DOCTYPE html>
<html>
<head>
    <title>Account Restoration</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin: 20px auto; background-color: #ffffff;">
        <tr>
            <td style="padding: 20px;">
                <h2 style="color: #333;">Dear {{$userName}},</h2>
                <p style="font-size: 16px; color: #666;">We are sorry to hear that you're leaving us! On the bright side, we can still have you back if you use the URL below to restore your account within 14 days from now. Unfortunately, after this period, your account will be permanently deleted.</p>
                <p style="font-size: 16px; color: #666;">
                    <a href={{$url}} style="color: #007bff; text-decoration: none;">Restore Your Account</a>
                </p>
                <p style="font-size: 16px; color: #666;">Thank you for considering us!</p>
            </td>
        </tr>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin: 0 auto;">
        <tr>
            <td style="padding: 20px; text-align: center; background-color: #f6f6f6;">
                <p style="font-size: 14px; color: #999;">© 2023 YourCompany. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
