/**
 * CASES theme — front-end behaviour (vanilla JS, no dependencies).
 *  1. EN/VI language toggle (content carries data-en / data-vi)
 *  2. Header glass effect + mobile menu
 *  3. Scroll reveal
 *  4. Contact slide-over panel + AJAX form
 *  5. Products page: category panels + SKU lightbox
 */
(function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;
	var lang = 'en';

	function store(key, val) {
		try {
			if (val === undefined) return localStorage.getItem(key);
			localStorage.setItem(key, val);
		} catch (e) {}
		return null;
	}

	/* ---------------------------------------------------------------- 1. Language */
	var BTN_ON = 'text-sky-400 font-bold hover:opacity-80 transition-all text-xs sm:text-sm';
	var BTN_OFF = 'text-white/60 hover:text-sky-400 transition-colors text-xs sm:text-sm';

	function setLang(next, persist) {
		var changed = next !== lang;
		lang = next;
		if (persist) store('cases_lang', next);
		root.removeAttribute('data-lang'); // release the anti-flash rule
		root.setAttribute('lang', next === 'vi' ? 'vi' : 'en');

		doc.querySelectorAll('[data-lang-en]').forEach(function (b) {
			b.className = next === 'en' ? BTN_ON : BTN_OFF;
			b.setAttribute('aria-pressed', next === 'en' ? 'true' : 'false');
		});
		doc.querySelectorAll('[data-lang-vi]').forEach(function (b) {
			b.className = next === 'vi' ? BTN_ON : BTN_OFF;
			b.setAttribute('aria-pressed', next === 'vi' ? 'true' : 'false');
		});

		if (changed || next === 'vi') {
			doc.querySelectorAll('[data-vi]').forEach(function (el) {
				var text = next === 'vi' ? el.getAttribute('data-vi') : el.getAttribute('data-en');
				if (text !== null) el.innerHTML = text;
			});
		}
		doc.dispatchEvent(new CustomEvent('cases:lang', { detail: next }));
	}

	function initLang() {
		var saved = store('cases_lang') === 'vi' ? 'vi' : 'en';
		lang = 'en'; // server output is English
		setLang(saved, false);
		doc.querySelectorAll('[data-lang-en]').forEach(function (b) {
			b.addEventListener('click', function () { setLang('en', true); });
		});
		doc.querySelectorAll('[data-lang-vi]').forEach(function (b) {
			b.addEventListener('click', function () { setLang('vi', true); });
		});
	}

	function tr(en, vi) { return lang === 'vi' ? vi : en; }

	/* ---------------------------------------------------------------- 2. Header */
	function initHeader() {
		var header = doc.getElementById('site-header');
		if (header) {
			var onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 40); };
			window.addEventListener('scroll', onScroll, { passive: true });
			onScroll();
		}
		var burger = doc.getElementById('hamburger-icon');
		var menu = doc.getElementById('mobile-menu');
		if (burger && menu) {
			var setOpen = function (open) {
				menu.classList.toggle('hidden', !open);
				burger.setAttribute('aria-expanded', open ? 'true' : 'false');
			};
			burger.addEventListener('click', function () { setOpen(menu.classList.contains('hidden')); });
			menu.addEventListener('click', function (e) { if (e.target.closest('a')) setOpen(false); });
			window.addEventListener('resize', function () { if (window.innerWidth >= 768) setOpen(false); });
		}
	}

	/* ---------------------------------------------------------------- 3. Reveal */
	function initReveal() {
		var els = doc.querySelectorAll('.reveal');
		if (!els.length) return;
		if (!('IntersectionObserver' in window)) {
			els.forEach(function (el) { el.classList.add('revealed'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('revealed');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.08 });
		els.forEach(function (el) { io.observe(el); });
	}

	/* ---------------------------------------------------------------- 4. Contact */
	function initContact() {
		var overlay = doc.getElementById('contact-overlay');
		var panel = doc.getElementById('contact-panel');
		if (!overlay || !panel) return;
		var form = doc.getElementById('contact-form');
		var errBox = doc.getElementById('contact-error');
		var okBox = doc.getElementById('contact-success');
		var submit = doc.getElementById('contact-submit');
		var opener = null;

		function open(trigger) {
			opener = trigger || null;
			overlay.hidden = false;
			panel.setAttribute('aria-hidden', 'false');
			doc.body.style.overflow = 'hidden';
			void panel.offsetWidth; // let the transition start from the closed state
			overlay.classList.add('is-open');
			panel.classList.add('is-open');
			var first = panel.querySelector('input[name="name"]');
			if (first) first.focus({ preventScroll: true });
		}

		function close() {
			overlay.classList.remove('is-open');
			panel.classList.remove('is-open');
			panel.setAttribute('aria-hidden', 'true');
			setTimeout(function () {
				if (!panel.classList.contains('is-open')) {
					overlay.hidden = true;
					doc.body.style.overflow = '';
				}
			}, 400);
			if (opener && opener.focus) opener.focus();
		}

		doc.addEventListener('click', function (e) {
			// Buttons with [data-open-contact], plus plain #contact links written inside article bodies.
			var o = e.target.closest('[data-open-contact], .entry-content a[href="#contact"]');
			if (o) { e.preventDefault(); open(o); return; }
			if (e.target.closest('[data-close-contact]') || e.target === overlay) close();
		});

		doc.addEventListener('keydown', function (e) {
			if (!panel.classList.contains('is-open')) return;
			if (e.key === 'Escape') { close(); return; }
			if (e.key === 'Tab') { // keep focus inside the dialog
				var f = panel.querySelectorAll('a[href],button:not([disabled]),input:not([tabindex="-1"]),textarea');
				if (!f.length) return;
				var first = f[0], last = f[f.length - 1];
				if (e.shiftKey && doc.activeElement === first) { e.preventDefault(); last.focus(); }
				else if (!e.shiftKey && doc.activeElement === last) { e.preventDefault(); first.focus(); }
			}
		});

		function showError(en, vi) {
			errBox.textContent = tr(en, vi);
			errBox.hidden = false;
		}

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				errBox.hidden = true;
				var data = new FormData(form);
				var name = String(data.get('name') || '').trim();
				var email = String(data.get('email') || '').trim();
				var message = String(data.get('message') || '').trim();
				if (!name || !message || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
					showError('Please enter your name, a valid email and a message.', 'Vui lòng nhập họ tên, email hợp lệ và nội dung.');
					return;
				}
				data.append('action', 'cases_contact');
				data.append('nonce', window.CASES ? window.CASES.nonce : '');
				submit.disabled = true;
				submit.classList.add('is-loading');

				fetch(window.CASES.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (res && res.success) {
							form.reset();
							submit.hidden = true;
							okBox.hidden = false;
							return;
						}
						var code = res && res.data && res.data.message;
						if (code === 'rate_limited') showError('Too many messages. Please try again later.', 'Bạn gửi quá nhiều tin nhắn. Vui lòng thử lại sau.');
						else showError('Could not send your message. Please try again or email us directly.', 'Không thể gửi tin nhắn. Vui lòng thử lại hoặc gửi email trực tiếp.');
					})
					.catch(function () {
						showError('Network error. Please try again.', 'Lỗi mạng. Vui lòng thử lại.');
					})
					.finally(function () {
						submit.disabled = false;
						submit.classList.remove('is-loading');
					});
			});
		}
	}

	/* ---------------------------------------------------------------- 5. Products */
	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function initProducts() {
		var data = window.CASES_PRODUCTS;
		var lb = doc.getElementById('lightbox');
		if (!data || !lb) return;

		var openId = null;
		var lbCat = null;
		var lbIdx = 0;
		var lbOpener = null;

		/* ---- category panels ---- */
		function resetCards() {
			doc.querySelectorAll('.expand-icon').forEach(function (i) { i.style.transform = ''; });
			doc.querySelectorAll('.expand-hint').forEach(function (h) { h.textContent = tr('View products', 'Xem sản phẩm'); });
			doc.querySelectorAll('.product-category-card').forEach(function (c) { c.setAttribute('aria-expanded', 'false'); });
			doc.querySelectorAll('.expand-panel').forEach(function (p) { p.hidden = true; p.innerHTML = ''; });
		}

		function closePanel() {
			resetCards();
			openId = null;
		}

		function skuTile(sku, i) {
			var media = sku.image
				? '<img src="' + esc(sku.image) + '" alt="' + esc(sku.name) + '" loading="lazy" class="w-full h-28 object-cover rounded-xl group-hover:scale-105 transition-all duration-300">'
				: '<div class="w-full h-28 bg-slate-50 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center group-hover:border-sky-400 group-hover:bg-sky-50 group-hover:scale-105 transition-all duration-300"><span class="material-symbols-outlined text-slate-300 text-3xl group-hover:text-sky-400 transition-colors" aria-hidden="true">image</span></div>';
			return '<button type="button" class="sku-slot group cursor-pointer text-left" data-sku="' + i + '">' + media +
				'<p class="mt-2 text-center font-headline font-bold text-slate-700 text-xs uppercase leading-snug px-1">' + esc(sku.name) + '</p></button>';
		}

		function openPanel(id, row) {
			var group = data[id];
			var panel = doc.getElementById('panel-row-' + row);
			if (!group || !panel) return;
			resetCards();
			panel.innerHTML =
				'<div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">' +
				'<h3 class="font-headline font-black text-slate-900 uppercase text-xl">' + esc(group.name) +
				' <span class="text-sky-600 text-sm font-semibold normal-case">— ' + group.skus.length + ' SKUs</span></h3>' +
				'<button type="button" data-panel-close class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 transition-colors" aria-label="Close"><span class="material-symbols-outlined text-slate-500" aria-hidden="true">close</span></button></div>' +
				'<div class="p-6"><div class="grid ' + esc(group.cols) + ' gap-4">' + group.skus.map(skuTile).join('') + '</div></div>';
			panel.hidden = false;
			panel.style.animation = 'none';
			void panel.offsetHeight;
			panel.style.animation = '';

			var card = doc.getElementById('card-' + id);
			if (card) {
				card.setAttribute('aria-expanded', 'true');
				var icon = card.querySelector('.expand-icon');
				var hint = card.querySelector('.expand-hint');
				if (icon) icon.style.transform = 'rotate(180deg)';
				if (hint) hint.textContent = tr('Collapse', 'Thu gọn');
			}
			openId = id;
			setTimeout(function () { panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 60);
		}

		function toggleCard(card) {
			var id = card.getAttribute('data-cat');
			if (openId === id) closePanel();
			else openPanel(id, card.getAttribute('data-row'));
		}

		doc.addEventListener('click', function (e) {
			var card = e.target.closest('.product-category-card');
			if (card) { toggleCard(card); return; }
			if (e.target.closest('[data-panel-close]')) { closePanel(); return; }
			var slot = e.target.closest('.sku-slot');
			if (slot && openId) openLb(openId, parseInt(slot.getAttribute('data-sku'), 10), slot);
		});
		doc.addEventListener('keydown', function (e) {
			var card = e.target.closest && e.target.closest('.product-category-card');
			if (card && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); toggleCard(card); }
		});

		/* ---- lightbox ---- */
		function setText(id, text) { var el = doc.getElementById(id); if (el) el.textContent = text; }

		function renderLb() {
			var group = data[lbCat];
			var sku = group.skus[lbIdx];
			var img = doc.getElementById('lb-img');
			var emoji = doc.getElementById('lb-emoji');
			if (sku.image) {
				img.src = sku.image;
				img.alt = sku.name;
				img.classList.remove('hidden');
				emoji.style.display = 'none';
			} else {
				img.classList.add('hidden');
				img.removeAttribute('src');
				emoji.style.display = '';
				emoji.textContent = group.emoji || '🦐';
			}
			setText('lb-sku-name', sku.name);
			setText('lb-tag', (group.emoji || '') + ' ' + (lang === 'vi' && group.vi ? group.vi : group.name));
			setText('lb-counter', (lbIdx + 1) + ' / ' + group.skus.length);
			setText('lb-desc', lang === 'vi' && sku.descVi ? sku.descVi : sku.desc);
			setText('lb-spec-size', sku.size || tr('As required', 'Theo yêu cầu'));
			setText('lb-spec-pack', sku.pack || '—');
			setText('lb-spec-origin', sku.origin || '—');
			setText('lb-spec-glaze', sku.glaze || '—');

			var dots = doc.getElementById('lb-dots');
			dots.innerHTML = '';
			group.skus.forEach(function (_, i) {
				var d = doc.createElement('button');
				d.type = 'button';
				d.className = 'lb-dot' + (i === lbIdx ? ' active' : '');
				d.setAttribute('aria-label', String(i + 1));
				d.addEventListener('click', function () { lbIdx = i; renderLb(); });
				dots.appendChild(d);
			});
		}

		function openLb(cat, idx, trigger) {
			lbCat = cat;
			lbIdx = idx;
			lbOpener = trigger || null;
			renderLb();
			lb.classList.add('active');
			doc.body.style.overflow = 'hidden';
			var close = lb.querySelector('[data-lb-close]');
			if (close) close.focus({ preventScroll: true });
		}

		function closeLb() {
			lb.classList.remove('active');
			doc.body.style.overflow = '';
			if (lbOpener && lbOpener.focus) lbOpener.focus();
		}

		function navLb(d) {
			var len = data[lbCat].skus.length;
			lbIdx = (lbIdx + d + len) % len;
			renderLb();
		}

		lb.addEventListener('click', function (e) {
			if (e.target === lb || e.target.closest('[data-lb-close]')) { closeLb(); return; }
			var nav = e.target.closest('[data-lb-nav]');
			if (nav) navLb(parseInt(nav.getAttribute('data-lb-nav'), 10));
		});
		doc.addEventListener('keydown', function (e) {
			if (!lb.classList.contains('active')) return;
			if (e.key === 'ArrowLeft') navLb(-1);
			else if (e.key === 'ArrowRight') navLb(1);
			else if (e.key === 'Escape') closeLb();
		});

		// Re-render open UI when the language changes.
		doc.addEventListener('cases:lang', function () {
			var id = openId;
			if (id) {
				var card = doc.getElementById('card-' + id);
				var hint = card && card.querySelector('.expand-hint');
				if (hint) hint.textContent = tr('Collapse', 'Thu gọn');
			}
			doc.querySelectorAll('.product-category-card').forEach(function (c) {
				if (c.getAttribute('data-cat') !== id) {
					var h = c.querySelector('.expand-hint');
					if (h) h.textContent = tr('View products', 'Xem sản phẩm');
				}
			});
			if (lb.classList.contains('active')) renderLb();
		});
	}

	/* ---------------------------------------------------------------- boot */
	function boot() {
		initHeader();
		initReveal();
		initContact();
		initProducts();
		initLang(); // last: other modules must already listen for "cases:lang"
	}
	if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
