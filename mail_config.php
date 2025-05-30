<?php
// PHPMailer configuration file
// Please modify the following settings according to your email service provider

return [
    // SMTP server settings
    'smtp_host' => 'smtp.gmail.com',        // Gmail SMTP server
    'smtp_port' => 587,                     // TLS port (Gmail)
    'smtp_secure' => 'tls',                 // Encryption method: tls or ssl
    
    // Email authentication information
    'smtp_username' => 'gesturo.lol@gmail.com',     // Your email address
    'smtp_password' => 'tlfnojplcciineao',        // Your email password or app-specific password
    
    // Sender information
    'from_email' => 'your-email@gmail.com',        // Sender email
    'from_name' => 'Reservation System',           // Sender name
    
    // Default settings
    'charset' => 'UTF-8',
    'is_html' => true,
    
    // Other common email service provider settings
    'providers' => [
        'gmail' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'secure' => 'tls'
        ],
        'outlook' => [
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'secure' => 'tls'
        ],
        'yahoo' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'secure' => 'tls'
        ],
        'qq' => [
            'host' => 'smtp.qq.com',
            'port' => 587,
            'secure' => 'tls'
        ],
        '163' => [
            'host' => 'smtp.163.com',
            'port' => 465,
            'secure' => 'ssl'
        ]
    ]
];
?> 