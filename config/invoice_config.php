<?php
// Configuración de rutas para facturas
return [
    'invoice_dir' => __DIR__ . '/../img/uploads/invoices/',
    'mapping_file' => __DIR__ . '/../data/invoices.json'
    // Si true, el webhook intentará enviar la factura por email automáticamente
    ,'email_on_webhook' => false
];
