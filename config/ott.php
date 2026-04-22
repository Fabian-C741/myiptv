<?php
/**
 * Configuración centralizada del sistema OTT.
 * Todas las constantes de negocio van aquí para facilitar
 * cambios sin tocar el código fuente.
 */
return [
    // Límite por defecto de dispositivos por usuario
    'default_max_devices' => 3,

    // Límite máximo de perfiles por usuario (estilo Netflix)
    'max_profiles' => 5,

    // Duración del token de sesión (en meses)
    'token_lifetime_months' => 6,

    // Cache: Tiempo de vida en segundos para datos frecuentes
    'cache' => [
        'epg_ttl'      => 300,  // 5 minutos
        'channels_ttl' => 600,  // 10 minutos
        'groups_ttl'   => 3600, // 1 hora
    ],

    // Palabras clave para detectar contenido adulto en listas M3U
    'adult_keywords' => ['xxx', 'adult', 'erotic', 'x18', 'xrated', 'porno'],

    // Configuración de EPG XMLTV
    'epg' => [
        'download_timeout' => 120, // segundos
        'max_days_stored' => 3,    // días de programación guardados
    ],

    // Rate limiting (login)
    'rate_limits' => [
        'login_attempts' => 5,
        'login_window'   => 1,  // minutos
    ],
];
