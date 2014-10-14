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
		var type = $(this).children('.type').first().val();
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

function addVariantField() {
	var field = $(
			'<div class="field required variant-field">'+
				'<input type="text" class="type" placeholder="Variant">' +
				'<input type="hidden" class="value" style="width: 90%;" tabindex="-1" placeholder="Value" data-main class="select2-offscreen">' +
				'<a href="#" class="button remove button-cancel"></a>' +
			'</div>'
			);

	var value = $(".value", field).select2({
		tags: [""], 
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
	$('a.create-similar').on('click', function(e) {
		$('.complete').addClass('hidden');

		e.preventDefault();
	});

	$('form').on('ms_product_create_submit', function() {
		var self = $(this);

		$.ajax({
			action : self.attr('action'),
			method : self.attr('method'),
			data   : self.serialize(),
			success: function(data) {
				$('.complete').removeClass('hidden');
			},
			error  : function(data) {
			}
		});
	});

	addVariantField();
	$(".add-variant").on("click", addVariantField);

	$('form#ms-create-form').on('submit', function(e) {
		$(this).trigger('ms_product_create_submit');

		e.preventDefault();
	});
});