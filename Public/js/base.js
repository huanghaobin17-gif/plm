$(function () {
    var statisticsClose = $("#statistics_2_status").attr("checked");
    if(statisticsClose == 'checked'){
        $(".statistics").attr("disabled", "disabled");
    }
    $("#statistics_2_status").click(function () {
        //关闭模块，选项变为不可选
        $(".statistics").attr("disabled", "disabled");
    });
    $("#statistics_1_status").click(function () {
        //关闭模块，选项变为不可选
        $(".statistics").removeAttr("disabled");
    });
});