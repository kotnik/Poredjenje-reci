
$(document).ready(function() {

  $.ajaxSetup ({
    cache: false
  });

  var ajax_load = "<img src='images/loader.gif' alt='loading...' />";

  //	load() functions
  var loadUrl = "ajaxload.php";

  $("a.rec").live('click', function() {
    var word_id = this.id;
    var get_set = gup('set');
    $("#words").html(ajax_load).load(loadUrl, "wordid=" + word_id + "&set=" + get_set);
    $(window).resize();
  });

  $(window).resize();

});

$(window).resize(function(){
	$('#content').css({
		position:'absolute',
		left: ($(window).width() - $('#content').outerWidth())/2,
		top: ($(window).height() - $('#content').outerHeight())/2
	});

});

function gup(name) {
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec(window.location.href);
  if(results == null)
    return "";
  else
    return results[1];
}
