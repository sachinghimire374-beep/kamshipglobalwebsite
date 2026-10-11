/* Kamship Global — interaction layer
   Vanilla JS, no dependencies. Everything degrades gracefully without it. */
(function () {
  "use strict";

  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var $  = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ------------------------------------------------------ sticky header -- */
  var header = $(".site-header");
  var toTop  = $(".to-top");
  var bar    = $(".progress");

  function onScroll() {
    var y = window.scrollY || document.documentElement.scrollTop;
    if (header) header.classList.toggle("stuck", y > 12);
    if (toTop)  toTop.classList.toggle("show", y > 640);
    if (bar) {
      var h = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.width = (h > 0 ? (y / h) * 100 : 0) + "%";
    }
  }
  var ticking = false;
  window.addEventListener("scroll", function () {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () {
      onScroll();
      if (typeof sweepReveals === "function") sweepReveals();
      if (typeof sweepCounters === "function") sweepCounters();
      ticking = false;
    });
  }, { passive: true });
  onScroll();

  if (toTop) {
    toTop.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: reduce ? "auto" : "smooth" });
    });
  }

  /* --------------------------------------------------------- mobile nav -- */
  var menuBtn = $(".menu-btn");
  var nav     = $("#site-nav");
  var scrim   = $(".nav-scrim");

  function setMenu(open) {
    if (!nav || !menuBtn) return;
    nav.classList.toggle("open", open);
    if (scrim) scrim.classList.toggle("on", open);
    menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.style.overflow = open && window.innerWidth <= 760 ? "hidden" : "";
  }
  if (menuBtn) {
    menuBtn.addEventListener("click", function () {
      setMenu(menuBtn.getAttribute("aria-expanded") !== "true");
    });
  }
  if (scrim) scrim.addEventListener("click", function () { setMenu(false); });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      setMenu(false);
      $$(".nav-item.open").forEach(function (i) { closeDrop(i); });
    }
  });

  /* ---------------------------------------------------------- dropdowns -- */
  function openDrop(item) {
    item.classList.add("open");
    var b = $(".nav-toggle", item);
    if (b) b.setAttribute("aria-expanded", "true");
  }
  function closeDrop(item) {
    item.classList.remove("open");
    var b = $(".nav-toggle", item);
    if (b) b.setAttribute("aria-expanded", "false");
  }

  $$(".nav-item").forEach(function (item) {
    var btn = $(".nav-toggle", item);
    if (!btn) return;

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      var open = item.classList.contains("open");
      $$(".nav-item.open").forEach(closeDrop);
      if (!open) openDrop(item);
    });

    // hover on pointer devices with a wide viewport only
    item.addEventListener("mouseenter", function () {
      if (window.innerWidth > 760 && window.matchMedia("(hover: hover)").matches) openDrop(item);
    });
    item.addEventListener("mouseleave", function () {
      if (window.innerWidth > 760 && window.matchMedia("(hover: hover)").matches) closeDrop(item);
    });
    item.addEventListener("focusout", function (e) {
      if (!item.contains(e.relatedTarget)) closeDrop(item);
    });
  });

  document.addEventListener("click", function (e) {
    if (!e.target.closest(".nav-item")) $$(".nav-item.open").forEach(closeDrop);
  });

  /* ------------------------------------------------------ scroll reveal -- */
  // A rect sweep rather than IntersectionObserver: IO can silently skip elements
  // that fly past during a fast wheel scroll or a phone fling, which would leave
  // content permanently invisible. This is cheap and cannot miss.
  var pending = $$("[data-reveal]");

  if (reduce) {
    pending.forEach(function (el) { el.classList.add("in"); });
    pending = [];
  } else {
    // stagger siblings that share a parent
    pending.forEach(function (el) {
      if (el.style.getPropertyValue("--d")) return;
      var sibs = Array.prototype.filter.call(el.parentNode.children, function (c) {
        return c.hasAttribute && c.hasAttribute("data-reveal");
      });
      var i = sibs.indexOf(el);
      if (i > 0 && sibs.length > 1 && sibs.length <= 8) el.style.setProperty("--d", (i * 80) + "ms");
    });
  }

  function sweepReveals() {
    if (!pending.length) return;
    var limit = window.innerHeight * 0.94;
    var still = [];
    for (var i = 0; i < pending.length; i++) {
      var el = pending[i];
      if (el.getBoundingClientRect().top < limit) el.classList.add("in");
      else still.push(el);
    }
    pending = still;
  }
  sweepReveals();
  window.addEventListener("resize", sweepReveals, { passive: true });
  // Anything still hidden once the page has settled gets shown regardless, so a
  // layout quirk can never hide content for good.
  window.setTimeout(function () {
    if (pending.length) { pending.forEach(function (el) { el.classList.add("in"); }); pending = []; }
  }, 9000);

  /* ---------------------------------------------------- number counters -- */
  function formatNum(n, decimals) {
    return n.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function runCount(el) {
    var target   = parseFloat(el.getAttribute("data-count"));
    var decimals = parseInt(el.getAttribute("data-decimals") || "0", 10);
    var dur      = parseInt(el.getAttribute("data-duration") || "1500", 10);
    if (isNaN(target)) return;
    if (reduce) { el.textContent = formatNum(target, decimals); return; }

    var start = null;
    function frame(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);           // easeOutCubic
      el.textContent = formatNum(target * eased, decimals);
      if (p < 1) window.requestAnimationFrame(frame);
    }
    window.requestAnimationFrame(frame);
  }

  // Counters use the same rect sweep as the reveals, for the same reason: a fast
  // scroll must never leave a statistic frozen at zero.
  var counters = $$("[data-count]");
  function sweepCounters() {
    if (!counters.length) return;
    var limit = window.innerHeight * 0.9;
    var still = [];
    for (var i = 0; i < counters.length; i++) {
      var el = counters[i];
      if (el.getBoundingClientRect().top < limit) runCount(el);
      else still.push(el);
    }
    counters = still;
  }
  sweepCounters();
  window.addEventListener("resize", sweepCounters, { passive: true });
  window.setTimeout(function () { if (counters.length) sweepCounters(); }, 9000);

  /* -------------------------------------------------------- hero enter -- */
  var hero = $(".hero");
  if (hero) {
    $$("[data-enter]", hero).forEach(function (el, i) {
      if (!el.style.getPropertyValue("--d")) el.style.setProperty("--d", (i * 110) + "ms");
    });
    window.requestAnimationFrame(function () { hero.classList.add("ready"); });
  }

  /* ---------------------------------------------------- headline rotator */
  var rot = $(".rotator");
  if (rot) {
    var words = $$("span", rot);
    if (words.length) {
      words[0].classList.add("on");
      if (!reduce && words.length > 1) {
        var idx = 0;
        setInterval(function () {
          var cur = words[idx];
          idx = (idx + 1) % words.length;
          var next = words[idx];
          cur.classList.remove("on");
          cur.classList.add("out");
          next.classList.remove("out");
          next.classList.add("on");
          window.setTimeout(function () { cur.classList.remove("out"); }, 500);
        }, 2900);
      }
    }
  }

  /* --------------------------------------------------------- live clocks */
  function timeIn(tz) {
    try {
      return new Intl.DateTimeFormat("en-US", {
        hour: "numeric", minute: "2-digit", timeZone: tz
      }).format(new Date());
    } catch (e) { return ""; }
  }
  function tickClocks() {
    $$("[data-tz]").forEach(function (el) {
      var t = timeIn(el.getAttribute("data-tz"));
      if (t) el.textContent = t;
    });
  }
  tickClocks();
  setInterval(tickClocks, 20000);

  /* ------------------------------------------------- operations board -- */
  // Queue items resolve once, in sequence — one orchestrated moment, not a loop.
  var boardRows = $$("[data-next]");
  boardRows.forEach(function (el, i) {
    function apply() {
      el.textContent = el.getAttribute("data-next");
      el.className = "status " + (el.getAttribute("data-next-class") || "done");
      if (!reduce) el.classList.add("flip");
    }
    if (reduce) apply();
    else window.setTimeout(apply, 1600 + i * 950);
  });

  /* --------------------------------------------------------------- tabs -- */
  $$("[data-tabs]").forEach(function (group) {
    var btns   = $$("[role=tab]", group);
    var panels = $$("[role=tabpanel]", group);

    function select(id) {
      btns.forEach(function (b) {
        var on = b.getAttribute("aria-controls") === id;
        b.setAttribute("aria-selected", on ? "true" : "false");
        b.setAttribute("tabindex", on ? "0" : "-1");
      });
      panels.forEach(function (p) { p.hidden = p.id !== id; });
    }

    btns.forEach(function (b) {
      b.addEventListener("click", function () { select(b.getAttribute("aria-controls")); });
      b.addEventListener("keydown", function (e) {
        var i = btns.indexOf(b);
        var n = null;
        if (e.key === "ArrowRight") n = btns[(i + 1) % btns.length];
        if (e.key === "ArrowLeft")  n = btns[(i - 1 + btns.length) % btns.length];
        if (n) { e.preventDefault(); n.focus(); select(n.getAttribute("aria-controls")); }
      });
    });
  });

  /* --------------------------------------------------- accordion (one) -- */
  // Within a group marked data-acc-single, opening one closes the others.
  $$("[data-acc-single]").forEach(function (group) {
    var items = $$("details", group);
    items.forEach(function (d) {
      d.addEventListener("toggle", function () {
        if (!d.open) return;
        items.forEach(function (o) { if (o !== d) o.open = false; });
      });
    });
  });

  /* ---------------------------------------------------- marquee doubling */
  $$(".marquee-track").forEach(function (track) {
    if (track.getAttribute("data-doubled")) return;
    track.innerHTML += track.innerHTML;       // seamless loop needs two copies
    track.setAttribute("data-doubled", "1");
  });

  /* ------------------------------------------------------- current year -- */
  $$("#year, .js-year").forEach(function (el) { el.textContent = new Date().getFullYear(); });
})();
