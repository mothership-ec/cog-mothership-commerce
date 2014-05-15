$(function() {
	$('*[data-toggle-manual]').hide();
	$('input[name="product_barcodes[type]"]:radio').on('change.toggle', function()
	{
		var self = $(this),
			manual = $('*[data-toggle-manual]'),
			automatic = $('*[data-toggle-automatic]'),
			toggle = self.val() == 'automatic';
		if (toggle) {
			manual.hide();
			automatic.show();
		} else {
			manual.show();
			automatic.hide();
		}
	});

});