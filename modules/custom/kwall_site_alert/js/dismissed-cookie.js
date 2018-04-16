(function ($, Drupal) {
  Drupal.behaviors.kwallSiteAlert = {
    attach: function (context, drupalSettings) {
			if (!$('body').hasClass('dismissed-processed')) {
				$('body').addClass('dismissed-processed')
	      // Since the key is updated every time the configuration form is saved,
	      // we can ensure users don't miss newly added or changed alerts.
	      var total = 5;
				for (i = 1; i <= total; i++) {
		      var keynum = "key" + i;
		      var cookieName = 'Drupal.visitor.kwall_site_alert_dismissed' + i;
		      var key = drupalSettings.kwall_site_alert.dismissedCookie[keynum];
		      // Only show the alert if dismiss button has not been clicked. The
		      // element is hidden by default in order to prevent it from momentarily
		      // flickering onscreen. We are not working with Bootstrap's 'hide' class
		      // since we don't want a dependency on Bootstrap.
		      //actually we are removing altogether so it doesn't mess with slideshow
		      if ($.cookie(cookieName) == key) {
		        $('.bs-site-alert' + i).remove();
		      }
		      
		      // Set the cookie value when dismiss button is clicked.
		      if (!$('.bs-site-alert' + i).hasClass('dismissed-processed')) {
			      $('.bs-site-alert' + i).addClass('dismissed-processed').attr('data-key',key).attr('data-cookie',cookieName);
			      $('.bs-site-alert' + i).on('close.bs.alert', function () {
				      $.cookie($(this).attr('data-cookie'), $(this).attr('data-key'), { path: drupalSettings.path.baseUrl });
				      $('#kwall-alerts').slick('slickRemove', $('.slick-slide').index(this) - 1);
				    });
		      }
		    }
		    $(document).ready(function(){
			    if(!$('#kwall-alerts').hasClass('slick-processed')) {
				  	$('#kwall-alerts').addClass('slick-processed');
				  	$('#kwall-alerts').slick({
						  infinite: true,
						  speed: 300,
						  slidesToShow: 1,
						  adaptiveHeight: true
						});
					}
				});
	    }
    }
  }
})(jQuery, Drupal);
