/**
 * @file
 * hero_slideshow functionality.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * TODO: Please move anything relater to hero slideshow to any place via drupal best practice.
   *
   * @type {{attach: Drupal.behaviors.heroSlideshow.attach}}
   */
  Drupal.behaviors.heroSlideshow = {
    attach: function (context, settings) {

      if ($(window).width() < 767) {
        return;
      }

      var $video_wrapper = $('.video-slideshow .video-wrapper', context);

      $video_wrapper.each(function () {
        var $this = $(this),
          $body = $('body'),
          mp4 = $this.data('mp4'),
          webm = $this.data('webm'),
          video = $.parseHTML('<video height="auto" class="hero-video" muted="muted" loop="loop" autoplay="autoplay"></video>');

        if (typeof mp4 !== 'undefined') {
          $(video).append('<source src="' + mp4 + '" type="video/mp4" />');
          $body.addClass('processed-video-script');
        }

        if (typeof webm !== 'undefined') {
          $(video).append('<source src="' + webm + '" type="video/webm" />');
          $body.addClass('processed-video-script');
        }

        if (typeof mp4 !== 'undefined' || typeof webm !== 'undefined') {
          $this.addClass('video-loaded');
        }

        $this.append(video);
        $body.addClass('processed-video');
      });

    }
  };

  // Paragraph Nav.
  // Cache selectors.
  var lastId,
    topMenu = $("#top-menu"),
    topMenuHeight = topMenu.outerHeight() + 15,
    // All list items.
    menuItems = topMenu.find("a"),
    // Anchors corresponding to menu items.
    scrollItems = menuItems.map(function () {
      var item = $($(this).attr("href"));
      if (item.length) {
        return item;
      }
    });

  // Bind click handler to menu items
  // so we can get a fancy scroll animation.
  menuItems.click(function (e) {
    var href = $(this).attr("href"),
      offsetTop = href === "#" ? 0 : $(href).offset().top - topMenuHeight - 75;
    $('html, body').stop().animate({
      scrollTop: offsetTop
    }, 600);
    e.preventDefault();
  });

  // Bind to scroll.
  $(window).scroll(function () {
    // Get container scroll position.
    var fromTop = $(this).scrollTop() + topMenuHeight;

    // Get id of current scroll item.
    var cur = scrollItems.map(function () {
      if ($(this).offset().top < fromTop + 150)
        return this;
    });
    // Get the id of the current element.
    cur = cur[cur.length - 1];
    var id = cur && cur.length ? cur[0].id : "";

    if (lastId !== id) {
      lastId = id;
      // Set/remove active class.
      menuItems
        .parent().removeClass("active").end().filter("[href='#" + id + "']")
        .parent().addClass("active");
    }
  });

})(jQuery, Drupal, drupalSettings);
