/* ============================
   ERUM – app.js (jQuery)
   ============================ */

$(function () {

  /* ── Navbar scroll effect ── */
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 50) {
      $('#mainNav').addClass('scrolled');
    } else {
      $('#mainNav').removeClass('scrolled');
    }
  });

  /* ── Smooth scroll for anchor links ── */
  $('a[href^="#"]').on('click', function (e) {
    const target = $($(this).attr('href'));
    if (target.length) {
      e.preventDefault();
      $('html, body').animate({ scrollTop: target.offset().top - 70 }, 700, 'swing');
    }
  });

  /* ── AOS-like scroll reveal ── */
  function checkAOS() {
    $('[data-aos]').each(function () {
      const elTop = $(this).offset().top;
      const winBottom = $(window).scrollTop() + $(window).height() - 60;
      const delay = parseInt($(this).data('aos-delay') || 0);
      if (elTop < winBottom) {
        const $el = $(this);
        setTimeout(function () {
          $el.addClass('aos-animate');
        }, delay);
      }
    });
  }
  $(window).on('scroll', checkAOS);
  checkAOS(); // run on load

  /* ── Animated counter ── */
  function animateCounter($el) {
    const target = parseInt($el.data('target'));
    let current = 0;
    const step = Math.max(1, Math.floor(target / 60));
    const timer = setInterval(function () {
      current = Math.min(current + step, target);
      $el.text(current.toLocaleString('id-ID'));
      if (current >= target) clearInterval(timer);
    }, 25);
  }

  // Trigger counters when in viewport
  let counterDone = false;
  $(window).on('scroll', function () {
    if (!counterDone) {
      const statsTop = $('.hero-stats').offset().top;
      if ($(window).scrollTop() + $(window).height() > statsTop + 50) {
        counterDone = true;
        $('.stat-num').each(function () { animateCounter($(this)); });
      }
    }
  });
  // Also trigger on load if already visible
  setTimeout(function () {
    const statsTop = $('.hero-stats').offset().top;
    if ($(window).scrollTop() + $(window).height() > statsTop + 50) {
      counterDone = true;
      $('.stat-num').each(function () { animateCounter($(this)); });
    }
  }, 600);

  /* ── Feature tabs (Why section) ── */
  $('.why-tab').on('click', function () {
    const tabId = $(this).data('tab');
    $('.why-tab').removeClass('active');
    $(this).addClass('active');
    $('.tab-pane').removeClass('active');
    $('#' + tabId).addClass('active');

    // Animate margin bar when HPP tab opens
    if (tabId === 'hpp-tab') {
      setTimeout(animateMarginBar, 300);
    }
  });

  function animateMarginBar() {
    $('#margin-bar').css('width', '0%');
    setTimeout(function () {
      $('#margin-bar').css('width', '35%');
      $('#margin-val').text('35%');
    }, 100);
  }
  // Init margin bar on page load
  setTimeout(animateMarginBar, 1000);

  /* ── Price toggle (Harga) ── */
  const prices = {
    monthly: { starter: '0', pro: '49.000', business: '99.000' },
    yearly:  { starter: '0', pro: '39.200', business: '79.200' }
  };

  $('#billingToggle').on('change', function () {
    const isYearly = $(this).is(':checked');
    $('.amount').each(function () {
      const monthly = $(this).data('monthly');
      const yearly  = $(this).data('yearly');
      const val = isYearly ? yearly : monthly;
      $(this).text(parseInt(val).toLocaleString('id-ID'));
    });
  });

  /* ── Feature price calculator (keunggulan tab) ── */
  const featurePrices = { t1: 49000, t2: 30000, t3: 20000, t4: 25000 };
  function calcPrice() {
    let total = 0;
    ['t1','t2','t3','t4'].forEach(function (id) {
      if ($('#' + id).is(':checked')) total += featurePrices[id];
    });
    $('#price-total').text('Rp ' + total.toLocaleString('id-ID'));
  }
  $('#t1, #t2, #t3, #t4').on('change', calcPrice);
  calcPrice();

  /* ── Testimoni slider ── */
  let currentTesti = 0;
  const testiCards = $('.testi-card');
  const dotBtns    = $('.dot-btn');

  function showTesti(idx) {
    testiCards.removeClass('active');
    dotBtns.removeClass('active');
    $(testiCards[idx]).addClass('active');
    $(dotBtns[idx]).addClass('active');
    currentTesti = idx;
  }

  dotBtns.on('click', function () {
    showTesti(parseInt($(this).data('idx')));
  });

  // Auto-rotate
  setInterval(function () {
    const next = (currentTesti + 1) % testiCards.length;
    showTesti(next);
  }, 5000);

  /* ── Footer newsletter ── */
  $('#footerSubscribe').on('click', function () {
    const email = $('#footerEmail').val().trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
      $('#footerEmail').css('border-color', '#EF4444');
      return;
    }
    $('#footerEmail').css('border-color', '');
    $('#subscribeMsg').text('✅ Berhasil! Kami akan menghubungimu segera.').fadeIn(300);
    $('#footerEmail').val('');
    // Simulate PHP call
    $.ajax({
      url: 'subscribe.php',
      method: 'POST',
      data: { email: email },
      error: function () { /* silent – static demo */ }
    });
  });

  /* ── Bar chart hover ── */
  $('.bar').on('mouseenter', function () {
    $(this).css('opacity', '.75');
  }).on('mouseleave', function () {
    $(this).css('opacity', '1');
  });

  /* ── Feature card hover lift with jQuery ── */
  $('.feature-card').on('mouseenter', function () {
    $(this).find('.feature-icon-wrap').css('transform', 'scale(1.08)').css('transition', 'transform .3s');
  }).on('mouseleave', function () {
    $(this).find('.feature-icon-wrap').css('transform', 'scale(1)');
  });

  /* ── Mockup animated bars ── */
  function animateBars() {
    $('.bar').each(function () {
      const h = $(this).attr('style').match(/height:([^%]+)%/);
      if (h) {
        const target = parseFloat(h[1]);
        $(this).css('height', '0%');
        setTimeout(() => {
          $(this).animate({ height: target + '%' }, 800);
        }, Math.random() * 400);
      }
    });
  }
  setTimeout(animateBars, 500);

  /* ── Navbar active link highlight on scroll ── */
  $(window).on('scroll', function () {
    const scrollPos = $(window).scrollTop() + 100;
    $('section[id]').each(function () {
      const id  = $(this).attr('id');
      const top = $(this).offset().top;
      const bot = top + $(this).outerHeight();
      if (scrollPos >= top && scrollPos < bot) {
        $('.nav-link').removeClass('active-nav');
        $(`.nav-link[href="#${id}"]`).addClass('active-nav');
      }
    });
  });

  /* ── Active nav link style ── */
  $('<style>.active-nav { color: var(--primary) !important; font-weight: 700; }</style>').appendTo('head');

});
