/* One whole-laboratory declaration; timestamps are assigned by the server. */
(function () {
  "use strict";
  const root = document.getElementById("lab-confirmation");
  if (!root) return;
  const form = root.querySelector("form");
  const message = root.querySelector('[role="status"]');
  const button = form.querySelector("button");
  const errors = {
    invalid_email: "Wpisz poprawny e-mail.",
    email_domain_not_allowed: "Użyj adresu @wat.edu.pl lub jego subdomeny.",
    invalid_group: "Wybierz grupę 1, 2 lub 3.",
    save_unavailable: "Serwer zapisu jest niedostępny. Spróbuj ponownie."
  };
  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    button.disabled = true;
    message.textContent = "Zapisywanie potwierdzenia…";
    try {
      const response = await fetch(root.dataset.endpoint, {
        method: "POST", credentials: "omit", cache: "no-store",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({course: "rsod2026", lab: "lab01", action: "confirm",
          email: form.elements.email.value.trim().toLowerCase(), group: form.elements.group.value}),
        signal: AbortSignal.timeout(15000)
      });
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(errors[data.error] || "Nie udało się zapisać potwierdzenia.");
      message.textContent = "Potwierdzenie zapisane. Prowadzący może je zobaczyć w podsumowaniu. Ponowne kliknięcie nie tworzy drugiego wpisu.";
    } catch (error) {
      message.textContent = (errors.save_unavailable + " " +
        (error.message === "Failed to fetch" ? "Sprawdź połączenie z internetem." : error.message));
    } finally {
      button.disabled = false;
    }
  });
}());
