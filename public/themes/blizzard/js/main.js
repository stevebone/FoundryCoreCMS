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

// Armory: item tooltips. Hover an [data-item] name -> fetch tooltip payload
// once per entry, render the WoW-style floating box next to the cursor.
(function () {
    const page = document.querySelector('.armory-page');
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

    document.addEventListener('mouseover', function (e) {
        const el = e.target.closest('[data-item]');
        if (!el || !page.contains(el)) return;
        const entry = el.dataset.item;
        tip.hidden = false;
        move(e);
        if (cache[entry]) { cache[entry].then(d => { if (d) render(d); }); return; }
        iconBox.hidden = true;
        statsBox.innerHTML = '<div class="tt-muted">' + esc((window.AR_TT || {}).loading || 'Loading...') + '</div>';
        cache[entry] = fetch('/api/armory/item?entry=' + encodeURIComponent(entry))
            .then(r => r.ok ? r.json() : null)
            .then(d => (d && d.data && d.data.item) || null)
            .catch(() => null);
        cache[entry].then(d => { if (d && !tip.hidden) render(d); });
    });

    document.addEventListener('mousemove', e => { if (!tip.hidden) move(e); });
    document.addEventListener('mouseout', function (e) {
        const el = e.target.closest('[data-item]');
        // Stay open when moving between children of the same item link.
        if (el && !(e.relatedTarget && el.contains(e.relatedTarget))) tip.hidden = true;
    });
    document.addEventListener('scroll', () => { tip.hidden = true; }, { passive: true });
})();
