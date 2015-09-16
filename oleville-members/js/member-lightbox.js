(function($) {

$(document).ready(function() {
    
    $('.member_profile').click(function(e){ //on add input button click
        e.preventDefault();
		var data = {
			'action': 'get_member_info',
			'cid': $(this).data('target'),     // We pass php values differently!
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		$.post(myAjax.ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			mem = jQuery.parseJSON( response );
			$('.member-name').html(mem.name);
			$('.member-picture').attr('src', mem.featured_image);
			$('.member-content').html(mem.content);
			$.colorbox({href:"#member-lightbox", inline:true, width:"40%", speed:"850", opacity:".5"});       
		});
				
    });
		
	function getParameterByName(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
	
	
});

})( jQuery );