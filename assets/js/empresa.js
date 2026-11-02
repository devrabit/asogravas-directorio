(function () {
	'use strict';

	function initGaleria(root) {
		var img = root.querySelector('[data-asdir-galeria-img]');
		var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-asdir-galeria-thumb]'));
		var contador = document.querySelector('[data-asdir-galeria-contador]');
		var prev = root.querySelector('[data-asdir-galeria-prev]');
		var next = root.querySelector('[data-asdir-galeria-next]');

		if (!img || thumbs.length < 2) {
			return;
		}

		var index = 0;

		function ir(i) {
			index = (i + thumbs.length) % thumbs.length;
			var thumb = thumbs[index];
			img.src = thumb.getAttribute('data-src');
			img.alt = thumb.getAttribute('data-alt') || '';

			thumbs.forEach(function (btn, n) {
				var activa = n === index;
				btn.classList.toggle('is-activa', activa);
				btn.setAttribute('aria-current', activa ? 'true' : 'false');
			});

			if (contador) {
				contador.textContent = (index + 1) + ' / ' + thumbs.length;
			}
		}

		thumbs.forEach(function (btn, n) {
			btn.addEventListener('click', function () {
				ir(n);
			});
		});

		if (prev) {
			prev.addEventListener('click', function () {
				ir(index - 1);
			});
		}

		if (next) {
			next.addEventListener('click', function () {
				ir(index + 1);
			});
		}

		root.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				ir(index - 1);
			}
			if (event.key === 'ArrowRight') {
				event.preventDefault();
				ir(index + 1);
			}
		});
	}

	function initVideo(fachada) {
		var boton = fachada.querySelector('.asdir-ficha-video__play');
		var id = fachada.getAttribute('data-youtube-id');

		if (!boton || !id) {
			return;
		}

		boton.addEventListener('click', function () {
			var iframe = document.createElement('iframe');
			iframe.src =
				'https://www.youtube-nocookie.com/embed/' +
				encodeURIComponent(id) +
				'?autoplay=1&rel=0';
			iframe.title = boton.getAttribute('aria-label') || 'YouTube';
			iframe.allow =
				'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
			iframe.allowFullscreen = true;
			iframe.setAttribute('frameborder', '0');
			iframe.className = 'asdir-ficha-video__iframe';

			fachada.innerHTML = '';
			fachada.appendChild(iframe);
			fachada.classList.add('is-reproduciendo');
		});
	}

	function initCotizacion(form) {
		var aviso = form.parentElement.querySelector('[data-asdir-cotizacion-ok]');

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			if (!form.checkValidity()) {
				form.reportValidity();
				return;
			}

			form.hidden = true;

			if (aviso) {
				aviso.hidden = false;
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-asdir-galeria]').forEach(initGaleria);
		document.querySelectorAll('[data-asdir-video]').forEach(initVideo);
		document.querySelectorAll('[data-asdir-cotizacion]').forEach(initCotizacion);
	});
})();
