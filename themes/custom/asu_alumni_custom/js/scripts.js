/**
 * @file
 * asu_alumni_custom functionality.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Equal height.
   */
  $.equalHeight = function (container) {
    var currentTallest = 0,
      currentRowStart = 0,
      rowDivs = [],
      $el,
      topPosition = 0;
    $(container).each(function () {

      $el = $(this);
      $($el).height('auto');
      var topPosition = $el.position().top;

      if (currentRowStart !== topPosition) {
        for (var currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
          rowDivs[currentDiv].height(currentTallest);
        }
        rowDivs.length = 0; // empty the array
        currentRowStart = topPosition;
        currentTallest = $el.height();
        rowDivs.push($el);
      } else {
        rowDivs.push($el);
        currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
      }
      for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
        rowDivs[currentDiv].height(currentTallest);
      }
    });
  };

  /**
   * Get real page size.
   *
   * @returns {*[]}
   */
  $.getPageSize = function () {
    var xScroll, yScroll, windowWidth, windowHeight, pageWidth, pageHeight;

    if (window.innerHeight && window.scrollMaxY) {
      xScroll = document.body.scrollWidth;
      yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight) { // All but Explorer Mac.
      xScroll = document.body.scrollWidth;
      yScroll = document.body.scrollHeight;
    } else if (document.documentElement && document.documentElement.scrollHeight > document.documentElement.offsetHeight) { // Explorer 6 strict mode.
      xScroll = document.documentElement.scrollWidth;
      yScroll = document.documentElement.scrollHeight;
    } else { // Explorer Mac... Would also work in Mozilla and Safari.
      xScroll = document.body.offsetWidth;
      yScroll = document.body.offsetHeight;
    }

    if (self.innerHeight) { // All except Explorer.
      windowWidth = self.innerWidth;
      windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode.
      windowWidth = document.documentElement.clientWidth;
      windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // Other Explorers.
      windowWidth = document.body.clientWidth;
      windowHeight = document.body.clientHeight;
    }

    // For small pages with total height less then height of the viewport.
    if (yScroll < windowHeight) {
      pageHeight = windowHeight;
    } else {
      pageHeight = yScroll;
    }

    // For small pages with total width less then width of the viewport.
    if (xScroll < windowWidth) {
      pageWidth = windowWidth;
    } else {
      pageWidth = xScroll;
    }

    return [pageWidth, pageHeight, windowWidth, windowHeight];
  };


  /**
   * Common tweaks for the theme.
   *
   * @type {{attach: Drupal.behaviors.commonTweaks.attach}}
   */
  Drupal.behaviors.commonTweaks = {
    attach: function (context, settings) {
      // var $exposed_forms = $('.views-exposed-form');

      // Destroy chosen for State field.
      // $exposed_forms.find('#edit-state').chosen('destroy');
    }
  };

  /**
   * Initialize Slick Slider.
   *
   * @type {{attach: Drupal.behaviors.slickSlider.attach}}
   */
  Drupal.behaviors.slickSlider = {
    attach: function (context, settings) {
      var $partner_slider = $('.partners-slick', context),
        $gallery_slider = $('.paragraph--type--gallery', context);

      if ($partner_slider.length) {
        // Prepare something.
        $partner_slider.find('img').removeClass('img-responsive');

        // There's at least one matching element.
        $partner_slider.slick({
          speed: 300,
          slidesToShow: 4,
          slidesToScroll: 1,
          appendArrows: '.views-slick',
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
                infinite: true
              }
            },
            {
              breakpoint: 967,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 1
              }
            },
            {
              breakpoint: 767,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1
              }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object.
          ]
        });
      }

      $(window).on('load', function () {
        $.equalHeight($partner_slider.find('.view-content'));
      });

      $(window).on('resize', function () {
        $.equalHeight($partner_slider.find('.view-content'));
      });


      if ($gallery_slider.length) {
        // Prepare something.
        $gallery_slider.find('img').removeClass('img-responsive');

        // There's at least one matching element.
        $gallery_slider.find('.field--item').slick({
          speed: 300,
          slidesToShow: 1,
          slidesToScroll: 1,
          appendArrows: '.views-slick'
        });
      }

    }

  };

  /**
   * Initialize FlexSlider for a node.
   *
   * @type {{attach: Drupal.behaviors.flexSlider.attach}}
   */
  Drupal.behaviors.flexSlider = {
    attach: function (context, settings) {
      var $slideshow = $('.image-slideshow');
      // var $hero_slideshow = $('.video-slideshow');

      // Flexslider attached for few content types only.
      if ($slideshow.length) {
        $slideshow.flexslider({
          animation: 'fade',
          animationLoop: true,
          controlNav: true
        });
      }

      // if ($hero_slideshow.length) {
      //   $hero_slideshow.flexslider({
      //     animation: 'fade',
      //     animationLoop: true,
      //     video: true,
      //     slideshow: true,
      //     before: function(){
      //       // Reload video.
      //       $('video').each(function() {
      //         $(this).get(0).load();
      //       });
      //     }
      //   });
      // }

    }
  };

  Drupal.behaviors.footerMega = {
    attach: function (context, settings) {
      $('h2.big-foot-head').unbind('click').click(function () {
        $(this).next().slideToggle();
      });
    }
  };

  /**
   * Makes the equal height for some elements.
   *
   * @type {{attach: Drupal.behaviors.equalHeight.attach}}
   */
  Drupal.behaviors.equalHeight = {
    attach: function (context, settings) {
      var $events_view = $('.field--name-field-events-view .list-wrapper'),
        $travel_view = $('.field--name-field-travel-view .list-wrapper'),
        $chapters_cta = $('.paragraph--type--chapters-cta .chapter-lists');

      if ($.getPageSize()[0] > 768) {
        $.equalHeight($events_view);
        $.equalHeight($travel_view);
        $.equalHeight($chapters_cta.find('.col-sm-3 > div'));
      }
      $(window).resize(function () {
        if ($.getPageSize()[0] > 768) {
          $.equalHeight($events_view);
          $.equalHeight($travel_view);
          $.equalHeight($chapters_cta.find('.col-sm-3 > div'));
        } else {
          $events_view.height('auto');
          $travel_view.height('auto');
          $chapters_cta.find('.col-sm-3 > div').height('auto');
        }
      });

    }
  };

  /**
   * Add background image to Instagram social block.
   *
   * @type {{attach: Drupal.behaviors.socialGrid.attach}}
   */
  Drupal.behaviors.socialGrid = {
    attach: function (context, settings) {
      var $instagram_img = $('.instagram-img');

      // FIXME: Use jQuery once().
      if ($instagram_img.find('.hidden').length && !$instagram_img.hasClass('insta-processed')) {
        var imgUrl = $('.instagram-img .hidden').html();
        $instagram_img.css({
          'background-image': 'url(' + imgUrl + ')',
          'background-size': 'cover',
          'background-position': 'center center'
        });
        $instagram_img.addClass('insta-processed');
      }

    }
  };

})(jQuery, Drupal, drupalSettings);
