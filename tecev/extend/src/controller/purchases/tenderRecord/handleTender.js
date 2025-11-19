layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;

        //监听提交
        form.on('submit(saveHandle)', function(data){
            var params = {}
            params = data.field;
            params.action = 'finalSave';
            submit($,params,handleTender);
            return false;
        });

        $("#addFacDetail").on('click',function () {
            var record_id = $('input[name="record_id"]').val();
            var flag = 1;
            top.layer.open({
                type: 2,
                title: '新增招标明细',
                area: ['720px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [handleTender+'?action=addFacDetail&id='+record_id],
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
        $(".delFac").on('click',function () {
            var params = {};
            var target = $(this);
            params.action = 'delFac';
            params.detail_id = target.attr('data-id');
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'handleTender',
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status == 1) {
                        target.parent().parent().remove();
                        if($('.supplierList').find('.supdetail').length == 0){
                            var tr = '<tr class="no-data">\n' +
                                '                    <td colspan="9" style="text-align: center !important;">暂无数据</td>\n' +
                                '                </tr>';
                            $('.supplierList').append(tr);
                        }
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
        });
        //下载模板文件
        //提示是否下载/预览
        $(document).on('click', '.downFile', function () {
            var params = {};
            params.path = $(this).data('path');
            params.filename = $(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
        });
    });
    exports('controller/purchases/tenderRecord/handleTender', {});
});
