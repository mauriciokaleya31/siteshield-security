/**
 * SiteShield Security — Motor Global de Proteção Anti-Clone e Anti-Inspeção
 * Proteção universal em tempo real para todas as páginas do frontend do WordPress.
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 */

(function() {
	'use strict';

	var config = window.SiteShieldAntiCloneConfig || {
		enabled: true,
		blockRightClick: true,
		blockDevTools: true,
		blockCopySelect: true,
		blockDragImages: true,
		blockPrint: false,
		enableFrameBusting: true
	};

	if (!config.enabled) {
		return;
	}

	/**
	 * Verifica se o elemento alvo é um campo de entrada de formulário editável
	 */
	function isEditable(el) {
		if (!el) return false;
		var tag = el.tagName ? el.tagName.toLowerCase() : '';
		if (tag === 'input' || tag === 'textarea' || tag === 'select' || tag === 'option') {
			return true;
		}
		if (el.isContentEditable || el.getAttribute('contenteditable') === 'true') {
			return true;
		}
		return false;
	}

	// 1. BLOQUEIO GLOBAL DE CLIQUE COM BOTÃO DIREITO (Context Menu)
	if (config.blockRightClick) {
		window.addEventListener('contextmenu', function(e) {
			if (!isEditable(e.target)) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}, true);

		document.addEventListener('contextmenu', function(e) {
			if (!isEditable(e.target)) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}, true);
	}

	// 2. BLOQUEIO GLOBAL DE ATALHOS DE TECLADO (F12, DevTools, Ctrl+U, Ctrl+C, etc.)
	window.addEventListener('keydown', function(e) {
		var key = e.key || '';
		var keyCode = e.keyCode || e.which || 0;
		var ctrlOrMeta = e.ctrlKey || e.metaKey;
		var shift = e.shiftKey;
		var alt = e.altKey;

		if (config.blockDevTools) {
			// F12
			if (keyCode === 123 || key === 'F12') {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+Shift+I / Cmd+Option+I (Inspecionar Elemento / DevTools)
			if ((ctrlOrMeta && shift && (keyCode === 73 || key === 'I' || key === 'i')) || (e.metaKey && alt && (keyCode === 73 || key === 'I' || key === 'i'))) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+Shift+J / Cmd+Option+J (Console do Navegador)
			if ((ctrlOrMeta && shift && (keyCode === 74 || key === 'J' || key === 'j')) || (e.metaKey && alt && (keyCode === 74 || key === 'J' || key === 'j'))) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+Shift+C / Cmd+Option+C (Inspecionar com ponteiro)
			if ((ctrlOrMeta && shift && (keyCode === 67 || key === 'C' || key === 'c')) || (e.metaKey && alt && (keyCode === 67 || key === 'C' || key === 'c'))) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+Shift+K (Console Firefox)
			if (ctrlOrMeta && shift && (keyCode === 75 || key === 'K' || key === 'k')) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+Shift+E (Painel de Rede / Network)
			if (ctrlOrMeta && shift && (keyCode === 69 || key === 'E' || key === 'e')) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+U / Cmd+Option+U (Exibir Código-Fonte da Página)
			if ((ctrlOrMeta && (keyCode === 85 || key === 'U' || key === 'u')) || (e.metaKey && alt && (keyCode === 85 || key === 'U' || key === 'u'))) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			// Ctrl+S / Cmd+S (Salvar Página HTML Completa)
			if (ctrlOrMeta && (keyCode === 83 || key === 'S' || key === 's')) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}

		if (config.blockPrint) {
			// Ctrl+P / Cmd+P (Imprimir)
			if (ctrlOrMeta && (keyCode === 80 || key === 'P' || key === 'p')) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}

		if (config.blockCopySelect) {
			// Ctrl+C / Cmd+C (Copiar)
			if (ctrlOrMeta && !shift && (keyCode === 67 || key === 'C' || key === 'c')) {
				if (!isEditable(e.target)) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			}

			// Ctrl+A / Cmd+A (Selecionar Tudo)
			if (ctrlOrMeta && (keyCode === 65 || key === 'A' || key === 'a')) {
				if (!isEditable(e.target)) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			}

			// Ctrl+X / Cmd+X (Recortar)
			if (ctrlOrMeta && (keyCode === 88 || key === 'X' || key === 'x')) {
				if (!isEditable(e.target)) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			}
		}
	}, true);

	// 3. BLOQUEIO DE EVENTOS DE SELEÇÃO E CÓPIA
	if (config.blockCopySelect) {
		document.addEventListener('selectstart', function(e) {
			if (!isEditable(e.target)) {
				e.preventDefault();
				return false;
			}
		}, true);

		document.addEventListener('copy', function(e) {
			if (!isEditable(e.target)) {
				e.preventDefault();
				if (e.clipboardData) {
					e.clipboardData.setData('text/plain', '');
				}
				return false;
			}
		}, true);

		document.addEventListener('cut', function(e) {
			if (!isEditable(e.target)) {
				e.preventDefault();
				return false;
			}
		}, true);
	}

	// 4. BLOQUEIO DE ARRASTO DE IMAGENS E MÍDIAS (Drag & Drop)
	if (config.blockDragImages) {
		window.addEventListener('dragstart', function(e) {
			var tag = e.target && e.target.tagName ? e.target.tagName.toLowerCase() : '';
			if (tag === 'img' || tag === 'picture' || tag === 'video' || tag === 'canvas' || tag === 'svg' || tag === 'a') {
				e.preventDefault();
				return false;
			}
		}, true);
	}

	// 5. ANTI-FRAME BUSTING (Proteção contra Clickjacking e iframes externos)
	if (config.enableFrameBusting) {
		try {
			if (window.top !== window.self) {
				if (window.top.location.hostname !== window.self.location.hostname) {
					window.top.location.href = window.self.location.href;
				}
			}
		} catch (err) {
			try {
				window.top.location.href = window.self.location.href;
			} catch (e) {}
		}
	}

	// 6. PROTEÇÃO DINÂMICA DE CONTEÚDO AJAX / ELEMENTOS NOVOS
	if (window.MutationObserver) {
		var observer = new MutationObserver(function(mutations) {
			if (config.blockDragImages) {
				var imgs = document.querySelectorAll('img, picture, video');
				for (var i = 0; i < imgs.length; i++) {
					imgs[i].setAttribute('draggable', 'false');
					imgs[i].oncontextmenu = function(e) {
						if (!isEditable(e ? e.target : null)) {
							if (e && e.preventDefault) e.preventDefault();
							return false;
						}
					};
				}
			}
		});

		if (document.body) {
			observer.observe(document.body, { childList: true, subtree: true });
		} else {
			document.addEventListener('DOMContentLoaded', function() {
				observer.observe(document.body, { childList: true, subtree: true });
			});
		}
	}
})();
