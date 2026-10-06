(function () {
  'use strict';

  /** Devuelve datos JSON locales sin evaluar código ni insertar HTML. */
  function leerDatos(id) {
    var nodo = document.getElementById(id);
    if (!nodo) return [];
    try { return JSON.parse(nodo.textContent); } catch (error) { return []; }
  }

  function buscarPorId(datos, id) {
    return datos.find(function (item) { return item.id === id; });
  }

  function escribir(id, valor) {
    var nodo = document.getElementById(id);
    if (nodo) nodo.textContent = valor || '';
  }

  /**
   * Solicita el detalle al endpoint interno. El token permanece en PHP y el
   * HTML recibido ya fue sanitizado mediante una lista permitida del servidor.
   */
  async function abrirNovedad(id) {
    var modal = document.getElementById('modal-novedad');
    if (!modal || !window.bootstrap || !/^\d+$/.test(String(id))) return;

    var contenido = document.getElementById('modal-novedad-contenido');
    var estado = document.getElementById('modal-novedad-estado');
    escribir('modal-novedad-categoria', 'Contenido');
    escribir('modal-novedad-titulo', 'Cargando publicación…');
    escribir('modal-novedad-fecha', '');
    if (contenido) contenido.replaceChildren();
    if (estado) estado.textContent = 'Cargando contenido…';
    window.bootstrap.Modal.getOrCreateInstance(modal).show();

    var controlador = new AbortController();
    var temporizador = window.setTimeout(function () { controlador.abort(); }, 9000);
    try {
      var respuesta = await fetch('modulos/home/php/detalle_neuria.php?id=' + encodeURIComponent(id), {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        signal: controlador.signal
      });
      var tipo = respuesta.headers.get('content-type') || '';
      if (!respuesta.ok || tipo.indexOf('application/json') === -1) throw new Error('Respuesta no disponible');
      var datos = await respuesta.json();
      if (!datos.ok || !datos.item) throw new Error('Contenido no disponible');

      escribir('modal-novedad-categoria', datos.item.tipo);
      escribir('modal-novedad-titulo', datos.item.titulo);
      escribir('modal-novedad-fecha', datos.item.fecha);
      if (estado) estado.textContent = '';
      if (contenido) {
        var plantilla = document.createElement('template');
        plantilla.innerHTML = datos.item.contenido_html || '<p>Esta publicación no tiene contenido adicional.</p>';
        contenido.replaceChildren(plantilla.content.cloneNode(true));
      }
    } catch (error) {
      escribir('modal-novedad-titulo', 'Contenido no disponible');
      if (estado) estado.textContent = 'No pudimos cargar esta publicación en este momento. Intentá nuevamente más tarde.';
    } finally {
      window.clearTimeout(temporizador);
    }
  }

  /** Carga una propuesta usando solo textContent para evitar HTML no confiable. */
  function abrirIdea(id) {
    var item = buscarPorId(leerDatos('fe-datos-ideas'), id);
    var modal = document.getElementById('modal-idea');
    if (!item || !modal || !window.bootstrap) return;
    escribir('modal-idea-nivel', item.nivel);
    escribir('modal-idea-titulo', item.titulo);
    escribir('modal-idea-objetivo', item.objetivo);
    escribir('modal-idea-destinatarios', item.destinatarios);
    escribir('modal-idea-materiales', item.materiales);
    escribir('modal-idea-docente', item.docente);
    escribir('modal-idea-ia', item.uso_ia);
    escribir('modal-idea-evaluacion', item.evaluacion);
    escribir('modal-idea-cuidados', item.cuidados);

    var lista = document.getElementById('modal-idea-pasos');
    if (lista) {
      lista.replaceChildren();
      item.pasos.forEach(function (paso) {
        var li = document.createElement('li');
        li.textContent = paso;
        lista.appendChild(li);
      });
    }
    window.bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  /** Filtra tarjetas existentes sin nuevas peticiones al servidor. */
  function filtrarNovedades(boton) {
    var filtro = boton.getAttribute('data-filtro');
    var tarjetas = document.querySelectorAll('.js-novedad');
    var visibles = 0;

    document.querySelectorAll('.fe-filter').forEach(function (item) {
      var activo = item === boton;
      item.classList.toggle('active', activo);
      item.setAttribute('aria-pressed', activo ? 'true' : 'false');
    });

    tarjetas.forEach(function (tarjeta) {
      var mostrar = filtro === 'todas' || tarjeta.getAttribute('data-categoria') === filtro;
      tarjeta.classList.toggle('d-none', !mostrar);
      if (mostrar) visibles += 1;
    });

    var vacio = document.getElementById('sin-novedades');
    if (vacio) vacio.classList.toggle('d-none', visibles > 0);
  }

  // Delegación: continúa funcionando aunque PHPLIB cargue la portada por AJAX.
  document.addEventListener('click', function (evento) {
    var novedad = evento.target.closest('.js-abrir-novedad');
    var idea = evento.target.closest('.js-abrir-idea');
    var filtro = evento.target.closest('.fe-filter');
    var ancla = evento.target.closest('.fe-page a[href^="#"]');

    if (novedad) abrirNovedad(novedad.getAttribute('data-id'));
    if (idea) abrirIdea(idea.getAttribute('data-id'));
    if (filtro) filtrarNovedades(filtro);

    if (ancla) {
      var destino = document.querySelector(ancla.getAttribute('href'));
      if (destino) {
        evento.preventDefault();
        destino.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        var menu = document.getElementById('fe-menu');
        if (menu && menu.classList.contains('show') && window.bootstrap) {
          window.bootstrap.Collapse.getOrCreateInstance(menu).hide();
        }
      }
    }
  });
}());
