/**
 * Created by 邓锦龙 on 2017/8/10.
 */
$(function(){
    $('#showTooltips').on('click', function(){
        var username = $.trim($("input[name='username']").val());
        var password = $.trim($("input[name='password']").val());
        if(!username){
            showTips('请输入用户名');
        }
        if(!password){
            showTips('请输入密码');
        }
        var data = {};
        var url = '/index.php/Home/Login/Login/login.html';
        $.ajax({
            type:"POST",
            url:url,
            data:{
                username:username,
                password:password
            },
            //返回数据的格式
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
            beforeSend:function(){
                //$("#loadRe").show();
            },
            //成功返回之后调用的函数
            success:function(data){
                if(data.status == 1){

                }else{
                    if ($tooltips.css('display') != 'none') return;
                    // toptips的fixed, 如果有`animation`, `position: fixed`不生效
                    $('.page.cell').removeClass('slideIn');
                    $tooltips.html(data.msg);
                    $tooltips.css('display', 'block');
                    setTimeout(function () {
                        $tooltips.css('display', 'none');
                    }, 2000);
                }
            },
            //调用出错执行的函数
            error: function(){
                //请求出错处理
                alert('服务器繁忙');
            }
        });
    });
});
var $tooltips = $('.js_tooltips');
function showTips(msg){
    if ($tooltips.css('display') != 'none') return;
    // toptips的fixed, 如果有`animation`, `position: fixed`不生效
    $('.page.cell').removeClass('slideIn');
    $tooltips.html(msg);
    $tooltips.css('display', 'block');
    setTimeout(function () {
        $tooltips.css('display', 'none');
    }, 2000);
}
