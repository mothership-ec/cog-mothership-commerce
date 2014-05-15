$(function() {
	$('input[name="product_barcodes[type]"]:radio').on('change.toggle', function()
	{
		var self = $(this),
			target = $('table[data-toggle]'),
			toggle = self.val() == 'automatic';
		if (toggle) {
			target.slideUp();
		} else {
			target.slideDown();
		}
		if (toggle) {
			target.find(':input').each(function() {
				var t = $(this);
				if (t.attr('required')) {
					t.removeAttr('required').attr('data-toggle-required', true);
				}
			});
		} else {
			target.find(':input').each(function() {
				var t = $(this);
				if (t.attr('data-toggle-required')) {
					t.removeAttr('data-toggle-required').attr('required', true);
				}
			});
		}
	}).trigger('change.toggle');
});