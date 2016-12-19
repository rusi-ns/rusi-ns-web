(function ( $ ) {
	$.fn.aptabs = function( options ) {
		var tabs = $(this);
		$(this).children().removeClass('ap-active');
		$('.'+options.content_container+' > div').hide();
		$('.'+options.content_container+' > div').eq(options.active).show();
		$(this).children().eq(options.active).addClass('ap-active');
		$(this).children().click(function(){
			tabs.children().removeClass('ap-active');
			$('.'+options.content_container+' > div').hide();
			$('.'+options.content_container+' > div').eq($(this).index()).show();
			tabs.children().eq($(this).index()).addClass('ap-active');
			$.cookie('ap_current_tab', $(this).index());
		});
	};
}( jQuery ));
jQuery(document).ready(function() {
	var active_tab;
	if (typeof jQuery.cookie('ap_current_tab') === 'undefined'){
		active_tab = 0;
	} else {
		active_tab = jQuery.cookie('ap_current_tab');
	}
	jQuery( ".ap-tabs" ).aptabs( { active : active_tab, content_container : 'ap-tabs-content'} );
});