var spanObj = $(".fileList span");
if ($(window).width() >= 768) {
    spanObj.css('width', 'auto');
    spanObj.css('min-width', '15rem');
}
if ($(window).width() < 375) {
    var groupObj = $(".layui-btn-group");
    $.each(groupObj, function (k, v) {
        var thisAobj = $(v).find("a");
        if (thisAobj.length == 2) {
            $(v).siblings("span").css('width', 'auto');
            $(v).siblings("span").css('max-width', '11.5rem');
        } else {
            $(v).siblings("span").css('width', 'auto');
            $(v).siblings("span").css('max-width', '15rem');
        }
    });
}