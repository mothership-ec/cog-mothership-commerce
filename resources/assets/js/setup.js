$(function() {
	// Product table JS setup
	var dataTable = $('.table-filter.products').dataTable({
		iDisplayLength: 25,
		"oLanguage": {
			"sLengthMenu": 'Display <select>'+
			'<option value="25">25</option>'+
			'<option value="50">50</option>'+
			'<option value="100">100</option>'+
			'<option value="200">200</option>'+
			'<option value="-1">All</option>'+
			'</select> products',
		"sInfo": "Showing (_START_ to _END_) of _TOTAL_ Products"}
    }).columnFilter({
		aoColumns: [
			{ type: "text" },
			null,
			{ type: "text" },
			{ type: "text" },
			{ type: "text" },
			null
		]
	});


    // Hide and show columns when ajax slide happens
	var showCol = 2;
	$('#main-slide').on('ms-cp-livePane-show', function() {
		$('.dataTables_length').hide();

		for (var i = 0; i < dataTable.fnSettings().aoColumns.length; ++i) {
			if (i!==showCol) {
				dataTable.fnSetColumnVis( i, false);
			}
		}

		$('table, .dataTables_paginate, .dataTables_info').animate({width: "18%"});
	});

	$('#main-slide').on('ms-cp-livePane-hide', function() {
		$('.dataTables_length').show();
		for (var i = 0; i < dataTable.fnSettings().aoColumns.length; ++i) {
			if (i!==showCol) {
				dataTable.fnSetColumnVis( i, true);
			}
		}
		$('table, .dataTables_paginate, .dataTables_info').animate({width: "100%"});
	});
});