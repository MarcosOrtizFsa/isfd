<?php
/** Fuente unica de datos institucionales editables. */
$institucion = [
    'nombre' => 'Instituto Superior de Formación Docente Continua y Técnica «Félix Atilio Cabrera»',
    'nombre_corto' => 'Félix A. Cabrera',
    'sigla' => 'ISFDCyT F.A.C.',
    'direccion' => 'Córdoba 1125, Barrio Don Bosco, Formosa Capital, CP 3600',
    'entorno' => 'Córdoba, Fotheringham, Hipólito Yrigoyen y Fortín Yunká',
    'telefono' => '03717-430298',
    'email' => 'ifd.fac@formosa.gov.ar', // Pendiente de confirmación institucional.
    'jardin' => 'Fortín Yunká 1190',
    'horarios' => [
        'Mañana' => '07:15 a 12:30 · Nivel Secundario',
        'Tarde' => '13:15 a 18:00 · Nivel Primario',
        'Noche' => '18:45 a 22:30 · Nivel Superior',
    ],
    'campus_url' => 'https://isfdcytcabrera-for.infd.edu.ar/aula',
    'preinscripcion_url' => '../#contacto',
    'revista_url' => 'https://elgrito-fac.com.ar',
    'facebook_url' => '',
    'instagram_url' => '',
    'youtube_url' => '',
    'mapa_latitud' => '-26.1849',
    'mapa_longitud' => '-58.1759',
    'mapa_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=-58.1859%2C-26.1929%2C-58.1659%2C-26.1769&layer=mapnik&marker=-26.1849%2C-58.1759',
    'hero_imagen' => '../image/hero-instituto-provisorio.png', // Provisoria hasta recibir la foto oficial.
    'institucional_imagen' => '../image/logo.jpeg',
];

// Información histórica; se presenta resumida en Home y completa en su vista.
$historiaInstitucional = [
    'Nace en el marco del Decreto 1008/75, que organizó los institutos de formación docente de la provincia.',
    'Fue creado por Decreto 505/78 con los profesorados de Enseñanza Primaria y Educación Física.',
    'En 1982 se incorporó el Nivel Primario y en 1985 el Nivel Medio.',
    'Desde 1986 lleva el nombre de Don Félix Atilio Cabrera, referente de la cultura y la educación formoseña.',
];

// Datos históricos a verificar antes de su publicación definitiva.
$autoridades = [
    ['cargo' => 'Director', 'nombre' => 'Prof. Antonio Heraldo Prieto'],
    ['cargo' => 'Secretario docente', 'nombre' => 'Lic. Juan Claudio Ferreyra'],
    ['cargo' => 'Secretario contable', 'nombre' => 'Jorge Verón'],
];



$institucion['hero_etiqueta'] = 'www.isfd.com.ar';
$institucion['hero_titulo'] = 'Instituto Superior';
$institucion['hero_nombre'] = 'Félix Atilio Cabrera';
$institucion['hero_descripcion'] = 'Formación Docente Continua y Técnica. Más de tres décadas formando niños, jóvenes y adultos en toda la provincia de Formosa, con propuestas de nivel inicial, primario, secundario y superior.';
$institucion['pie_texto'] = 'Formación Docente Continua y Técnica en Formosa.';
$institucion['historia_titulo'] = 'Una institución pionera en la docencia formoseña, desde 1978.';
$institucion['historia_resumen'] = 'Creado por Decreto 505/78, el instituto lleva el nombre de Don Félix Atilio Cabrera desde 1986. Conviven en un mismo predio el nivel superior y tres departamentos de aplicación.';
$institucion['historia_texto'] = implode("\n", $historiaInstitucional);
$institucion['autoridades_texto'] = implode("\n", array_map(static fn($a) => $a['cargo'].' | '.$a['nombre'], $autoridades));
$institucion['manana'] = $institucion['horarios']['Mañana'];
$institucion['tarde'] = $institucion['horarios']['Tarde'];
$institucion['noche'] = $institucion['horarios']['Noche'];
$institucion['videos_json'] = '[]';
return $institucion;
