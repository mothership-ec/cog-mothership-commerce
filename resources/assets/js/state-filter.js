$(function() {
	$('select[data-state-filter-country-selector]').filteredSelect({
		monitor: function() {
			return $($(this).attr('data-state-filter-country-selector'));
		},
		onChange: function() {
			if ($(this).children('option[value!=""]').length == 0) {
				$(this).parent().hide();
			} else {
				$(this).parent().show();
			}
		}
	});
});