jQuery(function () {
    var $ = jQuery; 
    $(window).load(function () {
        $('.form').css({height: $(window).height()-60});
        $('textarea').css({height: $(window).height()-130});
    });
});
