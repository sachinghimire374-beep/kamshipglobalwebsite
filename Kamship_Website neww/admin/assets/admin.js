/* Kamship admin — small helpers. The panel works without this file. */
(function () {
  "use strict";
  var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };

  /* Confirm before anything destructive. */
  $$("form[data-confirm]").forEach(function (f) {
    f.addEventListener("submit", function (e) {
      if (!window.confirm(f.getAttribute("data-confirm"))) { e.preventDefault(); }
    });
  });

  /* Mobile: show the section list and the top-bar links. */
  var btn = document.querySelector(".anav-btn");
  var side = document.querySelector(".aside");
  var right = document.querySelector(".abar-right");
  if (btn && side) {
    btn.addEventListener("click", function () {
      var open = side.classList.toggle("open");
      if (right) { right.classList.toggle("open", open); }
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  /* Add another empty question row on the FAQ screen. */
  var add = document.querySelector("[data-add-faq]");
  var rows = document.getElementById("faq-rows");
  if (add && rows) {
    add.addEventListener("click", function () {
      var n = rows.children.length + 1;
      var d = document.createElement("div");
      d.className = "asubpanel afaq";
      d.innerHTML =
        '<div class="afield afield-wide"><label for="q' + n + '">Question ' + n + '</label>' +
        '<input id="q' + n + '" type="text" name="q[]" maxlength="300"></div>' +
        '<div class="afield afield-wide"><label for="a' + n + '">Answer</label>' +
        '<textarea id="a' + n + '" name="a[]" rows="3"></textarea></div>';
      rows.appendChild(d);
      var input = d.querySelector("input");
      if (input) { input.focus(); }
    });
  }

  /* Warn before leaving a form with unsaved edits. */
  $$("form").forEach(function (f) {
    if (f.hasAttribute("data-confirm") || f.method.toLowerCase() !== "post") { return; }
    var dirty = false;
    f.addEventListener("input", function () { dirty = true; });
    f.addEventListener("submit", function () { dirty = false; });
    window.addEventListener("beforeunload", function (e) {
      if (dirty) { e.preventDefault(); e.returnValue = ""; }
    });
  });

  /* Branding: keep the colour swatch and the hex box showing the same value.
     The text box is the one that gets submitted, so the swatch is only ever a
     convenience — typing a hex code by hand still works on its own. */
  $$("input[data-syncs]").forEach(function (picker) {
    var text = document.getElementById(picker.getAttribute("data-syncs"));
    if (!text) { return; }
    picker.addEventListener("input", function () {
      text.value = picker.value.toUpperCase();
      text.dispatchEvent(new Event("input", { bubbles: true }));
    });
    text.addEventListener("input", function () {
      if (/^#[0-9a-fA-F]{6}$/.test(text.value.trim())) { picker.value = text.value.trim(); }
    });
  });

  /* Textareas grow to fit their content. */
  $$("textarea").forEach(function (t) {
    function fit() {
      if (t.scrollHeight > t.clientHeight) { t.style.height = (t.scrollHeight + 4) + "px"; }
    }
    t.addEventListener("input", fit);
    fit();
  });
})();
