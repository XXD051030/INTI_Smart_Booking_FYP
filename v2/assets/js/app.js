document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".sidebar");
  const toggle = document.querySelector("[data-sidebar-toggle]");

  if (sidebar && toggle) {
    toggle.addEventListener("click", () => {
      sidebar.classList.toggle("is-open");
    });
  }

  document.querySelectorAll("[data-dismiss-flash]").forEach((button) => {
    button.addEventListener("click", () => {
      const flash = button.closest("[data-flash]");
      if (flash) {
        flash.remove();
      }
    });
  });

  document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const message = form.getAttribute("data-confirm");
      if (message && !window.confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
