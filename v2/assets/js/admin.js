document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-toggle-target]").forEach((button) => {
    button.addEventListener("click", () => {
      const selector = button.getAttribute("data-toggle-target");
      const target = selector ? document.querySelector(selector) : null;
      if (target) {
        target.hidden = !target.hidden;
      }
    });
  });
});
