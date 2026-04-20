document.addEventListener("DOMContentLoaded", () => {
  const slotInputs = Array.from(document.querySelectorAll("[data-slot-input]"));
  const bookingPanel = document.querySelector("[data-booking-panel]");
  const selectionLimit = Number(bookingPanel?.dataset.maxSlots || 2);

  if (slotInputs.length === 0) {
    return;
  }

  const updateSelectionState = () => {
    const checked = slotInputs.filter((input) => input.checked).map((input) => input.value).sort();

    slotInputs.forEach((input) => {
      const pill = input.closest(".slot-pill");
      if (pill) {
        pill.classList.toggle("is-selected", input.checked);
      }
    });

    if (checked.length <= 1) {
      return;
    }

    const minutes = checked.map((time) => {
      const [hours, minutesPart] = time.split(":").map(Number);
      return (hours * 60) + minutesPart;
    });

    const invalidGap = minutes.some((value, index) => index > 0 && value - minutes[index - 1] !== 60);
    const warning = document.querySelector("[data-slot-warning]");
    if (warning) {
      warning.hidden = !invalidGap;
    }
  };

  slotInputs.forEach((input) => {
    input.addEventListener("change", () => {
      const checkedCount = slotInputs.filter((item) => item.checked).length;
      if (checkedCount > selectionLimit) {
        input.checked = false;
        window.alert(`You can only choose up to ${selectionLimit} consecutive slots.`);
      }

      updateSelectionState();
    });
  });

  updateSelectionState();
});
