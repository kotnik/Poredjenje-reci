
$(document).ready(function() {

  $.ajaxSetup ({
    cache: false
  });

  var ajax_load = "<img src='images/loader.gif' alt='loading...' />";

  //	load() functions
  var loadUrl = "ajaxload.php";

  $("a.rec").live('click', function() {
    var word_id = this.id;
    // FIXME: unlock for sets other that 1
    $("#words").html(ajax_load).load(loadUrl, "wordid=" + word_id + "&set=1");
  });

});
