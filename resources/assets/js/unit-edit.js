$(function() {
	$('#product-edit-form').on('click', '.barcode-edit', function() {
		var id = $(this).data('unit'),
			barcode = $(this).data('barcode')
		;

		$('#unit_barcode_unit').val(id);
		$('#unit_barcode_barcode').attr('placeholder', barcode);
	});
});