$(function() {
	// Product table JS setup
	$('.table-filter.products').dataTable({
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
			null
		]
	});

	$(document).ready(function() {
		$('.table-filter').dataTable();
	});
});