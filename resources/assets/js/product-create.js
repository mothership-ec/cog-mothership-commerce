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

	var units = [], max = Object.keys(variants).length - 1; 
	
	function cartesian(u, i) {
		var key = Object.keys(vars)[i];

		for (var j=0, l=variants[key].length; j<l; ++j){
			var a = vars.clone();
			var unit = new Unit();

			a.push();
		}
	}

	console.log(units);
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