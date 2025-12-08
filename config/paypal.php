<?php
// Configuración mínima de PayPal. Rellenar con tus credenciales.
// Guardar este archivo con permisos seguros y no subir a repositorios públicos.
return [
    // 'sandbox' o 'live'
    'mode' => 'sandbox',
    'client_id' => 'AWO0SBvepzyGzSQYWdOpAcbx28YddlRE6Q6T1lj6BkDhMENk6IvcihYnfArameohT9U_JREyI05WoCTY',
    'secret' => 'EGwm9ZaOA4gg4ZuGnPDhxzL-rybOamrt2I2W79G-zegDaM1xuptLsRXwsXDJgtClz_4XxL3uvHX9E_Ul',
    // URL para llamadas a la API
    'api_base' => function ($mode) {
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }
];