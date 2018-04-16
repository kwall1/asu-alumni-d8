//Initialize Flixslider
$(window).load(function() {
  $('#flexslider-1.flexslider').flexslider({
        animation: 'fade',
        animationLoop: true,
        controlNav: true,
        controlsContainer: ".flexslider .field-type-image"
  });
  $('#flexslider-2.flexslider').flexslider({
        animation: 'fade',
        animationLoop: true,
        controlNav: true
  });
  $('#flexslider-3.flexslider').flexslider({
        animation: 'fade',
        animationLoop: true,
        controlNav: true,
        controlsContainer:"#flexslider-3 .field-type-image",
  });
  
  //Initialize Slick Slider
  var $object = $('.slick-carosel-example');
  if($object.length) {
    // there's at least one matching element
    $('.slick-carosel-example').slick({
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
        infinite: true,
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
    // instead of a settings object
  ]
});

  }

});

//equal height

equalheight = function(container){

var currentTallest = 0,
     currentRowStart = 0,
     rowDivs = new Array(),
     $el,
     topPosition = 0;
 $(container).each(function() {

   $el = $(this);
   $($el).height('auto')
   topPostion = $el.position().top;

   if (currentRowStart != topPostion) {
     for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
       rowDivs[currentDiv].height(currentTallest);
     }
     rowDivs.length = 0; // empty the array
     currentRowStart = topPostion;
     currentTallest = $el.height();
     rowDivs.push($el);
   } else {
     rowDivs.push($el);
     currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
  }
   for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
     rowDivs[currentDiv].height(currentTallest);
   }
 });
}

$(window).load(function() {
  equalheight('.view-slideshow-example .view-content');
});


$(window).resize(function(){
  equalheight('.view-slideshow-example .view-content');
});
