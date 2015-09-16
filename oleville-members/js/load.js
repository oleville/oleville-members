(function ($) {
	$(document).ready(function () {
			// None of the options are set
			alert('firing');
			$("#members").smoothDivScroll({
				autoScrollingMode: "onStart",
				manualContinuousScrolling: true,
			});
		});
});