// تبديل الوضع الليلي/النهاري، والقيمة بتتحفظ في localStorage عشان تفضل زي ما المستخدم مختار
function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('theme', theme);
}

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        });
    }

    // فتح/قفل قائمة التنقل على الموبايل
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('hidden');
        });
    }

    // القوائم المنسدلة في الـ navbar (الإدارة / التقارير) على الشاشات الكبيرة
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !menu.classList.contains('hidden');
            document.querySelectorAll('[data-dropdown-menu]').forEach((m) => m.classList.add('hidden'));
            menu.classList.toggle('hidden', isOpen);
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown-menu]').forEach((m) => m.classList.add('hidden'));
    });

    // نموذج "أضف عدة صفوف واحفظهم مرة واحدة" (طلاب/معلمين/فصول/مواعيد)
    // كل حاوية عندها data-row-adder، وجواها data-rows فيها صفوف data-row
    document.querySelectorAll('[data-row-adder]').forEach((container) => {
        const addBtn = container.querySelector('[data-add-row]');
        const rowsBox = container.querySelector('[data-rows]');
        if (!addBtn || !rowsBox) return;

        addBtn.addEventListener('click', () => {
            const rows = rowsBox.querySelectorAll('[data-row]');
            const clone = rows[rows.length - 1].cloneNode(true);
            clone.querySelectorAll('input').forEach((input) => { input.value = ''; });
            clone.querySelectorAll('select').forEach((select) => { select.selectedIndex = 0; });
            rowsBox.appendChild(clone);
            clone.querySelector('input, select')?.focus();
        });

        rowsBox.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('[data-remove-row]');
            if (!removeBtn) return;
            const rows = rowsBox.querySelectorAll('[data-row]');
            if (rows.length > 1) removeBtn.closest('[data-row]').remove();
        });
    });

    // تأكيد قبل أي عملية حذف عندها data-confirm
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // فلترة فورية لجدول أو قائمة بمجرد الكتابة في خانة بحث عندها data-live-search
    document.querySelectorAll('[data-live-search]').forEach((input) => {
        const targetSelector = input.getAttribute('data-live-search');
        const rows = document.querySelectorAll(targetSelector);
        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            rows.forEach((row) => {
                const text = row.getAttribute('data-search-text') || row.textContent;
                row.classList.toggle('hidden', !text.toLowerCase().includes(term));
            });
        });
    });

    // تأثير ظهور تدريجي للمحتوى عند تحميل الصفحة
    const mainContent = document.getElementById('page-content');
    if (mainContent) {
        requestAnimationFrame(() => mainContent.classList.add('page-in'));
    }
});
