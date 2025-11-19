layui.define(function(exports){
    layui.use(['element', 'layer'], function(){
        var element = layui.element;
        var layer = layui.layer;
        //预览
        $(document).on('click','.showFile',function () {
            var path= $(this).data('path');
            var name=$(this).data('name');
            var url=admin_name+'/Tool/showFile';
            top.layer.open({
                type: 2,
                title: name + '相关文件查看',
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['70%', '100%'],
                closeBtn: 1,
                content: [url +'?path=' + path + '&filename=' + name]
            });
            return false;
        });
        //下载
        $(document).on('click','.downFile',function () {
            var params={};
            params.path= $(this).data('path');
            params.filename=$(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data:params,
                method:'POST'
            });
            return false;
        });
        $(document).on('click','.delAssets',function () {
            layer.confirm('确定移除该设备？', {icon: 3, title: '移除设备'}, function (index) {
                var params = {};
                var url = $("input[name='url']").val();
                params['id'] = $("input[name='id']").val();
                params['action'] = 'delBoxAssets';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: params,
                    //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                    beforeSend: function () {
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1},function () {
                                window.location.reload();
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                    },
                    //调用出错执行的函数
                    error: function () {
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 5});
                    },
                    complete: function () {
                        layer.closeAll('loading');
                    }
                });
                layer.close(index);
            });
        });
    });
    exports('controller/archives/box/showBox', {});
});