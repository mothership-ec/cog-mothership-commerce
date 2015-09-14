$(function() {

	// Product table JS setup
	var	showImages = $('.table-filter.products').data('show-images'),
		columns = getColumns(),
		dataTable = $('.table-filter.products').dataTable({
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
		aoColumns: columns
	});

	function getColumns() {
		columns = [
			{ type: "text" },
			null,
			{ type: "text" },
			{ type: "text" },
			{ type: "text" },
			null
		];

		if (!showImages) {
			columns.splice(1,1);
		}

		return columns;
	}

    // Hide and show columns when ajax slide happens
	var showCol = showImages ? 2 : 1;
	$('#main-slide').on('show.cp-livePane-slide', function(e, data) {
		$('.dataTables_length').hide();

		for (var i = 0; i < dataTable.fnSettings().aoColumns.length; ++i) {
			if (i!==showCol) {
				dataTable.fnSetColumnVis( i, false);
			}
		}
		$('table.products, .dataTables_paginate, .dataTables_info').animate({width: "18%"}, data.speed);

		$('.dataTables_filter').hide();

		$('.dataTables_info').css({ paddingBottom: '50px'});

	});

	$('#main-slide').on('hide.cp-livePane-slide', function(e, data) {
		$('.dataTables_length').show();
		for (var i = 0; i < dataTable.fnSettings().aoColumns.length; ++i) {
			if (i!==showCol) {
				dataTable.fnSetColumnVis( i, true);
			}
		}
		$('table.products, .dataTables_paginate, .dataTables_info').animate({width: "100%"}, data.speed);

		$('.dataTables_filter').show();

		$('.dataTables_info').css({ paddingBottom: '20px'});
	});
});