window.variants = [];

$(function(){

	function getVariantMarkup() {

	}

	$("#colour").select2({
		tags:[""],
		tokenSeparators: [",", " ", ", "]
	});

	$("#size").select2({
		tags:["small", "medium", "large"],
		tokenSeparators: [",", " ", ", "]
	});


});