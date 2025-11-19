layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;
        form.val("showExpertReview", {
            "installation": installation
            ,"business": business
            ,"rationality": rationality
            ,"technical": technical
            ,"benefit": benefit
            ,"necessity": necessity
            ,"project_desc": project_desc

            ,"repair": repair
            ,"safety": safety
            ,"matching": matching
            ,"reliability": reliability
            ,"technical_desc": technical_desc
        });

        //下载附件
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
    exports('controller/purchases/tendering/showExpertReview', {});
});
