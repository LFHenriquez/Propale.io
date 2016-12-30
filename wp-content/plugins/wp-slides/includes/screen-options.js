jQuery(function($){
	$(".custom-options-panel input[type='checkbox']").change(function() {
	    $name = $(this).attr('name');
	    $('.wp-list-table .' + $name).toggleClass('hidden');
	});
});