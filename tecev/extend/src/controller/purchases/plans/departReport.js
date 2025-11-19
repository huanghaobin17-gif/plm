layui.define(function(exports){
    layui.use(['form','formSelects','upload'], function(){
        var form = layui.form,formSelects = layui.formSelects,upload = layui.upload;
        form.render();
        $("#addAssets").on('click',function () {
            var flag = 1;
            top.layer.open({
                type: 2,
                title: '新增设备明细',
                area: ['700px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [departReport+'?action=addAssets&id='+$('input[name="plans_id"]').val()],
                end: function () {
                    if (flag) {
                        location.reload();
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
        });

        //修改设备明细
        $('.editAssets').on('click',function () {
            var flag = 1;
            top.layer.open({
                type: 2,
                title: '编辑设备明细',
                area: ['700px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [departReport+'?action=editAssets&assets_id='+$(this).attr('data-id')],
                end: function () {
                    if (flag) {
                        location.reload();
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });

        //删除设备明细
        $('.delAssets').on('click',function () {
            var params = {};
            params.action = 'delAssets';
            params.assets_id = $(this).attr('data-id');
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'departReport',
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status == 1) {
                        location.reload();
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
            return false;
        });

        //上传附件
        upload.render({
            elem: '#addAssetsFile'  //绑定元素
            , url: admin_name+'/PurchasePlans/departReport' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile',plans_id:$('input[name="plans_id"]').val()}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    location.reload();
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });

        //下载模板文件
        $("#dwFile").on('click',function () {
            var params = {};
            params.path = '/Public/dwfile/';
            params.filename = '科室申请附件模板.zip';
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
        });

        //下载设备附件
        $('.downFile').on('click',function () {
            var params = {};
            params.path = $(this).data('path');
            params.filename = $(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
            return false;
        });

        //删除附件
        $('.delFile').on('click',function () {
            var params = {};
            params.action = 'delFile';
            params.file_id = $(this).attr('data-id');
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'departReport',
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status == 1) {
                        location.reload();
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
            return false;
        });

        //正式保存
        form.on('submit(finalSave)', function(data){
            var params = data.field;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'departReport',
                data: params,
                dataType: "json",
                async: true,
                beforeSend:beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg,{icon : 1,time:1000},function () {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    }else{
                        layer.msg(data.msg,{icon : 2,time:1000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            return false;
        });

    });
    exports('controller/purchases/plans/departReport', {});
});