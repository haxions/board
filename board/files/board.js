$(function(){
    $("#button_area li").hover(
	function(){$(this).addClass("selected");},
	function(){$(this).removeClass("selected");}
    );
});