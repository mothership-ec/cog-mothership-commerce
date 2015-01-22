window.variantKeys = ["Colour", "Size"];
window.tokenSeparators = [","];
window.units = [];


function Unit(variants) {
	this.variants = variants ? variants : {};
}

// Updates the units from the fields
function updateUnits() {
	
	/*
	|
	|  Update unit objects.
	|
	*/
	var variants = {};

	// create variant array
	$('.variant-field').each(function(e) {
		var type = $(this).children('.variant-type').first().val();
		var arr  = $(this).children('.value').first().select2("val");
		arr.forEach(function(val) {
			if(!type) return;

			if(!(type in variants)) {
				variants[type] = [];
			}
			
			variants[type].push(val);
		});
	});

	// in no variants, then empty the Units array
	if (Object.keys(variants).length === 0) {
		window.units = [];
	} else{	
		var units = [new Unit];

		// Construct the unit array
		Object.keys(variants).forEach(function(key){
			var values = variants[key],
				newUnits = [],
				baseUnit;

			while(baseUnit = units.shift()) {
				values.forEach(function(val) {
					var variants = {};
					$.extend(variants, baseUnit.variants);
					var unit = new Unit(variants);

					unit.variants[key] = val;
					newUnits.push(unit);
				});
			}
			units = newUnits;
		});

		// Update units
		window.units = units;
	}

	/*
	|
	|  Update document.
	|
	*/
	var e_Units    = $('#product_create_units');
	e_Units.empty();
	
	// unit form prototype
	var unitFormProto    = $(e_Units.data('prototype'));
	for(var i = 0; i < window.units.length; ++i) {

		var unit       = window.units[i],
			unitForm   = unitFormProto.clone(),
			elem       = $()
			titleParts = [],
			// variant form prototype
			variantFormProto = $($('#product_create_units___unit___variants', unitFormProto).data('prototype'))
		;

		Object.keys(unit.variants).forEach(function(key){
			// build title
			var val = unit.variants[key];
			titleParts.push('<span class="' + key + '">'+val+'</span>');

			// build form
			var varForm = $(variantFormProto.html()
				.replace(/__variant__label__/g, '')
				.replace(/__variant__/g, key)
			).children().remove('label');

			$('#product_create_units___unit___variants', unitForm).append(varForm);

			$('#product_create_units___unit___variants_'+key+'_key', unitForm).val(key);
			$('#product_create_units___unit___variants_'+key+'_value', unitForm).val(unit.variants[key]);

		});

		// replace names given by symfony
		unitForm = $(unitForm.html(
			unitForm.html()
				.replace(/__unit__label__/g, titleParts.join(' - '))
				.replace(/__unit__/g, i)
			)
		);

		// elem.append(unitForm);
		var removeBtn = $('<a href="#" class="button remove button-cancel"></a>');
		removeBtn.click(function(){
			$(this).parent('.field-wrap').remove();
		});
		unitForm.append(removeBtn);

		e_Units.append(unitForm);
	}
}

var currentKey = 0;
function addVariantField() {
	var field = $($('#variant-field-container').data('prototype').replace(/__key__/g, ++currentKey));

	var value = $(".value", field).select2({
		tags: [],
		formatNoMatches: function () {
			return 'Add variant value';
		},
		tokenSeparators: window.tokenSeparators
	});

	value.on("change", updateUnits);
	
	$(".remove", field).click(function() {
		$(this).parent(".variant-field").remove();
		updateUnits();
	});

	$(".variant-options").append(field);
}


$(function(){
	// seems that slect2 is funny about tabbing
	$('.variants').on('keydown', '.variant-type', function(e) {
		var keyCode = e.keyCode || e.which;

		if (e.keyCode === 9 && !e.shiftKey) {
			$(this).nextAll('.select2-container').find('.select2-input').focus();
			e.preventDefault();
		}
	});

	$('.price-field').on('change', function() {
		var _this = $(this);
		_this.closest('.currency-units').find('.price-field').each(function() {
			if ($(this).val() == '') {
				$(this).val(_this.val());
			}
		});
	});

	$('body').on('click.modal', '[data-modal-open]', function() {
		$('.save.button').attr('disabled', 'disabled');

		$(document).bind('ajaxComplete.createProduct', function() {
			$('.save.button').removeAttr('disabled');
			$(document).unbind('ajaxComplete.createProduct');
		});
	});

	$('body').on('click.modal', '[data-modal-close]', function() {
		$('#product_create_name').val('');
		$('[id*=product_create_units_]').val('');
	});

	addVariantField();
	$(".add-variant").on("click", addVariantField);
});