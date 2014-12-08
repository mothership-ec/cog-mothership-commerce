(function($) {
	$('[data-price-warn]').each(function() {
		var btn  = $(this),
			form = btn.closest('form'),
			content = $(btn.data('price-warn')),
			modal   = $(btn.data('modal'))
		;

		btn.on('click.price-warn', function(e) {
			var warn = false;

			$('.price-field', form).each(function() {
				if($(this).val() == 0) {
					warn = true;
				}
			});

			if(warn) {
				content.text(window.priceWarning);
				$('.form-submit', modal).on('click', function() {
					form.submit();
				});
			} else {
				form.submit();
				e.stopPropagation();
			}
		});
	});
})(jQuery);