<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord reset</title>
</head>

<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7fa; line-height: 1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f7fa; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td bgcolor="#2ab6cb" style="padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">
                                Wachtwoord Resetten
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px;">
                                Hallo,
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #555555; font-size: 15px; line-height: 1.7;">
                                Er is een verzoek ingediend om je wachtwoord te resetten. Klik op de onderstaande knop om een nieuw wachtwoord in te stellen.
                            </p>

                            <!-- Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 30px 0;">
                                        <a href="{!! $url !!}" style="display: inline-block; background-color: #2ab6cb; color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; letter-spacing: 0.3px;">
                                            Reset Wachtwoord
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Warning Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fff8e6; border-left: 4px solid #ffa726; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0; color: #8d6e0d; font-size: 14px; line-height: 1.6;">
                                            <strong>⏱️ Let op:</strong> Deze link verloopt over 60 minuten.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 20px 0; color: #666666; font-size: 14px; line-height: 1.7;">
                                Als je dit verzoek niet hebt aangevraagd, kun je dit bericht gewoon negeren. Je wachtwoord blijft dan ongewijzigd.
                            </p>

                            <!-- Fallback Link -->
                            <p style="margin: 25px 0 0 0; padding-top: 25px; border-top: 1px solid #e5e7eb; color: #888888; font-size: 13px; line-height: 1.6;">
                                <strong>Werkt de knop niet?</strong><br>
                                Kopieer en plak deze link in je browser:<br>
                                <a href="{!! $url !!}" style="color: #2ab6cb; word-break: break-all;">{!! $url !!}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #9ca3af; font-size: 13px; line-height: 1.6;">
                                Deze e-mail is automatisch verzonden. Reageer niet op dit bericht.
                            </p>
                        </td>
                    </tr>
                </table>
 
              
            </td>
        </tr>
    </table>
</body>

</html>