// app/code/Ignitix/BlackFriday25/view/frontend/web/js/scratch.js
define([
  'jquery',
  'Magento_Checkout/js/model/quote',
  'Magento_Customer/js/customer-data',
  'Magento_Checkout/js/action/get-payment-information',
  'mage/translate'
], function ($, quote, customerData, getPaymentInformationAction) {
  'use strict';

  var globalCfg = {};
  var pollTimer = null;

  /* ------------------------ fireworks ------------------------- */
  function startFireworks(durationMs) {
    var cv = document.getElementById('bf25-fireworks');
    if (!cv) {
      cv = document.createElement('canvas');
      cv.id = 'bf25-fireworks';
      cv.style.position = 'fixed';
      cv.style.inset = '0';
      cv.style.zIndex = '10000';
      cv.style.pointerEvents = 'none';
      document.body.appendChild(cv);
    }
    var ctx = cv.getContext('2d');
    var w, h, dpr = Math.max(1, window.devicePixelRatio || 1);

    function resize() {
      w = Math.floor(window.innerWidth * dpr);
      h = Math.floor(window.innerHeight * dpr);
      cv.width = w; cv.height = h;
      cv.style.width = window.innerWidth + 'px';
      cv.style.height = window.innerHeight + 'px';
    }
    resize();
    window.addEventListener('resize', resize);

    var particles = [];
    function burst() {
      var x = Math.random() * w;
      var y = Math.random() * h * 0.5 + 20 * dpr;
      var count = 20 + Math.floor(Math.random() * 30);
      for (var i = 0; i < count; i++) {
        var a = Math.random() * Math.PI * 2;
        var s = (Math.random() * 4 + 2) * dpr;
        particles.push({
          x: x, y: y,
          vx: Math.cos(a) * s,
          vy: Math.sin(a) * s,
          life: 60 + Math.random() * 20,
          age: 0
        });
      }
    }

    var endAt = Date.now() + (durationMs || 10000);
    var raf;
    (function loop() {
      if (Date.now() > endAt) {
        cancelAnimationFrame(raf);
        window.removeEventListener('resize', resize);
        ctx.clearRect(0, 0, w, h);
        if (cv && cv.parentNode) cv.parentNode.removeChild(cv);
        return;
      }
      raf = requestAnimationFrame(loop);

      ctx.clearRect(0, 0, w, h);
      if (Math.random() < 0.08) burst();

      for (var i = particles.length - 1; i >= 0; i--) {
        var p = particles[i];
        p.age++;
        p.vy += 0.03 * dpr;
        p.x += p.vx;
        p.y += p.vy;

        if (p.age > p.life) { particles.splice(i, 1); continue; }

        var alpha = 1 - p.age / p.life;
        ctx.beginPath();
        ctx.arc(p.x, p.y, 2 * dpr, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(' +
          (200 + Math.floor(Math.random() * 55)) + ',' +
          (100 + Math.floor(Math.random() * 155)) + ',' +
          (50 + Math.floor(Math.random() * 205)) + ',' + alpha + ')';
        ctx.fill();
      }
    })();
  }

  function continueFireworksFromStorage() {
    var untilRaw = sessionStorage.getItem('bf25_fireworks_until');
    var until = untilRaw ? parseInt(untilRaw, 10) : 0;
    if (until && until > Date.now()) {
      startFireworks(until - Date.now());
      setTimeout(function () {
        sessionStorage.removeItem('bf25_fireworks_until');
      }, (until - Date.now()) + 200);
    } else {
      sessionStorage.removeItem('bf25_fireworks_until');
    }
  }

  /* ---------------------- inline initializer ------------------ */
  function initInline(containerSelector, cfg) {
    var $root = $(containerSelector);
    if (!$root.length) return;

    // Per-instance state
    var state = $root.data('bf25-state');
    if (state && state.initialized) return;
    state = {
      initialized: true,
      canvasInit: false,
      revealing: false,
      applied: false,
      pendingSku: '',
      preselected: false,
      imageLockedUrl: '' // lock the first resolved image
    };
    $root.data('bf25-state', state);

    // Elements
    var canvas      = $root.find('#ignitix-bf25-inline-canvas')[0];
    var $prize      = $root.find('#ignitix-bf25-inline-prize');
    var $result     = $root.find('#ignitix-bf25-inline-result');
    var $resultName = $root.find('#ignitix-bf25-inline-result-name');
    var $underlay   = $root.find('#bf25-inline-underlay').length
      ? $root.find('#bf25-inline-underlay')
      : $root.find('.ignitix-bf25-underlay');

    function setUnderlayToSingleImage(url, alt) {
      if (!$underlay.length || !url) return;
      if (state.imageLockedUrl) return; // already locked, don't change
      state.imageLockedUrl = url;

      $underlay
        .empty()
        .append(
          $('<div/>', {
            css: {
              width: '100%',
              height: '100%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              background: '#fff'
            }
          }).append(
            $('<img/>', {
              src: url,
              alt: alt || '',
              css: {
                maxWidth: '100%',
                maxHeight: '100%',
                objectFit: 'contain',
                display: 'block'
              }
            })
          )
        );
    }

    function showMessage(msg) {
      if ($prize && $prize.length) {
        $prize.text(msg || $.mage.__('Not eligible.'));
      }
    }

    // Preselect (promise) — lock the single prize visual + remember SKU
    function preselectPrize() {
      var dfd = $.Deferred();
      if (state.preselected || !cfg.preselectUrl) { dfd.resolve(); return dfd.promise(); }
      state.preselected = true;

      $.getJSON(cfg.preselectUrl).done(function (resp) {
        if (resp && resp.success && resp.product) {
          state.pendingSku = resp.product.sku || '';
          var imgUrl = resp.product.image || resp.product.small_image || '';
          var alt    = resp.product.name || '';
          if (imgUrl) setUnderlayToSingleImage(imgUrl, alt); // locks image
        } else if (resp && resp.message) {
          showMessage(resp.message);
        }
      }).always(function () {
        dfd.resolve();
      });

      return dfd.promise();
    }

    // DPR-aware overlay paint
    function paintOverlay() {
      if (!canvas) return;
      var ctx = canvas.getContext('2d');
      var dpr = Math.max(1, window.devicePixelRatio || 1);

      var rect = canvas.getBoundingClientRect();
      if (rect.width === 0 || rect.height === 0) {
        setTimeout(paintOverlay, 50);
        return;
      }

      canvas.style.width = rect.width + 'px';
      canvas.style.height = rect.height + 'px';
      canvas.width = Math.floor(rect.width * dpr);
      canvas.height = Math.floor(rect.height * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

      var w = rect.width, h = rect.height;
      ctx.clearRect(0, 0, w, h);
      ctx.globalCompositeOperation = 'source-over';
      ctx.fillStyle = '#b0b7c3';
      ctx.fillRect(0, 0, w, h);
      ctx.fillStyle = '#dfe3eb';
      for (var i = 0; i < w; i += 12) { ctx.fillRect(i, 0, 6, h); }

      ctx.fillStyle = '#6b7280';
      ctx.font = 'bold 20px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText($.mage.__('Scratch here'), w / 2, h / 2);

      ctx.globalCompositeOperation = 'destination-out';
    }

    function initCanvas() {
      if (state.canvasInit || !canvas) return;
      state.canvasInit = true;

      var ctx = canvas.getContext('2d');
      paintOverlay();

      var isDown = false, revealThreshold = 0.55, moveCount = 0;

      function scratch(e) {
        if (!isDown || state.revealing) return;
        var rect = canvas.getBoundingClientRect();
        var t = e.touches ? e.touches[0] : e;
        var x = t.clientX - rect.left, y = t.clientY - rect.top;

        ctx.beginPath();
        ctx.arc(x, y, 18, 0, Math.PI * 2);
        ctx.fill();

        try {
          var w = rect.width, h = rect.height;
          var img = ctx.getImageData(0, 0, Math.max(1, Math.floor(w)), Math.max(1, Math.floor(h)));
          var data = img.data, cleared = 0;
          for (var i = 3; i < data.length; i += 4) { if (data[i] === 0) cleared++; }
          if ((cleared / (w * h)) > revealThreshold) { reveal(); }
        } catch (err) {
          moveCount++; if (moveCount > 40) { reveal(); }
        }
      }

      canvas.addEventListener('mousedown', function () { isDown = true; });
      canvas.addEventListener('mouseup', function () { isDown = false; });
      canvas.addEventListener('mouseleave', function () { isDown = false; });
      canvas.addEventListener('mousemove', scratch);

      canvas.addEventListener('touchstart', function (e) { isDown = true; e.preventDefault(); }, { passive: false });
      canvas.addEventListener('touchend', function () { isDown = false; }, { passive: false });
      canvas.addEventListener('touchcancel', function () { isDown = false; }, { passive: false });
      canvas.addEventListener('touchmove', scratch, { passive: false });

      if (window.ResizeObserver) {
        var ro = new ResizeObserver(function () { paintOverlay(); });
        ro.observe(canvas);
      } else {
        window.addEventListener('resize', paintOverlay);
      }
    }

    function reveal() {
      if (state.applied || state.revealing) return;
      state.revealing = true;

      if ($prize.length) $prize.text($.mage.__('Drawing gift...'));

      var fk = cfg.formKey || (typeof window !== 'undefined' && window.FORM_KEY) || '';
      $.ajax({
        type: 'POST',
        url: cfg.applyUrl,
        data: {
          form_key: fk,
          sku: state.pendingSku || '' // backend will honor pending/hint or re-draw
        },
        dataType: 'json'
      })
      .done(function (resp) {
        if (resp && (resp.success || resp.alreadyApplied)) {
          state.applied = true;

          var imgUrl = (resp.product && (resp.product.image || resp.product.small_image)) ? (resp.product.image || resp.product.small_image) : '';
          var imgAlt = (resp.product && resp.product.name) ? resp.product.name : '';
          if (imgUrl && !state.imageLockedUrl) {
            setUnderlayToSingleImage(imgUrl, imgAlt); // don't overwrite if locked
          }

          if (resp.product && resp.product.name) {
            $resultName.text(resp.product.name + (resp.product.sku ? ' (' + resp.product.sku + ')' : ''));
            $result.show();
          }

          customerData.reload(['cart'], true);
          $.when(getPaymentInformationAction()).always(function () {
            $(document).trigger('bf25:applied', [resp.product || null]);

            var fwMs = (parseInt(cfg.fireworksSeconds, 10) > 0 ? parseInt(cfg.fireworksSeconds, 10) : 10) * 1000;
            var endTs = Date.now() + fwMs;
            sessionStorage.setItem('bf25_fireworks_until', String(endTs));
            startFireworks(fwMs);

            var reloadMs = (parseInt(cfg.postRevealReloadMs, 10) > 0 ? parseInt(cfg.postRevealReloadMs, 10) : 5000);
            setTimeout(function () { location.reload(true); }, reloadMs);
          });
        } else {
          var msg = (resp && resp.message) ? resp.message : $.mage.__('Could not apply prize.');
          if ($prize.length) $prize.text(msg);
        }
      })
      .fail(function (xhr) {
        var msg = $.mage.__('Error. Please try again.');
        try { if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message; } catch (e) {}
        if ($prize.length) $prize.text(msg);
      })
      .always(function () { state.revealing = false; });
    }

    // Expose reveal for this instance (optional)
    $root.data('bf25-reveal', reveal);

    // Ensure underlay (image) is set before scratching starts, then init canvas
    preselectPrize().always(function () {
      initCanvas();
    });
  }

  /* --------------------- optional global init ----------------- */
  function startPolling(statusUrl) {
    if (pollTimer) clearInterval(pollTimer);
    if (!statusUrl) return;
    pollTimer = setInterval(function () {
      $.getJSON(statusUrl).done(function (s) {
        if (s && typeof s.applied !== 'undefined' && s.applied) {
          $(document).trigger('bf25:applied');
          clearInterval(pollTimer); pollTimer = null;
        }
      });
    }, 5000);
  }

  // Public API
  window.IgnitixBf25 = window.IgnitixBf25 || {};
  window.IgnitixBf25.initInline = initInline;
  window.IgnitixBf25._startFireworks = startFireworks;

  continueFireworksFromStorage();

  return function (cfg) {
    globalCfg = cfg || {};
    if (globalCfg.statusUrl) startPolling(globalCfg.statusUrl);
  };
});