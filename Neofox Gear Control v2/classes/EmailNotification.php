<?php
// classes/EmailNotification.php
class EmailNotification {
    public static function sendCheckoutConfirmation($to_email, $asset_name, $borrower, $return_date) {
        $subject = "Equipment Checked Out - {$asset_name}";
        $message = "
        <h3>Equipment Checkout Confirmation</h3>
        <p><strong>Asset:</strong> {$asset_name}</p>
        <p><strong>Borrower:</strong> {$borrower}</p>
        <p><strong>Expected Return:</strong> {$return_date}</p>
        <p>Please return the equipment on time and in good condition.</p>
        ";
        
        return self::sendEmail($to_email, $subject, $message);
    }

    public static function sendOverdueAlert($to_email, $asset_name, $borrower, $days_overdue) {
        $subject = "OVERDUE EQUIPMENT ALERT - {$asset_name}";
        $message = "
        <h3>Equipment Overdue Alert</h3>
        <p><strong>Asset:</strong> {$asset_name}</p>
        <p><strong>Borrower:</strong> {$borrower}</p>
        <p><strong>Days Overdue:</strong> {$days_overdue}</p>
        <p style='color: red;'><strong>This equipment needs to be returned immediately!</strong></p>
        ";
        
        return self::sendEmail($to_email, $subject, $message);
    }

    public static function sendEmail($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Neofox Gear Control <noreply@neofoxmedia.com>' . "\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
}
?>