/* MediCare Admin — Services page
 * Frontend-only interactions. No network requests, no storage, no backend.
 */
(function () {
    'use strict';

    var $ = function (s, c) { return (c || document).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

    var CATEGORY_LABELS = {
        consultation: 'Consultation', diagnostics: 'Diagnostics', treatment: 'Treatment',
        procedures: 'Procedures', preventive: 'Preventive', wellness: 'Wellness'
    };
    var PAGE_SIZE = { list: 10, grid: 9 };

    /* ---------------------------------------------------------
       Helpers
       --------------------------------------------------------- */
    function fmtMoney(n) { return Number(n).toLocaleString('en-US'); }
    function fmtDuration(m) { m = Number(m); return m >= 1440 ? Math.round(m / 60) + ' hrs' : m + ' min'; }

    var toastTimer;
    function toast(msg, type) {
        var el = $('#toast');
        el.className = 'toast' + (type === 'error' ? ' error' : '');
        el.innerHTML = '<i class="fa-solid ' + (type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i><span></span>';
        el.lastChild.textContent = msg;
        el.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.hidden = true; }, 3600);
    }

    /* ---------------------------------------------------------
       Sidebar
       --------------------------------------------------------- */
    var mq = window.matchMedia('(max-width: 1023px)');
    $('#sidebar-toggle').addEventListener('click', function () {
        if (mq.matches) document.body.classList.toggle('sidebar-open');
        else document.body.classList.toggle('sidebar-collapsed');
    });
    $('#sidebar-overlay').addEventListener('click', function () { document.body.classList.remove('sidebar-open'); });
    (mq.addEventListener ? mq.addEventListener.bind(mq, 'change') : mq.addListener.bind(mq))(function () {
        document.body.classList.remove('sidebar-open');
    });

    $$('[data-submenu-toggle]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var li = a.closest('.has-sub');
            var open = li.classList.toggle('open');
            a.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
    $$('.sidebar a[href="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); });
    });

    /* ---------------------------------------------------------
       Generic dropdown menus (bell, user, more, row actions)
       --------------------------------------------------------- */
    function closeMenus(except) {
        $$('.menu').forEach(function (m) {
            if (m === except) return;
            m.hidden = true;
            var btn = m.parentElement && $('[aria-haspopup]', m.parentElement);
            if (btn) btn.setAttribute('aria-expanded', 'false');
        });
    }
    function bindMenu(btnSel, menuSel) {
        var btn = $(btnSel), menu = $(menuSel);
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = menu.hidden;
            closeMenus(menu);
            menu.hidden = !willOpen;
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    }
    bindMenu('#notif-btn', '#notif-menu');
    bindMenu('#user-btn', '#user-menu');
    bindMenu('#more-btn', '#more-menu');
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.menu a[href="#"]')) { /* keep default */ }
        closeMenus();
    });
    $$('.menu a[href="#"]').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); }); });

    /* ---------------------------------------------------------
       Data model (read from the static markup)
       --------------------------------------------------------- */
    var services = $$('#service-rows tr').map(function (row) {
        var d = row.dataset;
        return {
            id: Number(d.id),
            name: d.name, cat: d.cat, fee: Number(d.fee), mins: Number(d.mins),
            status: d.status, desc: d.desc, full: d.full, img: d.img,
            features: d.features ? d.features.split('|') : [],
            removed: false,
            row: row,
            card: $('#service-grid .svc-card[data-id="' + d.id + '"]')
        };
    });
    function byId(id) { return services.filter(function (s) { return s.id === Number(id); })[0]; }

    /* ---------------------------------------------------------
       Filtering, views, pagination
       --------------------------------------------------------- */
    var DEFAULTS = { cat: 'all', q: '', status: 'all', view: 'list', page: 1 };
    var state = { cat: 'all', q: '', status: 'all', view: 'list', page: 1 };

    function matches() {
        var q = state.q.trim().toLowerCase();
        return services.filter(function (s) {
            if (s.removed) return false;
            if (state.cat !== 'all' && s.cat !== state.cat) return false;
            if (state.status !== 'all' && s.status !== state.status) return false;
            if (q && (s.name + ' ' + CATEGORY_LABELS[s.cat] + ' ' + s.desc).toLowerCase().indexOf(q) === -1) return false;
            return true;
        });
    }

    function render() {
        var list = matches();
        var size = PAGE_SIZE[state.view];
        var pages = Math.max(1, Math.ceil(list.length / size));
        if (state.page > pages) state.page = pages;
        var start = (state.page - 1) * size;
        var slice = list.slice(start, start + size);

        services.forEach(function (s) {
            var idx = slice.indexOf(s);
            var show = idx !== -1;
            s.row.hidden = !(show && state.view === 'list');
            s.card.hidden = !(show && state.view === 'grid');
            if (show) $('[data-f="num"]', s.row).textContent = start + idx + 1;
        });

        $('#list-view').hidden = state.view !== 'list' || !list.length;
        $('#grid-view').hidden = state.view !== 'grid' || !list.length;
        $('#empty-state').hidden = list.length > 0;
        $('#page-info').textContent = list.length
            ? 'Showing ' + (start + 1) + ' to ' + (start + slice.length) + ' of ' + list.length + ' services'
            : 'Showing 0 of 0 services';

        renderPager(pages);
        updateStats();
    }

    function renderPager(pages) {
        var box = $('#page-controls');
        box.innerHTML = '';
        if (pages <= 1 && !matches().length) return;
        function btn(html, page, opts) {
            opts = opts || {};
            var b = document.createElement('button');
            b.type = 'button';
            b.innerHTML = html;
            if (opts.label) b.setAttribute('aria-label', opts.label);
            if (opts.active) { b.classList.add('active'); b.setAttribute('aria-current', 'page'); }
            if (opts.disabled) b.disabled = true;
            b.addEventListener('click', function () { state.page = page; render(); });
            box.appendChild(b);
        }
        function gap() { var s = document.createElement('span'); s.className = 'gap'; s.textContent = '…'; box.appendChild(s); }

        btn('<i class="fa-solid fa-chevron-left"></i>', state.page - 1, { label: 'Previous page', disabled: state.page === 1 });
        var first = Math.max(1, Math.min(state.page - 2, pages - 4));
        var last = Math.min(pages, first + 4);
        if (first > 1) { btn('1', 1, { label: 'Page 1' }); if (first > 2) gap(); }
        for (var p = first; p <= last; p++) btn(String(p), p, { active: p === state.page, label: 'Page ' + p });
        if (last < pages) { if (last < pages - 1) gap(); btn(String(pages), pages, { label: 'Page ' + pages }); }
        btn('<i class="fa-solid fa-chevron-right"></i>', state.page + 1, { label: 'Next page', disabled: state.page === pages });
    }

    function updateStats() {
        var live = services.filter(function (s) { return !s.removed; });
        var active = live.filter(function (s) { return s.status === 'active'; }).length;
        $('#stat-total').textContent = live.length;
        $('#stat-active').textContent = active;
        $('#stat-inactive').textContent = live.length - active;
        $('#stat-total-active').textContent = active;
        $('#stat-total-inactive').textContent = live.length - active;
    }

    function syncTabs() {
        var inMore = state.cat === 'preventive' || state.cat === 'wellness';
        $$('#category-tabs .tab[data-cat]').forEach(function (t) {
            var on = t.dataset.cat === state.cat;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        $('#more-btn').classList.toggle('active', inMore);
        $('#more-label').textContent = inMore ? CATEGORY_LABELS[state.cat] : 'More';
        $('#filter-cat').value = state.cat;
    }

    function setCategory(cat) {
        state.cat = cat; state.page = 1;
        syncTabs(); render();
    }

    $$('#category-tabs .tab[data-cat]').forEach(function (t) {
        t.addEventListener('click', function () { setCategory(t.dataset.cat); });
    });
    $$('#more-menu .more-item').forEach(function (i) {
        i.addEventListener('click', function () { setCategory(i.dataset.cat); });
    });
    $('#filter-cat').addEventListener('change', function (e) { setCategory(e.target.value); });
    $('#filter-status').addEventListener('change', function (e) { state.status = e.target.value; state.page = 1; render(); });

    $('#filter-q').addEventListener('input', function (e) {
        state.q = e.target.value; state.page = 1;
        $('#global-search').value = e.target.value;
        render();
    });
    $('#global-search').addEventListener('input', function (e) {
        state.q = e.target.value; state.page = 1;
        $('#filter-q').value = e.target.value;
        render();
    });

    $('#filter-reset').addEventListener('click', function () {
        state.cat = DEFAULTS.cat; state.q = ''; state.status = 'all'; state.page = 1;
        $('#filter-q').value = ''; $('#global-search').value = ''; $('#filter-status').value = 'all';
        syncTabs(); render();
    });

    $$('.view-btn').forEach(function (b) {
        b.addEventListener('click', function () {
            state.view = b.dataset.view; state.page = 1;
            $$('.view-btn').forEach(function (x) {
                var on = x === b;
                x.classList.toggle('active', on);
                x.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            render();
        });
    });

    /* ---------------------------------------------------------
       Row / card actions (event delegation)
       --------------------------------------------------------- */
    function setStatus(s, status) {
        s.status = status;
        s.row.dataset.status = status;
        s.card.dataset.status = status;
        var label = status === 'active' ? 'Active' : 'Inactive';
        [s.row, s.card].forEach(function (el) {
            var pill = $('[data-f="status"]', el);
            pill.textContent = label;
            pill.classList.toggle('pill-active', status === 'active');
            pill.classList.toggle('pill-inactive', status === 'inactive');
        });
        $('input[data-action="toggle-status"]', s.row).checked = status === 'active';
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-action]');
        if (!trigger || trigger.tagName === 'INPUT') return;
        var host = trigger.closest('[data-id]');
        var s = host && byId(host.dataset.id);
        var action = trigger.dataset.action;

        if (action === 'toggle-menu') {
            e.stopPropagation();
            var menu = $('.menu', trigger.parentElement);
            var willOpen = menu.hidden;
            closeMenus(menu);
            menu.hidden = !willOpen;
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            return;
        }
        if (!s) return;
        closeMenus();
        if (action === 'view') openModal(s);
        else if (action === 'edit') editService(s);
        else if (action === 'toggle-status-btn') {
            setStatus(s, s.status === 'active' ? 'inactive' : 'active');
            render();
            toast(s.name + ' is now ' + s.status + '.');
        } else if (action === 'delete') {
            s.removed = true;
            render();
            toast(s.name + ' removed from this view (demo only — nothing was deleted on a server).');
        }
    });

    document.addEventListener('change', function (e) {
        var input = e.target.closest('input[data-action="toggle-status"]');
        if (!input) return;
        var s = byId(input.closest('[data-id]').dataset.id);
        setStatus(s, input.checked ? 'active' : 'inactive');
        render();
        toast(s.name + ' is now ' + s.status + '.');
    });

    /* ---------------------------------------------------------
       View modal
       --------------------------------------------------------- */
    var modal = $('#modal'), modalFor = null, lastFocus = null;

    function openModal(s) {
        modalFor = s; lastFocus = document.activeElement;
        $('#m-img').src = 'assets/images/' + s.img;
        $('#m-img').alt = s.name;
        $('#modal-title').textContent = s.name;
        var cat = $('#m-cat'); cat.textContent = CATEGORY_LABELS[s.cat]; cat.className = 'badge cat-' + s.cat;
        var st = $('#m-status'); st.textContent = s.status === 'active' ? 'Active' : 'Inactive'; st.className = 'pill pill-' + s.status;
        $('#m-fee').textContent = '₹' + fmtMoney(s.fee);
        $('#m-dur').textContent = fmtDuration(s.mins);
        $('#m-id').textContent = '#' + String(s.id).padStart(3, '0');
        $('#m-desc').textContent = s.full || s.desc;
        var ul = $('#m-features'); ul.innerHTML = '';
        s.features.forEach(function (f) { var li = document.createElement('li'); li.textContent = f; ul.appendChild(li); });
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        $('.modal-close', modal).focus();
    }
    function closeModal() {
        if (modal.hidden) return;
        modal.hidden = true;
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    $$('[data-close]', modal).forEach(function (el) { el.addEventListener('click', closeModal); });
    $('#m-edit').addEventListener('click', function () { var s = modalFor; closeModal(); if (s) editService(s); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal(); closeMenus();
            document.body.classList.remove('sidebar-open');
        }
        if (e.key === 'Tab' && !modal.hidden) {
            var f = $$('button, [href], input, select, textarea', modal).filter(function (x) { return !x.disabled && x.offsetParent !== null; });
            if (!f.length) return;
            var first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    /* ---------------------------------------------------------
       Add / Edit form
       --------------------------------------------------------- */
    var form = $('#service-form');
    var editing = null;
    var tags = [];
    var DEFAULT_TAGS = ['Experienced Doctors', 'Quick Consultation'];

    function renderTags() {
        var ul = $('#tag-list'); ul.innerHTML = '';
        tags.forEach(function (t, i) {
            var li = document.createElement('li');
            var span = document.createElement('span'); span.textContent = t;
            var b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Remove ' + t);
            b.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            b.addEventListener('click', function () { tags.splice(i, 1); renderTags(); });
            li.appendChild(span); li.appendChild(b); ul.appendChild(li);
        });
    }
    function addTag(value) {
        value = value.trim();
        if (!value) return;
        if (tags.some(function (t) { return t.toLowerCase() === value.toLowerCase(); })) { toast('That feature is already added.', 'error'); return; }
        if (tags.length >= 8) { toast('You can add up to 8 features.', 'error'); return; }
        tags.push(value); renderTags();
    }
    $('#tag-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault(); addTag(this.value); this.value = '';
        } else if (e.key === 'Backspace' && !this.value && tags.length) {
            tags.pop(); renderTags();
        }
    });
    $('#tag-input').addEventListener('blur', function () { if (this.value.trim()) { addTag(this.value); this.value = ''; } });

    /* image upload + local preview */
    var objectUrl = null;
    function showPreview(src) {
        $('#dz-img').src = src;
        $('#dz-preview').hidden = false;
        $('#dz-empty').hidden = true;
    }
    function clearPreview() {
        if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        $('#f-image').value = '';
        $('#dz-img').removeAttribute('src');
        $('#dz-preview').hidden = true;
        $('#dz-empty').hidden = false;
    }
    function handleFile(file) {
        if (!file) return;
        if (['image/png', 'image/jpeg'].indexOf(file.type) === -1) { toast('Please choose a PNG or JPG image.', 'error'); return; }
        if (file.size > 2 * 1024 * 1024) { toast('Image is larger than 2MB.', 'error'); return; }
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        showPreview(objectUrl);
    }
    var dz = $('#dropzone');
    dz.addEventListener('click', function (e) { if (!e.target.closest('#dz-remove')) $('#f-image').click(); });
    dz.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $('#f-image').click(); } });
    $('#f-image').addEventListener('change', function () { handleFile(this.files[0]); });
    $('#dz-remove').addEventListener('click', function (e) { e.stopPropagation(); clearPreview(); });
    ['dragenter', 'dragover'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('drag'); }); });
    dz.addEventListener('drop', function (e) { handleFile(e.dataTransfer && e.dataTransfer.files[0]); });

    /* visual rich-text toolbar */
    var editor = $('#f-full');
    $$('.editor-toolbar button[data-cmd]').forEach(function (b) {
        b.addEventListener('mousedown', function (e) { e.preventDefault(); });
        b.addEventListener('click', function () {
            editor.focus();
            var cmd = b.dataset.cmd;
            if (cmd === 'createLink') {
                var url = window.prompt('Enter link URL (https://…)');
                if (url && /^https?:\/\//i.test(url)) document.execCommand('createLink', false, url);
                else if (url) toast('Links must start with http:// or https://', 'error');
            } else {
                document.execCommand(cmd, false, null);
            }
        });
    });
    $('#editor-format').addEventListener('change', function () {
        editor.focus();
        document.execCommand('formatBlock', false, this.value);
    });

    function setMode(text) { $('#form-mode').textContent = text; }

    function resetForm() {
        form.reset();
        editing = null;
        tags = DEFAULT_TAGS.slice();
        renderTags();
        editor.innerHTML = '';
        clearPreview();
        setMode('New service');
    }

    function scrollToForm() {
        $('#service-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function editService(s) {
        editing = s;
        form.reset();
        $('#f-name').value = s.name;
        $('#f-cat').value = s.cat;
        $('#f-fee').value = s.fee;
        $('#f-dur').value = s.mins;
        $('#f-short').value = s.desc;
        $('#f-status').value = s.status;
        editor.textContent = s.full || s.desc;
        tags = s.features.slice(); renderTags();
        clearPreview();
        showPreview('assets/images/' + s.img);
        setMode('Editing: ' + s.name);
        scrollToForm();
        setTimeout(function () { $('#f-name').focus({ preventScroll: true }); }, 450);
    }

    $$('.js-add').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.remove('sidebar-open');
            resetForm();
            scrollToForm();
            setTimeout(function () { $('#f-name').focus({ preventScroll: true }); }, 450);
        });
    });

    function updateCopy(s) {
        [s.row, s.card].forEach(function (el) {
            $$('[data-f="name"]', el).forEach(function (n) { n.textContent = s.name; });
            $$('[data-f="fee"]', el).forEach(function (n) { n.textContent = fmtMoney(s.fee); });
            $$('[data-f="dur"]', el).forEach(function (n) { n.textContent = fmtDuration(s.mins); });
            var d = $('[data-f="desc"]', el); if (d) d.textContent = s.desc;
            var c = $('[data-f="cat"]', el);
            c.textContent = CATEGORY_LABELS[s.cat];
            c.className = 'badge cat-' + s.cat;
            el.dataset.cat = s.cat;
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        var name = $('#f-name').value.trim();
        if (editing) {
            var s = editing;
            s.name = name; s.cat = $('#f-cat').value; s.fee = Number($('#f-fee').value);
            s.mins = Number($('#f-dur').value); s.desc = $('#f-short').value.trim();
            s.full = editor.textContent.trim() || s.desc; s.features = tags.slice();
            updateCopy(s);
            setStatus(s, $('#f-status').value);
            render();
            toast('“' + name + '” updated in this view (demo only — nothing is sent to a server).');
        } else {
            toast('“' + name + '” saved (demo only — nothing is sent to a server).');
        }
    });

    $('#form-cancel').addEventListener('click', function () {
        resetForm();
        $('.page-head').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    $('#form-back').addEventListener('click', function () {
        $('.page-head').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /* ---------------------------------------------------------
       Init
       --------------------------------------------------------- */
    resetForm();
    syncTabs();
    render();
})();
