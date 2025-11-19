/**

 @Name：layuiAdmin 公共业务
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL

 */

layui.define(function (exports) {
    var $ = layui.$
        , layer = layui.layer
        , laytpl = layui.laytpl
        , setter = layui.setter
        , view = layui.view
        , admin = layui.admin

    //公共业务的逻辑处理可以写在此处，切换任何页面都会执行
    //……


    //退出
    admin.events.logout = function () {
        //执行退出接口
        $.ajax({
            type: "POST",
            dataType: "json",
            url: admin_name + '/Login/logout',
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
            beforeSend: function () {
                layer.msg('正在退出，请稍候...', {
                    icon: 16,
                    time: 14000,
                    shade: 0.01
                });
            },
            //成功返回之后调用的函数
            success: function (data) {
                if (data.status == 1) {
                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                        window.location.href = admin_name + "/Login/login";
                    });
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            },
            //调用出错执行的函数
            error: function () {
                //请求出错处理
                layer.msg('服务器繁忙', {icon: 2});
            },
            complete: function () {
                layer.closeAll('msg');
            }
        });
        // admin.ajax({
        //    url :
        //   ,type: 'post'
        //   ,data: {}
        //   ,succese: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
        //     //清空本地记录的 token，并跳转到登入页
        //   }
        // });
    };

    admin.events.change_hospital = function () {
        var target = $(this);
        var params = {};
        params.hosid = $(this).attr('data-hosid');
        $.ajax({
            type: "POST",
            data: params,
            dataType: "json",
            url: admin_name + '/Login/changeHospital',
            datatype: "json",
            //成功返回之后调用的函数
            success: function (data) {
                if (data.status == 1) {
                    //target.parent().parent().find('cite').html(data.hosname);
                    parent.location.reload();
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            },
            //调用出错执行的函数
            error: function () {
                //请求出错处理
                layer.msg('服务器繁忙', {icon: 2});
            }
        });
    };



    //对外暴露的接口
    exports('common', {});
});