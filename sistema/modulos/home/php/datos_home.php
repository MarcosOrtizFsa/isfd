<?php
/**
 * Configuración y contenidos demostrativos de Formosa Educa IA.
 *
 * Este archivo actúa como fuente de datos temporal. En una etapa posterior,
 * los arrays pueden reemplazarse por consultas sin modificar las plantillas.
 */

$portal = array(
    'nombre' => 'Formosa Educa IA',
    'sigla' => 'FE',
    'logo_ruta' => '../image/logo_formosa_educa_ia.svg',
    'descripcion' => 'Un portal educativo para explorar la inteligencia artificial con criterio pedagógico, responsabilidad y mirada crítica.',
    'correo' => 'Contacto por definir',
    'contacto' => 'Próximamente',
    'redes' => 'Próximamente'
);

// Archivo editorial histórico: no se publica cuando NEURIA está conectado.
// Se conserva como borrador local para futuras tareas de edición, no como fallback.
$novedades_demo = array(
    array(
        'id' => 'novedad-1',
        'categoria' => 'Formosa',
        'fecha' => 'Contenido demostrativo',
        'titulo' => 'Ideas para conversar sobre IA en la escuela',
        'resumen' => 'Preguntas iniciales para reconocer qué sabe el grupo y construir acuerdos de uso responsable.',
        'contenido' => 'Ejemplo editorial: una conversación guiada puede ayudar a distinguir qué hace una herramienta de IA, qué datos conviene proteger y por qué sus respuestas siempre deben revisarse.',
        'icono' => 'bi-chat-square-dots'
    ),
    array(
        'id' => 'novedad-2',
        'categoria' => 'Argentina',
        'fecha' => 'Contenido demostrativo',
        'titulo' => 'Claves para planificar actividades con IA',
        'resumen' => 'Una guía breve para comenzar por el objetivo pedagógico y elegir después la tecnología.',
        'contenido' => 'Ejemplo editorial: la planificación debe definir primero qué aprenderán los estudiantes, qué producirán y cómo se evaluará. La IA se incorpora únicamente cuando aporta valor a ese recorrido.',
        'icono' => 'bi-journal-check'
    ),
    array(
        'id' => 'novedad-3',
        'categoria' => 'Mundo',
        'fecha' => 'Contenido demostrativo',
        'titulo' => 'Aprender a verificar respuestas automáticas',
        'resumen' => 'Estrategias para contrastar información, detectar errores y reconocer límites de los sistemas.',
        'contenido' => 'Ejemplo editorial: comparar una respuesta generada con libros, materiales de clase y fuentes confiables transforma el posible error en una oportunidad de aprendizaje.',
        'icono' => 'bi-shield-check'
    ),
    array(
        'id' => 'novedad-4',
        'categoria' => 'Formosa',
        'fecha' => 'Contenido demostrativo',
        'titulo' => 'Producciones locales con identidad propia',
        'resumen' => 'La tecnología como apoyo para investigar y comunicar historias, ambientes y saberes de Formosa.',
        'contenido' => 'Ejemplo editorial: las producciones pueden partir de entrevistas, observaciones y bibliografía local. La IA ayuda a ordenar borradores, pero las decisiones y la voz final pertenecen a los estudiantes.',
        'icono' => 'bi-geo-alt'
    ),
    array(
        'id' => 'novedad-5',
        'categoria' => 'Argentina',
        'fecha' => 'Contenido demostrativo',
        'titulo' => 'Ciudadanía digital y cuidado de datos',
        'resumen' => 'Una oportunidad para trabajar privacidad, autoría y convivencia en entornos digitales.',
        'contenido' => 'Ejemplo editorial: antes de usar una plataforma es importante revisar sus condiciones, las edades permitidas y qué información solicita. Nunca deben cargarse datos personales de estudiantes.',
        'icono' => 'bi-person-lock'
    ),
    array(
        'id' => 'novedad-6',
        'categoria' => 'Mundo',
        'fecha' => 'Contenido demostrativo',
        'titulo' => 'Creatividad humana acompañada por tecnología',
        'resumen' => 'Propuestas donde las herramientas amplían alternativas sin reemplazar la producción personal.',
        'contenido' => 'Ejemplo editorial: pedir varias alternativas a una IA puede servir para analizar estilos o iniciar un borrador. El grupo debe transformar, justificar y firmar su producción final.',
        'icono' => 'bi-lightbulb'
    )
);

$ideas = array(
    array('id' => 'idea-1', 'titulo' => 'Crear ejercicios con distintos niveles', 'objetivo_breve' => 'Adaptar la práctica sin perder un objetivo común.', 'nivel' => 'Primario y secundario', 'objetivo' => 'Resolver actividades graduadas y explicar las estrategias utilizadas.', 'destinatarios' => 'Estudiantes de nivel primario o secundario.', 'materiales' => 'Contenido trabajado, consignas base y una herramienta de IA habilitada.', 'pasos' => array('Definir el contenido y el aprendizaje esperado.', 'Solicitar ejemplos de dificultad inicial, media y avanzada.', 'Revisar, corregir y seleccionar las consignas.', 'Resolver y comparar estrategias en el aula.'), 'docente' => 'Define criterios, revisa cada ejercicio y acompaña la resolución.', 'uso_ia' => 'Propone variantes que el docente valida antes de utilizarlas.', 'evaluacion' => 'Explicación del procedimiento y revisión de errores.', 'cuidados' => 'No cargar datos personales. Verificar resultados y adecuación a la edad.'),
    array('id' => 'idea-2', 'titulo' => 'Analizar errores de una respuesta', 'objetivo_breve' => 'Desarrollar lectura crítica y argumentación.', 'nivel' => 'Secundario y superior', 'objetivo' => 'Detectar afirmaciones dudosas y justificar correcciones.', 'destinatarios' => 'Estudiantes de nivel secundario o superior.', 'materiales' => 'Una respuesta generada, bibliografía y fuentes confiables.', 'pasos' => array('Presentar una respuesta sin asumir que es correcta.', 'Marcar datos que necesitan verificación.', 'Contrastar con al menos dos fuentes.', 'Reescribir la respuesta y explicar los cambios.'), 'docente' => 'Selecciona el caso y modela criterios de verificación.', 'uso_ia' => 'Genera un texto que funciona como objeto de análisis.', 'evaluacion' => 'Calidad de las evidencias y claridad de la corrección.', 'cuidados' => 'Reconocer errores y sesgos. Explicitar que el texto inicial fue generado con IA.'),
    array('id' => 'idea-3', 'titulo' => 'Preparar preguntas sobre un texto', 'objetivo_breve' => 'Profundizar la comprensión lectora.', 'nivel' => 'Primario y secundario', 'objetivo' => 'Formular preguntas literales, inferenciales y críticas.', 'destinatarios' => 'Estudiantes de los últimos años de primaria y nivel secundario.', 'materiales' => 'Texto de estudio y herramienta de IA supervisada.', 'pasos' => array('Leer el texto completo.', 'Definir tres tipos de preguntas.', 'Pedir ejemplos a la IA sin incluir datos personales.', 'Evaluar, mejorar y responder las preguntas elegidas.'), 'docente' => 'Orienta la lectura y valida la pertinencia de las preguntas.', 'uso_ia' => 'Sugiere preguntas que el grupo clasifica y mejora.', 'evaluacion' => 'Pertinencia de las preguntas y fundamentos de las respuestas.', 'cuidados' => 'Verificar que las preguntas se respondan desde el texto y no induzcan errores.'),
    array('id' => 'idea-4', 'titulo' => 'Diseñar escritura creativa', 'objetivo_breve' => 'Producir textos propios a partir de disparadores.', 'nivel' => 'Todos los niveles', 'objetivo' => 'Planificar, escribir y revisar una producción con voz personal.', 'destinatarios' => 'Adaptable a distintos niveles educativos.', 'materiales' => 'Cuaderno o editor de texto y disparadores creados en clase.', 'pasos' => array('Acordar género, destinatario y extensión.', 'Generar posibles escenarios o conflictos.', 'Elegir y transformar un disparador.', 'Escribir, revisar y compartir la versión propia.'), 'docente' => 'Acompaña decisiones de escritura y devolución entre pares.', 'uso_ia' => 'Ofrece disparadores; no redacta la entrega final.', 'evaluacion' => 'Proceso de revisión, coherencia y decisiones personales.', 'cuidados' => 'Declarar el uso de IA y evitar copiar una respuesta completa.'),
    array('id' => 'idea-5', 'titulo' => 'Comparar y verificar fuentes', 'objetivo_breve' => 'Evaluar confiabilidad y construir evidencia.', 'nivel' => 'Secundario y superior', 'objetivo' => 'Distinguir afirmaciones, fuentes y evidencias verificables.', 'destinatarios' => 'Estudiantes de nivel secundario o superior.', 'materiales' => 'Respuesta de IA, buscadores, biblioteca y ficha de análisis.', 'pasos' => array('Separar las afirmaciones principales.', 'Buscar el origen de cada dato.', 'Comparar autoría, fecha y propósito de las fuentes.', 'Elaborar una conclusión con referencias.'), 'docente' => 'Enseña criterios de autoridad, actualidad y trazabilidad.', 'uso_ia' => 'Aporta una respuesta inicial que debe ser contrastada.', 'evaluacion' => 'Selección de fuentes y justificación de confiabilidad.', 'cuidados' => 'No aceptar citas inexistentes. Reconocer sesgos y verificar enlaces.'),
    array('id' => 'idea-6', 'titulo' => 'Organizar una guía de estudio', 'objetivo_breve' => 'Planificar el repaso de manera consciente.', 'nivel' => 'Secundario y superior', 'objetivo' => 'Jerarquizar contenidos y construir una guía personal de estudio.', 'destinatarios' => 'Estudiantes de nivel secundario o superior.', 'materiales' => 'Programa, apuntes propios y materiales de la asignatura.', 'pasos' => array('Listar los temas realmente trabajados.', 'Ordenarlos por relación y dificultad.', 'Solicitar una estructura posible.', 'Corregirla y completar cada punto con materiales de clase.'), 'docente' => 'Valida prioridades y ayuda a identificar omisiones.', 'uso_ia' => 'Sugiere una organización, nunca reemplaza los apuntes ni la lectura.', 'evaluacion' => 'Cobertura de contenidos y utilidad de la guía producida.', 'cuidados' => 'No subir evaluaciones privadas ni información personal. Revisar condiciones y edades permitidas.'),
);
?>
