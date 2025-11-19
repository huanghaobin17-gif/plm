layui.define(function(exports){
    layui.use(['layer', 'form', 'table'], function () {
        var layer = layui.layer
            , form = layui.form;

        //初始化
        form.render();


        //新增操作
        form.on('submit(OutSideApprove)', function (data) {
            var params = data.field;
            if (!params.remark) {
                layer.msg('请补充审批意见', {icon: 2});
                return false;
            }
            if(params.is_adopt == 1){
                tips = '确认通过审核？';
            }else{
                tips = '确认驳回申请？';
            }
            layer.confirm(tips, {icon: 3, title: $(this).html()}, function (index) {
                submit($,params,assetOutSideApproveUrl);
                return false;
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
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [url +'?path=' + path + '&filename=' + name]
            });
            return false;
        });

    });
    exports('controller/assets/outside/assetOutSideApprove', {});
});
