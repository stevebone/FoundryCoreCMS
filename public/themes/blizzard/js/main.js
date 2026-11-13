// Blizzard theme scripts

// Header: transparent over hero -> solid once scrolled
(function () {
    const header = document.getElementById('siteHeader');
    if (!header) return;
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

// Header dropdowns (language + account): hover opens via CSS; click toggles
// .open for touch devices and keyboard users. Outside click / Escape closes.
(function () {
    const closeAll = () => document
        .querySelectorAll('.dropdown.open')
        .forEach(d => d.classList.remove('open'));

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.dropdown-btn');
        if (!btn) { closeAll(); return; }
        const dd = btn.closest('.dropdown');
        const wasOpen = dd.classList.contains('open');
        closeAll();
        dd.classList.toggle('open', !wasOpen);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });
})();

// Admin forms: a checkbox with data-toggle="#target" shows/hides the target
document.addEventListener('change', function (e) {
    const el = e.target.closest('[data-toggle]');
    if (!el) return;
    const target = document.querySelector(el.dataset.toggle);
    if (target) target.classList.toggle('is-hidden', !el.checked);
});

// Armory: realm select reloads the character list via the API
(function () {
    const realmSel = document.getElementById('armoryRealm');
    const charSel = document.getElementById('armoryChar');
    if (!realmSel || !charSel) return;

    realmSel.addEventListener('change', function () {
        const selected = charSel.value;
        fetch('/api/armory/characters?realm=' + encodeURIComponent(realmSel.value))
            .then(r => r.json())
            .then(d => {
                const chars = (d.data && d.data.characters) || [];
                const first = charSel.options[0];
                charSel.innerHTML = '';
                charSel.appendChild(first);
                chars.forEach(c => {
                    const o = document.createElement('option');
                    o.value = c.guid;
                    o.textContent = c.name + ' — ' + c.level + ' ' + c.class;
                    if (String(c.guid) === selected) o.selected = true;
                    charSel.appendChild(o);
                });
            })
            .catch(() => {});
    });
})();

// Realm status widget
(function () {
    const box = document.getElementById('realmStatus');
    if (!box) return;

    Promise.all([
        fetch('/api/realm/status').then(r => r.json()),
        fetch('/api/server/online').then(r => r.json()).catch(() => null)
    ]).then(([status, players]) => {
        const realms = status.data || [];
        if (!realms.length) {
            box.innerHTML = '<span class="muted">-</span>';
            return;
        }
        const count = players && players.data ? players.data.online : null;
        box.innerHTML = realms.map(r => {
            const cls = r.online ? 'realm-item online' : 'realm-item';
            const label = r.online ? 'Online' : 'Offline';
            const p = count !== null && r.online
                ? '<span class="players">' + count + ' players</span>' : '';
            return '<div class="' + cls + '"><span class="dot"></span>' +
                '<span class="name">' + escapeHtml(r.name) + '</span>' +
                '<span class="players">' + label + '</span>' + p + '</div>';
        }).join('');
    }).catch(() => {
        box.innerHTML = '<span class="muted">-</span>';
    });

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }
})();

// Armory + DB: item tooltips. Hover a [data-item] element -> fetch tooltip
// payload once per entry, render the WoW-style floating box next to cursor.
(function () {
    const page = document.querySelector('.armory-page, .db-page');
    if (!page) return;

    // Paired tooltip: icon box + stats box side by side (warcraftdb style).
    const tip = document.createElement('div');
    tip.className = 'item-tt';
    tip.hidden = true;
    const iconBox = document.createElement('div');
    iconBox.className = 'tt-icon';
    iconBox.hidden = true;
    const statsBox = document.createElement('div');
    statsBox.className = 'item-tooltip';
    tip.appendChild(iconBox);
    tip.appendChild(statsBox);
    document.body.appendChild(tip);

    const cache = {};
    const esc = s => String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    function sellText(c) {
        if (!c) return '';
        const g = Math.floor(c / 10000), s = Math.floor(c % 10000 / 100), cc = c % 100;
        const parts = [];
        if (g) parts.push(g + 'g');
        if (s) parts.push(s + 's');
        if (cc || !parts.length) parts.push(cc + 'c');
        return parts.join(' ');
    }

    const L = window.AR_TT || {};

    const qNames = ['poor','common','uncommon','rare','epic','legendary','artifact','heirloom'];

    function render(it) {
        const q = it.quality >= 0 && it.quality <= 7 ? it.quality : 1;
        if (it.iconUrl) {
            iconBox.className = 'tt-icon q-' + qNames[q];
            iconBox.innerHTML = '<img src="' + esc(it.iconUrl) + '" alt="">';
            iconBox.hidden = false;
        } else {
            iconBox.hidden = true;
        }
        let h = '<div class="tt-name q-' + qNames[q] + '">' + esc(it.name) + '</div>';
        if (it.ilvl) h += '<div class="tt-ilvl">' + esc(L.itemLevel || 'Item Level') + ' ' + it.ilvl + '</div>';
        if (it.bonding) h += '<div>' + esc(it.bonding) + '</div>';
        const typeLine = [it.invType, it.subclass].filter(Boolean).map(esc).join(', ');
        if (typeLine) h += '<div>' + typeLine + '</div>';
        if (it.armor) h += '<div>' + it.armor + ' ' + esc(L.armor || 'Armor') + '</div>';
        if (it.damage) {
            h += '<div>' + it.damage.min + ' - ' + it.damage.max + ' ' + esc(L.damage || 'Damage') +
                 ' <span class="tt-muted">(' + esc(L.speed || 'Speed') + ' ' + it.damage.speed + ')</span></div>' +
                 '<div class="tt-muted">(' + it.damage.dps + ' ' + esc(L.dps || 'damage per second') + ')</div>';
        }
        (it.stats || []).forEach(s => {
            h += '<div class="tt-stat">+' + s.value + ' ' + esc(s.name) + '</div>';
        });
        (it.effects || []).forEach(fx => {
            if (!fx.text) return;
            const kind = fx.trigger === 0 ? (L.use || 'Use') : fx.trigger === 1 ? (L.equip || 'Equip') : (L.effect || 'Effect');
            h += '<div class="tt-equip">' + esc(kind) + ': ' + esc(fx.text) + '</div>';
        });
        if (it.requiredLevel) h += '<div>' + esc(L.reqLevel || 'Requires Level') + ' ' + it.requiredLevel + '</div>';
        if (it.allowableClass && it.allowableClass.length) {
            h += '<div>' + esc(L.classes || 'Classes') + ': ' + it.allowableClass.map(esc).join(', ') + '</div>';
        }
        if (it.description) h += '<div class="tt-flavor">"' + esc(it.description) + '"</div>';
        const sp = sellText(it.sellPrice);
        if (sp) h += '<div class="tt-sell">' + esc(L.sell || 'Sell Price') + ': ' + sp + '</div>';
        statsBox.innerHTML = h;
    }

    function move(e) {
        const pad = 14;
        let x = e.clientX + pad, y = e.clientY + pad;
        const r = tip.getBoundingClientRect();
        if (x + r.width > innerWidth - 8) x = e.clientX - r.width - pad;
        if (y + r.height > innerHeight - 8) y = e.clientY - r.height - pad;
        tip.style.left = x + 'px';
        tip.style.top = y + 'px';
    }

    let activeEntry = null;
    let hoverEl = null;
    const hide = () => { tip.hidden = true; activeEntry = null; hoverEl = null; };

    // Last-resort guard: :hover is browser ground truth — if the pointer left
    // the element by any path, the tip dies within this interval.
    setInterval(function () {
        if (!tip.hidden && (!hoverEl || !hoverEl.matches(':hover'))) {
            hide();
        }
    }, 200);

    document.addEventListener('mouseover', function (e) {
        const el = e.target.closest('[data-item]');
        if (!el || !page.contains(el)) {
            // Pointer moved outside any item element — hide unconditionally.
            if (!tip.hidden) hide();
            return;
        }
        const entry = el.dataset.item;
        // Same item, pointer moved between its children — just reposition.
        if (entry === activeEntry && !tip.hidden) { move(e); return; }
        activeEntry = entry;
        hoverEl = el;
        tip.hidden = false;
        move(e);
        const show = d => {
            if (entry !== activeEntry || tip.hidden) return;
            if (d) render(d); else hide();
        };
        if (cache[entry]) { cache[entry].then(show); return; }
        iconBox.hidden = true;
        statsBox.innerHTML = '<div class="tt-muted">' + esc((window.AR_TT || {}).loading || 'Loading...') + '</div>';
        cache[entry] = fetch('/api/armory/item?entry=' + encodeURIComponent(entry))
            .then(r => r.ok ? r.json() : null)
            .then(d => (d && d.data && d.data.item) || null)
            .catch(() => null);
        cache[entry].then(show);
    });

    document.addEventListener('mousemove', function (e) {
        if (tip.hidden) return;
        // Bulletproof hide: hit-test what is ACTUALLY under the cursor —
        // hides no matter which event-target edge case failed to fire.
        const under = document.elementFromPoint(e.clientX, e.clientY);
        const el = under && under.closest ? under.closest('[data-item]') : null;
        if (!el || !page.contains(el) || el.dataset.item !== activeEntry) {
            hide();
            return;
        }
        move(e);
    });
    document.addEventListener('mouseout', function (e) {
        const el = e.target.closest('[data-item]');
        // Stay open when moving between children of the same item link.
        if (el && !(e.relatedTarget && el.contains(e.relatedTarget))) {
            hide();
        }
    });
    ['scroll', 'wheel', 'pointerdown', 'contextmenu'].forEach(ev =>
        document.addEventListener(ev, hide, { passive: true }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
    window.addEventListener('blur', hide);
})();

// Autocomplete for [data-ac] inputs: debounced fetch -> positioned dropdown.
// data-ac = endpoint; data-ac-goto = URL template ("...{id}") to navigate to on pick,
// otherwise the picked value fills the input and submits the form.
(function () {
    let box = null, items = [], sel = -1, acInput = null, timer = null;

    function closeBox() {
        if (box) { box.remove(); box = null; }
        items = []; sel = -1; acInput = null;
    }

    function pick(i) {
        const it = items[i];
        if (it === undefined || !acInput) return;
        const inp = acInput, form = inp.form;
        const gotoT = inp.dataset.acGoto;
        if (gotoT && it.id !== undefined) {
            window.location.href = gotoT.replace('{id}', encodeURIComponent(it.id));
            return;
        }
        inp.value = typeof it === 'string' ? it : (it.name || '');
        closeBox();
        if (form) form.submit();
    }

    function noteRow(text) {
        const row = document.createElement('div');
        row.className = 'db-ac-note';
        row.textContent = text;
        return row;
    }

    // Render the dropdown anchored inside the input's .db-ac wrapper so it
    // always lands directly under the box (falls back to body positioning).
    function showBox(input, fill) {
        if (box) box.remove();
        acInput = input;
        box = document.createElement('div');
        box.className = 'db-ac-list';
        fill(box);
        const wrap = input.closest('.db-ac');
        if (wrap) {
            wrap.appendChild(box);
        } else {
            document.body.appendChild(box);
            const r = input.getBoundingClientRect();
            box.style.left = (r.left + window.scrollX) + 'px';
            box.style.top = (r.bottom + window.scrollY + 2) + 'px';
            box.style.minWidth = r.width + 'px';
        }
    }

    function openBox(input, list) {
        items = list; sel = -1;
        showBox(input, b => {
            list.forEach((it, i) => {
                const row = document.createElement('div');
                row.className = 'db-ac-item';
                row.textContent = typeof it === 'string' ? it : (it.name + (it.id ? '  (#' + it.id + ')' : ''));
                row.addEventListener('pointerdown', e => { e.preventDefault(); pick(i); });
                b.appendChild(row);
            });
        });
    }

    function trigger(inp) {
        clearTimeout(timer);
        const q = inp.value.trim();
        if (q.length < 1) { closeBox(); return; }
        showBox(inp, b => b.appendChild(noteRow('Searching…')));
        timer = setTimeout(async () => {
            try {
                const r = await fetch(inp.dataset.ac + '?q=' + encodeURIComponent(q));
                const j = r.ok ? await r.json() : null;
                if (document.activeElement !== inp || !j || !j.data) return;
                if (j.data.length) openBox(inp, j.data);
                else showBox(inp, b => b.appendChild(noteRow('No matches')));
            } catch (_) { /* ignore */ }
        }, 200);
    }

    document.addEventListener('input', function (e) {
        const inp = e.target.closest && e.target.closest('input[data-ac]');
        if (inp) trigger(inp);
    });

    document.addEventListener('focusin', function (e) {
        const inp = e.target.closest && e.target.closest('input[data-ac]');
        if (inp && inp.value.trim().length >= 1) trigger(inp);
    });

    document.addEventListener('keydown', function (e) {
        if (!box || !acInput || e.target !== acInput) return;
        const rows = box.querySelectorAll('.db-ac-item');
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (!items.length) return;
            sel = (sel + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
            rows.forEach((n, i) => n.classList.toggle('sel', i === sel));
        } else if (e.key === 'Enter' && sel >= 0) {
            e.preventDefault();
            pick(sel);
        } else if (e.key === 'Escape') {
            closeBox();
        }
    });

    document.addEventListener('pointerdown', function (e) {
        if (box && !box.contains(e.target) && e.target !== acInput) closeBox();
    });
    window.addEventListener('blur', closeBox);
    document.addEventListener('scroll', function (e) {
        if (box && !(e.target instanceof Node && box.contains(e.target))) closeBox();
    }, true);
})();

// Quest objective map: click the thumbnail -> lightbox with the full map.
document.addEventListener('click', function (e) {
    const canvas = e.target.closest('[data-map-zoom]');
    if (!canvas) return;
    const overlay = document.createElement('div');
    overlay.className = 'db-map-modal';
    overlay.innerHTML = '<div class="db-map-modal-box">'
        + '<button type="button" class="db-map-close" aria-label="Close">&times;</button>'
        + canvas.innerHTML
        + '</div>';
    const close = () => { overlay.remove(); document.removeEventListener('keydown', onKey); };
    const onKey = ev => { if (ev.key === 'Escape') close(); };
    overlay.addEventListener('click', ev => {
        if (ev.target === overlay || ev.target.closest('.db-map-close')) close();
    });
    document.addEventListener('keydown', onKey);
    document.body.appendChild(overlay);
});
document.addEventListener('keydown', function (e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.matches
        && e.target.matches('[data-map-zoom], [data-perks-modal]')) {
        e.preventDefault();
        e.target.click();
    }
});

// Guild perks box: click -> modal with the full perk list (hidden markup in page).
document.addEventListener('click', function (e) {
    const box = e.target.closest('[data-perks-modal]');
    if (!box) return;
    const src = box.parentElement.querySelector('.guild-perks-all');
    if (!src) return;
    const overlay = document.createElement('div');
    overlay.className = 'db-map-modal';
    overlay.innerHTML = '<div class="db-map-modal-box guild-perks-modal">'
        + '<button type="button" class="db-map-close" aria-label="Close">&times;</button>'
        + src.innerHTML
        + '</div>';
    const close = () => { overlay.remove(); document.removeEventListener('keydown', onKey); };
    const onKey = ev => { if (ev.key === 'Escape') close(); };
    overlay.addEventListener('click', ev => {
        if (ev.target === overlay || ev.target.closest('.db-map-close')) close();
    });
    document.addEventListener('keydown', onKey);
    document.body.appendChild(overlay);
});

// DB pages: "+ Add filter" style buttons toggle a panel via data-panel="#id"
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-panel]');
    if (!btn) return;
    const target = document.querySelector(btn.dataset.panel);
    if (target) target.classList.toggle('is-hidden');
});

// DB items: Columns menu checkboxes toggle table cells via data-colcell.
document.addEventListener('change', function (e) {
    const cb = e.target.closest('[data-col]');
    if (!cb) return;
    document.querySelectorAll('[data-colcell="' + cb.dataset.col + '"]')
        .forEach(cell => { cell.hidden = !cb.checked; });
});

// DB item page: relation tabs (Disenchants / Reward From / Dropped By / ...)
document.addEventListener('click', function (e) {
    const tab = e.target.closest('.db-rel-tab[data-rt]');
    if (!tab) return;
    document.querySelectorAll('.db-rel-tab').forEach(t =>
        t.classList.toggle('active', t === tab));
    document.querySelectorAll('[data-rpanel]').forEach(p =>
        p.classList.toggle('is-hidden', p.dataset.rpanel !== tab.dataset.rt));
});

// Admin pickers: a select[data-preview="#img"] shows a live thumbnail of the
// picked file; data-base is the folder URL prefix, data-fallback the default.
document.addEventListener('change', function (e) {
    const sel = e.target.closest && e.target.closest('select[data-preview]');
    if (!sel) return;
    const img = document.querySelector(sel.dataset.preview);
    if (!img) return;
    img.src = sel.value ? sel.dataset.base + sel.value : sel.dataset.fallback;
});

// News editor: markdown toolbar, live preview, image upload.
(function () {
    const form = document.querySelector('[data-md-editor]');
    if (!form) return;
    const body = form.querySelector('#newsBody');
    const preview = form.querySelector('#mdPreview');
    const file = form.querySelector('#mdFile');
    const cover = form.querySelector('#newsCover');
    let uploadFor = 'body';

    const insert = (before, after) => {
        const s = body.selectionStart, e2 = body.selectionEnd;
        const sel = body.value.slice(s, e2);
        body.setRangeText(before + sel + after, s, e2, 'end');
        body.focus();
        body.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const ops = {
        bold: () => insert('**', '**'), italic: () => insert('*', '*'),
        h2: () => insert('## ', ''), h3: () => insert('### ', ''),
        link: () => insert('[', '](https://)'),
        ul: () => insert('- ', ''), ol: () => insert('1. ', ''),
        quote: () => insert('> ', ''), hr: () => insert('\n---\n', ''),
    };

    form.addEventListener('click', function (e) {
        const tool = e.target.closest('[data-md]');
        if (tool) { ops[tool.dataset.md] && ops[tool.dataset.md](); return; }
        const up = e.target.closest('[data-md-upload]');
        if (up) { uploadFor = up.dataset.mdUpload; file.value = ''; file.click(); }
    });

    let timer = null;
    const renderPreview = () => {
        const fd = new FormData();
        fd.append('body', body.value);
        fetch('/api/news/preview', { method: 'POST', body: fd })
            .then(r => r.ok ? r.json() : null)
            .then(j => { if (j && j.data) preview.innerHTML = j.data.html; })
            .catch(() => {});
    };
    body.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(renderPreview, 400);
    });
    renderPreview();

    file.addEventListener('change', function () {
        if (!file.files.length) return;
        const fd = new FormData();
        fd.append('image', file.files[0]);
        fetch('/api/news/image', { method: 'POST', body: fd })
            .then(r => r.ok ? r.json() : null)
            .then(j => {
                const url = j && j.data && j.data.url;
                if (!url) return;
                if (uploadFor === 'cover') {
                    cover.value = url;
                } else {
                    body.setRangeText('![image](' + url + ')',
                        body.selectionStart, body.selectionEnd, 'end');
                    body.dispatchEvent(new Event('input', { bubbles: true }));
                }
            })
            .catch(() => {});
    });
})();

// DB item page: Copy Link button
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    const done = () => {
        const orig = btn.textContent;
        btn.textContent = btn.dataset.copied || 'Copied!';
        setTimeout(() => { btn.textContent = orig; }, 1500);
    };
    if (navigator.clipboard) {
        navigator.clipboard.writeText(location.href).then(done).catch(done);
    } else {
        done();
    }
});

// Sortable tables + client-side row filter (guild roster etc.)
// th[data-key] sorts by that column's td[data-v]; input[data-filter] hides
// non-matching rows of the target tbody.
(function () {
    const norm = v => (v || '').toLowerCase();
    const isNum = v => v !== '' && !isNaN(v);

    document.querySelectorAll('table[data-sortable]').forEach(table => {
        const tbody = table.tBodies[0];
        if (!tbody) return;
        table.querySelectorAll('th[data-key]').forEach(th => {
            th.addEventListener('click', () => {
                const col = +th.dataset.key;
                const asc = !th.classList.contains('sorted-asc');
                table.querySelectorAll('th[data-key]').forEach(h =>
                    h.classList.remove('sorted-asc', 'sorted-desc'));
                th.classList.add(asc ? 'sorted-asc' : 'sorted-desc');
                [...tbody.rows].sort((a, b) => {
                    const av = a.cells[col] ? a.cells[col].dataset.v ?? a.cells[col].textContent : '';
                    const bv = b.cells[col] ? b.cells[col].dataset.v ?? b.cells[col].textContent : '';
                    const cmp = isNum(av) && isNum(bv)
                        ? +av - +bv
                        : norm(av).localeCompare(norm(bv));
                    return asc ? cmp : -cmp;
                }).forEach(r => tbody.appendChild(r));
            });
        });
    });

    document.querySelectorAll('input[data-filter]').forEach(input => {
        const tbody = document.querySelector(input.dataset.filter);
        if (!tbody) return;
        input.addEventListener('input', () => {
            const q = norm(input.value.trim());
            [...tbody.rows].forEach(r => {
                r.hidden = q !== '' && !norm(r.textContent).includes(q);
            });
        });
    });
})();

// Simple tab bars: button[data-tab] toggles sibling .guild-tabpanel targets.
(function () {
    document.querySelectorAll('[data-tab]').forEach(btn => {
        btn.addEventListener('click', () => {
            const scope = btn.closest('.guild-tabs');
            if (!scope) return;
            scope.querySelectorAll('[data-tab]').forEach(b =>
                b.classList.toggle('active', b === btn));
            scope.parentElement.querySelectorAll('.guild-tabpanel').forEach(p =>
                p.classList.toggle('is-hidden', ('#' + p.id) !== btn.dataset.tab));
        });
    });
})();
