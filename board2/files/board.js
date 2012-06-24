$(function(){
    $("#button_area li").hover(
	function(){$(this).addClass("selected");},
	function(){$(this).removeClass("selected");}
    );
    $("#button_area li").click(
	function(){
	    var id = $(this).attr("id");
	    if(id == "btn_newthread"){
	    }else if(id == "btn_list"){
		location.href = ""
	    }
	}
    );
});

function check_form(){
    var type = document.post_form.type.value;
    var flag = true;
    if(type == "new"){
	if(flag && document.post_form.title.value == "") flag = false;
    }
    if(flag && document.post_form.author.value == "") flag = false;
    if(flag && document.post_form.contents.value == "") flag = false; 
    if(flag) return true;
    else{
	alert("全ての項目を入力してください");
	return false;
    }
}