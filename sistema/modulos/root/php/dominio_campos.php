<?php
declare(strict_types=1);

/** Lista explícita compartida por la vista y el guardado; no expone parámetros técnicos. */
function dominio_campos(): array
{
    return [
        'Identidad y dominio' => [
            'dominio' => ['Dominio', 'url', 66, 12],
            'titulo_site' => ['Título del sitio', 'text', 66, 8],
            'descripcion_site' => ['Descripción del sitio', 'textarea', 220, 12],
            'keywords' => ['Palabras clave', 'textarea', 220, 12],
        ],
        'Contacto institucional' => [
            'email_alerta' => ['Correo para alertas', 'email', 220, 6],
        ],
        'Redes y servicios externos' => [
            'facebook' => ['Facebook', 'url', 150, 6],
            'twitter' => ['Twitter / X', 'url', 80, 6],
            'youtube' => ['YouTube', 'url', 80, 6],
            'instagram' => ['Instagram', 'url', 80, 6],
            'tiktok' => ['TikTok', 'url', 60, 6],
        ],
        'Contadores internos' => [
            'contador_1' => ['Contador 1', 'number', 10, 3],
            'contador_2' => ['Contador 2', 'number', 10, 3],
            'contador_3' => ['Contador 3', 'number', 10, 3],
            'contador_4' => ['Contador 4', 'number', 10, 3],
        ],
    ];
}
