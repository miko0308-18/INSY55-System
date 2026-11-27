(function () {
  const sidebar = document.getElementById('adminSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const toggle = document.getElementById('sidebarToggle');

  function closeSidebar() {
    sidebar?.classList.remove('show');
    backdrop?.classList.remove('show');
  }

  function openSidebar() {
    sidebar?.classList.add('show');
    backdrop?.classList.add('show');
  }

  toggle?.addEventListener('click', function () {
    if (sidebar?.classList.contains('show')) closeSidebar();
    else openSidebar();
  });

  backdrop?.addEventListener('click', closeSidebar);

  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      const message = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!window.confirm(message)) {
        e.preventDefault();
      }
    });
  });
})();
