<?php
/**
 * Configuración de seguridad avanzada del sistema OTT.
 */
return [
    // Rondas de Bcrypt para hashing de contraseñas y PINs
    'bcrypt_rounds' => 12,

    // IPs que siempre se ignoran en el sistema de alertas
    'trusted_ips' => [
        '127.0.0.1',
        '::1',
    ],

    // Activar/desactivar logs de seguridad en canal separado
    'security_log' => env('SECURITY_LOG', true),

    // Canal de log para eventos de seguridad
    'log_channel' => 'daily',

    // Número de intentos fallidos antes de registrar una alerta
    'alert_threshold' => 5,
];
