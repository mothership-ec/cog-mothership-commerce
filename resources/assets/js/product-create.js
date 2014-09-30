window.variantKeys = ["Colour", "Size"];
window.tokenSeparators = [","];
window.units = [];


function Unit(variants) {
	this.variants = variants ? variants : {};
}

function updateUnits() {
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

	var units = [new Unit];

	// Construct the unit array
	Object.keys(variants).forEach(function(key){
		var values = variants[key];
		
		units.forEach(function(baseUnit, idx) {

			values.forEach(function(val) {
				unit = new Unit(baseUnit.variants);
				unit.variants[key] = val;
				units.push(unit);
			});

			delete units[idx]; 
		});
	}); 


	for(var i = 0; i < units.length; ++i) {
		var e_Units = $('#units');
		var unit = units[i];
		var elem = $('<ul><li></li></ul>');
		e_Units.empty();

		var titleParts = [];
		Object.keys(unit.variants).forEach(function(key){
			var val = unit.variants[key];

			titleParts.push('<span class="' + key + '">'+val+'</span>');
		});

		elem.append(titleParts.join(' - '));

		elem.append(
			'<fieldset>' +
				'<label for="sku">SKU</label>' +
				'<input type="text" id="sku" value="TONIC-1">' +
			'</fieldset>' +
			'<fieldset>' +
				'<label for="stock">Stock value</label>' +
				'<input type="number" id="stock" placeholder="0">' +
			'</fieldset>'
			);
		var removeBtn = $('<a href="#" class="button remove button-cancel"></a>')
		e_Units.append(elem);
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
		$(this).parent(".variant-type").remove();
	});

	$(".variant-options").append(field);
}

$(function(){
	addVariantField();
	$(".add-variant").on("click", addVariantField);

});