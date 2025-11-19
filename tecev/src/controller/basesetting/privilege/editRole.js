layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;
        form.verify({
            rolename: function(value){
                if(value.length < 2){
                    return '角色名称至少2个字符';
                }
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '角色名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '角色名称不能全为数字';
                }
            }
        });
        //监听提交
        form.on('submit(editRole)', function(data){
            var url = $(this).attr('data-url');
            params = data.field;
            $.ajax({
                type:"POST",
                url:url,
                data:params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend:function(){
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success:function(data){
                    layer.closeAll('loading');
                    if(data.status == 1){
                        CloseWin(data.msg,1,1);
                    }else{
                        CloseWin(data.msg,2,0);
                    }
                },
                //调用出错执行的函数
                error: function(){
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 2});
                }
            });
            return false;
        });
    });
    exports('controller/basesetting/privilege/editRole', {});
});