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
			can = jQuery.parseJSON( response );
			$('.member-name').html(can.name);
			$('.member-picture').attr('src', can.featured_image);
			$('.member-content').html(can.content);
			$.colorbox({href:"#member-lightbox", inline:true, width:"40%", speed:"850", opacity:".5"});       
		});
				
    });
	
	$('.write-in-other').click(function(e) {
		$.colorbox({html:"<div class='voting-messages warning'><i class='fa fa-warning'></i>Your Write In Must Have the Form: <br> 'John Doe' or <br> 'John Doe and James Smith'</div>", speed:"850", width:"400px", opacity:".5"})
	});
	
	if($('#voting-messages').length) {
			$.colorbox({href:"#voting-messages-wrapper", inline:true, speed:"850", width:"600px", opacity:".5"});
		}
	
	
	
    $('.writeins').click(function(e){ //on add input button click
        e.preventDefault();
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		var target = '#' + $(this).parent().parent().attr('id') + '-results';
		
		$(target).toggle("slow");
				
    });
	
	function getParameterByName(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
	
	
});

})( jQuery );