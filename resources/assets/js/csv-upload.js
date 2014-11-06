$(function() {
	$( ".csv-show.valid" ).click(function() {
	if ($('.valid .hide').is(":visible")) {
		$('.valid .hide').hide();
		$( '.csv-show.valid').text("Show all rows");
	} 
	else {
		$('.valid .hide').show();
		$( '.csv-show.valid').text("Hide rows");
	}
	});

	$( ".csv-show.invalid" ).click(function() {
	if ($('.invalid .hide').is(":visible")) {
		$('.invalid .hide').hide();
		$( '.csv-show.invalid').text("Show all rows");
	} 
	else {
		$('.invalid .hide').show();
		$( '.csv-show.invalid').text("Hide rows");
	}
	});

	$('#ms_csv_upload_confirm_create_pages').change(function() {
	  	if ($(this).is(':checked')) {
	    	$('.csv-preview .field-wrap').slideToggle();
	  	} else {
	    	$('.csv-preview .field-wrap').slideToggle();
	  	}
	});

});