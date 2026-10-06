<?php
/**
 * Sanitiza HTML editorial recibido desde NEURIA mediante una lista permitida.
 * Elimina scripts, iframes, estilos, eventos inline y protocolos peligrosos.
 */
function neuria_sanitizar_html($html)
{
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    // NEURIA puede entregar el cuerpo como texto plano. HTML colapsa los saltos
    // de línea, por eso los convertimos en párrafos y cortes seguros antes de
    // continuar. htmlspecialchars impide que ese texto se interprete como HTML.
    if ($html === strip_tags($html)) {
        $texto = str_replace(array("\r\n", "\r"), "\n", trim($html));
        $bloques = preg_split('/\n{2,}/', $texto);
        $parrafos = array();
        foreach ($bloques as $bloque) {
            $bloque = trim($bloque);
            if ($bloque === '') {
                continue;
            }
            $seguro = htmlspecialchars($bloque, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $parrafos[] = '<p>' . nl2br($seguro, false) . '</p>';
        }
        return implode("\n", $parrafos);
    }

    $permitidas = array('p', 'br', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'a');
    $documento = new DOMDocument('1.0', 'UTF-8');
    $estadoAnterior = libxml_use_internal_errors(true);
    $documento->loadHTML('<?xml encoding="utf-8" ?><div id="neuria-contenido">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($estadoAnterior);

    $contenedor = $documento->getElementById('neuria-contenido');
    if (!$contenedor) {
        return '';
    }

    neuria_limpiar_nodo($contenedor, $permitidas);
    $salida = '';
    foreach ($contenedor->childNodes as $hijo) {
        $salida .= $documento->saveHTML($hijo);
    }
    return $salida;
}

function neuria_limpiar_nodo(DOMNode $nodo, array $permitidas)
{
    for ($indice = $nodo->childNodes->length - 1; $indice >= 0; $indice--) {
        $hijo = $nodo->childNodes->item($indice);
        if ($hijo->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        $etiqueta = strtolower($hijo->nodeName);
        if (!in_array($etiqueta, $permitidas, true)) {
            // Conserva el texto de etiquetas neutras, pero elimina por completo
            // elementos ejecutables o embebidos junto con su contenido.
            if (in_array($etiqueta, array('script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'), true)) {
                $nodo->removeChild($hijo);
                continue;
            }
            while ($hijo->firstChild) {
                $nodo->insertBefore($hijo->firstChild, $hijo);
            }
            $nodo->removeChild($hijo);
            continue;
        }

        if ($hijo->hasAttributes()) {
            for ($a = $hijo->attributes->length - 1; $a >= 0; $a--) {
                $atributo = $hijo->attributes->item($a);
                $nombre = strtolower($atributo->name);
                $permitido = $etiqueta === 'a' && in_array($nombre, array('href', 'title'), true);
                if (!$permitido) {
                    $hijo->removeAttribute($atributo->name);
                }
            }
        }

        if ($etiqueta === 'a') {
            $href = trim($hijo->getAttribute('href'));
            if ($href !== '' && !preg_match('#^(https?://|mailto:|/)#i', $href)) {
                $hijo->removeAttribute('href');
            }
            $hijo->setAttribute('target', '_blank');
            $hijo->setAttribute('rel', 'noopener noreferrer');
        }
        neuria_limpiar_nodo($hijo, $permitidas);
    }
}
?>
