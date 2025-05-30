<?php
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    private $config;
    
    public function __construct() {
        $this->config = require 'mail_config.php';
        $this->mail = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    private function setupSMTP() {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['smtp_host'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['smtp_username'];
            $this->mail->Password = $this->config['smtp_password'];
            $this->mail->SMTPSecure = $this->config['smtp_secure'];
            $this->mail->Port = $this->config['smtp_port'];
            
            // Character encoding
            $this->mail->CharSet = $this->config['charset'];
            
            // Sender information
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
            
        } catch (Exception $e) {
            throw new Exception("SMTP setup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Send email
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email content
     * @param string $toName Recipient name (optional)
     * @param bool $isHTML Whether HTML format (default true)
     * @return bool
     */
    public function sendMail($to, $subject, $body, $toName = '', $isHTML = true) {
        try {
            // Recipients
            $this->mail->addAddress($to, $toName);
            
            // Email content
            $this->mail->isHTML($isHTML);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            
            // If HTML email, set plain text version
            if ($isHTML) {
                $this->mail->AltBody = strip_tags($body);
            }
            
            $result = $this->mail->send();
            
            // Clear recipients for next send
            $this->mail->clearAddresses();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send verification code email
     */
    public function sendVerificationCode($to, $code, $username = '') {
        $subject = "Your Verification Code - Reservation System";
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center;'>
                <h2 style='color: #333;'>Verification Code</h2>
            </div>
            <div style='padding: 30px; background-color: white;'>
                <p>Hello" . ($username ? " {$username}" : "") . ",</p>
                <p>Your verification code is:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; padding: 15px 30px; border: 2px solid #007bff; border-radius: 5px;'>{$code}</span>
                </div>
                <p>This verification code is valid for 10 minutes. Please use it promptly.</p>
                <p>If you did not request this verification code, please ignore this email.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='color: #666; font-size: 14px;'>This email was sent automatically by the system. Please do not reply.</p>
            </div>
        </div>";
        
        return $this->sendMail($to, $subject, $body);
    }
    
    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($to, $username) {
        $subject = "Welcome to Reservation System";
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #007bff; padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Welcome to</h2>
                <h3 style='color: white; margin: 5px 0 0 0;'>Reservation System</h3>
            </div>
            <div style='padding: 30px; background-color: white;'>
                <p>Dear {$username},</p>
                <p>Welcome! You have successfully registered for Reservation System!</p>
                <p>You can now:</p>
                <ul>
                    <li>Book meeting rooms and discussion rooms</li>
                    <li>Reserve sports facilities</li>
                    <li>Book laboratory equipment</li>
                    <li>View and manage your booking records</li>
                </ul>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/login.php' style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Login Now</a>
                </div>
                <p>Thank you for joining us!</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='color: #666; font-size: 14px;'>This email was sent automatically by the system. Please do not reply.</p>
            </div>
        </div>";
        
        return $this->sendMail($to, $subject, $body);
    }
    
    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation($to, $username, $bookingDetails) {
        $subject = "Booking Confirmation - Reservation System";
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #28a745; padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Booking Confirmation</h2>
            </div>
            <div style='padding: 30px; background-color: white;'>
                <p>Hello {$username},</p>
                <p>Your booking has been successfully confirmed!</p>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #333;'>Booking Details:</h4>
                    <p><strong>Item:</strong> {$bookingDetails['item']}</p>
                    <p><strong>Date:</strong> {$bookingDetails['date']}</p>
                    <p><strong>Time:</strong> {$bookingDetails['time']}</p>
                    <p><strong>Booking ID:</strong> {$bookingDetails['booking_id']}</p>
                </div>
                <p>Please arrive on time and follow the relevant regulations.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/general.php' style='background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>View My Bookings</a>
                </div>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='color: #666; font-size: 14px;'>This email was sent automatically by the system. Please do not reply.</p>
            </div>
        </div>";
        
        return $this->sendMail($to, $subject, $body);
    }
    
    /**
     * Get last error information
     */
    public function getLastError() {
        return $this->mail->ErrorInfo;
    }
}
?> 