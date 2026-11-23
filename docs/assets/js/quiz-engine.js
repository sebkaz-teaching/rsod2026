/**
 * Course quiz engine — shared across course sites (rsod2026, prba2026,
 * ...), each keeping its own copy of this file. Mounts a self-grading
 * quiz into a container and POSTs the result to the shared PHP backend
 * in _server/, tagged with which course and lecture page it's on.
 *
 * Usage (top of a lecture .qmd, as raw HTML):
 *   <div id="course-quiz"></div>
 *   <script src="../assets/js/quiz-engine.js"></script>
 *   <script>
 *     CourseQuiz.mount(document.getElementById('course-quiz'), {
 *       course: "rsod2026",
 *       lecture: "wyklad2",
 *       submitUrl: "https://sebastianzajac.pl/rsod-quiz/submit.php",
 *       questions: [ { id, points, diff, text, code, options:[{v,t}] }, ... ]
 *     });
 */
(function (global) {
  "use strict";

  var STYLE_ID = "rsod-quiz-style";
  var CSS = `
.rsod-quiz{--rq-blue:#33539e;--rq-line:#dce3ed;margin:24px 0 32px;padding:clamp(16px,3vw,28px);border:1px solid var(--rq-line);border-radius:16px;background:#f5f7fb;color:#203247;font:inherit}
.rsod-quiz *{box-sizing:border-box}
.rsod-quiz .rq-heading{margin-bottom:24px}
.rsod-quiz .rq-eyebrow{margin:0 0 8px;color:var(--rq-blue);font-size:.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.rsod-quiz h3{margin:0 0 12px;font-size:1.3rem;line-height:1.4;border:0}
.rsod-quiz .rq-badges{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 12px}
.rsod-quiz .rq-badges span{padding:5px 10px;border:1px solid var(--rq-line);border-radius:20px;background:#fff;font-size:.8rem}
.rsod-quiz .rq-help{margin:8px 0 0;color:#53647c;font-size:.86rem;line-height:1.6}
.rsod-quiz .rq-field{display:flex;flex-direction:column;gap:8px;margin:0 0 24px}
.rsod-quiz .rq-field label{font-weight:650}
.rsod-quiz input[type=email]{width:100%;max-width:480px;padding:11px 13px;border:1px solid #aebbd0;border-radius:8px;background:#fff;color:inherit;font:inherit}
.rsod-quiz .rq-questions{display:grid;gap:18px}
.rsod-quiz .rq-q{min-width:0;margin:0;padding:20px;border:1px solid var(--rq-line);border-radius:12px;background:#fff}
.rsod-quiz .rq-meta{display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;margin-bottom:12px;color:#53647c;font-size:.78rem}
.rsod-quiz .rq-question{margin:0 0 14px;font-weight:650;line-height:1.6}
.rsod-quiz pre{padding:12px;border-radius:8px;background:#eef2f8;overflow-x:auto;font-size:.85rem}
.rsod-quiz .rq-opts{display:grid;gap:8px}
.rsod-quiz .rq-opt{display:flex;align-items:flex-start;gap:11px;margin:0;padding:11px 13px;border:1px solid var(--rq-line);border-radius:8px;cursor:pointer;line-height:1.55;transition:background .15s,border-color .15s}
.rsod-quiz .rq-opt:hover{border-color:#91a5cb;background:#f4f7fc}
.rsod-quiz .rq-opt:has(input:checked){border-color:var(--rq-blue);background:#eaf0fc;box-shadow:inset 3px 0 var(--rq-blue)}
.rsod-quiz input[type=radio]{flex:0 0 auto;width:17px;height:17px;margin:4px 0 0;accent-color:var(--rq-blue)}
.rsod-quiz :is(input,button):focus-visible{outline:3px solid #789ce3;outline-offset:3px}
.rsod-quiz .rq-footer{margin-top:24px;padding-top:20px;border-top:1px solid var(--rq-line)}
.rsod-quiz button.rq-submit{padding:12px 20px;border:0;border-radius:9px;background:var(--rq-blue);color:#fff;font:inherit;font-weight:650;cursor:pointer}
.rsod-quiz button.rq-submit:hover{background:#26427f}
.rsod-quiz button.rq-submit:disabled{opacity:.65;cursor:default}
.rsod-quiz .rq-result,.rsod-quiz .rq-error{margin-top:20px;padding:18px;border-radius:10px;line-height:1.6}
.rsod-quiz .rq-result{border:1px solid #b7d9c2;background:#e8f5ed;color:#215d36}
.rsod-quiz .rq-error{border:1px solid #efc6c1;background:#fff0ee;color:#8a2f1f}
.rsod-quiz .rq-score{font-size:1.8rem;font-weight:750}
@media(max-width:600px){.rsod-quiz .rq-q{padding:14px}.rsod-quiz .rq-opt{padding:10px}.rsod-quiz button.rq-submit{width:100%}}
`;

  function injectStyle() {
    if (document.getElementById(STYLE_ID)) return;
    var s = document.createElement("style");
    s.id = STYLE_ID;
    s.textContent = CSS;
    document.head.appendChild(s);
  }

  function el(html) {
    var t = document.createElement("template");
    t.innerHTML = html.trim();
    return t.content.firstElementChild;
  }

  var DISPLAY_LETTERS = ["A", "B", "C", "D"];

  // Fisher-Yates. Grading uses each option's own `v` (submitted as the
  // radio's value), never the on-screen position — shuffling display
  // order only stops "always pick the Nth option" guessing, it can't
  // desync grading.
  function shuffled(arr) {
    var copy = arr.slice();
    for (var i = copy.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var tmp = copy[i];
      copy[i] = copy[j];
      copy[j] = tmp;
    }
    return copy;
  }

  function mount(container, config) {
    injectStyle();
    container.classList.add("rsod-quiz");
    var questions = config.questions;
    var maxScore = questions.reduce(function (s, q) { return s + q.points; }, 0);

    container.innerHTML =
      '<div class="rq-heading"><p class="rq-eyebrow">Test powtórkowy</p><h3>' +
      (config.title || config.lecture) + '</h3><div class="rq-badges"><span>' +
      questions.length + ' pytań</span><span>' + maxScore +
      ' pkt do zdobycia</span><span>Jedna odpowiedź w pytaniu</span></div>' +
      '<p class="rq-help">Wpisz e-mail i zaznacz odpowiedź w każdym pytaniu. Po wysłaniu zobaczysz wynik.</p></div>' +
      '<form class="rq-form">' +
      '<div class="rq-field"><label for="rq-email"><strong>E-mail</strong></label>' +
      '<input type="email" id="rq-email" required autocomplete="email" placeholder="imie.nazwisko@student.wat.edu.pl"></div>' +
      '<div class="rq-questions"></div>' +
      '<div class="rq-footer"><button type="submit" class="rq-submit">Wyślij i pokaż wynik</button>' +
      '<p class="rq-help">Poczekaj na potwierdzenie zapisu — samo kliknięcie nie oznacza jeszcze wysłania wyniku.</p></div>' +
      "</form>" +
      '<div class="rq-output" role="status" aria-live="polite"></div>';

    var qHost = container.querySelector(".rq-questions");
    questions.forEach(function (q, i) {
      var opts = shuffled(q.options)
        .map(function (o, idx) {
          return (
            '<label class="rq-opt"><input type="radio" name="' +
            q.id +
            '" value="' +
            o.v +
            '" required><span><strong>' +
            DISPLAY_LETTERS[idx] +
            ")</strong> " +
            o.t +
            "</span></label>"
          );
        })
        .join("");
      var block = el(
        '<div class="rq-q" role="group" aria-labelledby="rq-title-' + i + '">' +
          '<div class="rq-meta"><span>Pytanie ' +
          (i + 1) +
          " · " +
          q.points +
          " pkt</span><span>" +
          (q.diff || "") +
          "</span></div>" +
          '<p class="rq-question" id="rq-title-' + i + '">' +
          q.text +
          "</p>" +
          (q.code ? "<pre><code>" + q.code.replace(/</g, "&lt;") + "</code></pre>" : "") +
          '<div class="rq-opts">' +
          opts +
          "</div>" +
          "</div>"
      );
      qHost.appendChild(block);
    });

    var form = container.querySelector(".rq-form");
    var output = container.querySelector(".rq-output");
    var submitBtn = container.querySelector(".rq-submit");

    form.addEventListener("submit", function (ev) {
      ev.preventDefault();
      var email = container.querySelector("#rq-email").value.trim();
      var answers = {};
      questions.forEach(function (q) {
        var picked = form.querySelector('input[name="' + q.id + '"]:checked');
        answers[q.id] = picked ? picked.value : null;
      });

      submitBtn.disabled = true;
      submitBtn.textContent = "Wysyłanie…";

      fetch(config.submitUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: email, course: config.course, lecture: config.lecture, answers: answers }),
      })
        .then(function (r) {
          if (!r.ok) throw new Error("http_" + r.status);
          return r.json();
        })
        .then(function (data) {
          if (!data.ok) throw new Error(data.error || "unknown_error");
          output.innerHTML =
            '<div class="rq-result"><div class="rq-score">' +
            data.score +
            " / " +
            data.maxScore +
            "</div><p>Wynik zapisany dla " +
            email +
            ".</p></div>";
          submitBtn.textContent = "Wynik zapisany";
        })
        .catch(function (err) {
          output.innerHTML =
            '<div class="rq-error">Nie udało się zapisać wyniku (' +
            err.message +
            "). Spróbuj ponownie za chwilę lub napisz do prowadzącego.</div>";
          submitBtn.disabled = false;
          submitBtn.textContent = "Wyślij i pokaż wynik";
        });
    });
  }

  global.CourseQuiz = { mount: mount };
})(window);
