<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MAIL {
    
    public function sendEmailWithAttachment($filename)
    {
        $logger = $GLOBALS['fcLogger'];
    
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'dzariq85@gmail.com';
            $mail->Password = 'haxsutjoonpyajff';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->SMTPDebug = 0;
            $mail->setFrom('dzariq85@gmail.com', 'senangpay');
            $mail->addAddress('dzariq85@gmail.com', 'Dzariq');
    
            $mail->isHTML(true);
            $mail->Subject = 'senangPay Settlement File';
            $mail->Body = 'Attached';
    
            // Attach the Excel file
            $mail->addAttachment($filename);
    
            $mail->send();
            $logger->info("sent!");
        } catch (Exception $e) {
            $logger->info("error email: !" . $mail->ErrorInfo);
        }
    }
}