layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;
        //监听提交
        form.on('submit(saveReview)', function(data){
            var url = expertReview;
            params = data.field;
            var target = $('input');
            var names = [];
            $.each(target,function (index,item) {
                if($.inArray($(item).attr('name'),names) == -1){
                    names.push($(item).attr('name'));
                }
            });
            $.each(names,function (index,item) {
                if(!params[item]){
                    layer.msg('请对所有项目进行评审！',{icon : 2,time:1500});
                    return false;
                }
            });
            submit($,params,url);
            return false;
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
    });
    exports('controller/purchases/tendering/expertReview', {});
});
