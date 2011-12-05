$(document).ready(function(){
	$(".dump_r").on("click", ".excol", function(){
		$(this).parent().toggleClass("expanded collapsed");
	});
});