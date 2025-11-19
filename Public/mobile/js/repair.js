/**
 * Created by chouxiaoya on 2017/3/18.
 */
$(function () {
    $("#hisReNextPage").click(function () {
        var p = $(this).attr('title');
        var status = $(this).attr('data-status');
        var url = '/index.php/Home/Repair/engineerMore/p/' + p + '/status/' + status;
        $.ajax({
            type: "GET",
            url: url,
            //返回数据的格式
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
            beforeSend: function () {
                $("#loadRe").show();
            },
            //成功返回之后调用的函数
            success: function (msg) {
                if (msg) {
                    $("#hisRe").append(msg);
                    $("#loadRe")[0].style.display = 'none';
                    var p = parseInt($("#hisReNextPage").attr('title'));
                    p = p + 1;
                    $("#hisReNextPage").attr('title', p);
                } else {
                    $("#hisReNextPage").html('没有更多数据了');
                    $("#loadRe")[0].style.display = 'none';
                }
            },
            //调用出错执行的函数
            error: function () {
                //请求出错处理
                alert('服务器繁忙');
            }
        });
    });

    $("#nowReNextPage").click(function () {
        var p = $(this).attr('title');
        var status = $(this).attr('data-status');
        var url = '/index.php/Home/Repair/engineerMoreNow/p/' + p + '/status/' + status;
        $.ajax({
            type: "GET",
            url: url,
            //返回数据的格式
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
            beforeSend: function () {
                $("#loadNowRe").show();
            },
            //成功返回之后调用的函数
            success: function (msg) {
                if (msg) {
                    $("#nowRe").append(msg);
                    $("#loadNowRe")[0].style.display = 'none';
                    var p = parseInt($("#nowReNextPage").attr('title'));
                    p = p + 1;
                    $("#nowReNextPage").attr('title', p);
                } else {
                    $("#nowReNextPage").html('没有更多数据了');
                    $("#loadNowRe")[0].style.display = 'none';
                }
            },
            //调用出错执行的函数
            error: function () {
                //请求出错处理
                alert('服务器繁忙');
            }
        });
    });
});
function getDetail(repid) {
    window.location.href = "/index.php/Home/Repair/faultDetail/repid/" + repid;
}