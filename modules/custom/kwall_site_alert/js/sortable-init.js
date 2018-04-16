(function ($, Drupal) {
  Drupal.behaviors.sortable = {
    attach: function (context, drupalSettings) {
	  	if(!$('body').hasClass('slick-processed')) {
		  	$('body').addClass('slick-processed');
		  	var el = document.getElementById('bootstrap-site-alert-admin');
				var sortable = Sortable.create(el);
			}
		}
  }
})(jQuery, Drupal);
