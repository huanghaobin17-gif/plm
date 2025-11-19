layui.define(function(exports){
    layui.use(['layer', 'form', 'table'], function () {
        var layer = layui.layer
            , form = layui.form;

        //初始化
        form.render();


        //新增操作
        form.on('submit(submit)', function (data) {
            var proposer = $("input[name='proposer']").val();
            var proposer_time = $("input[name='proposer_time']").val();
            params = data.field;
            if (!params.remark) {
                layer.msg('请补充审批意见', {icon: 2});
                return false;
            }
            params.proposer = proposer;
            params.proposer_time = proposer_time;
            if(params.is_adopt == 1){
                tips = '确认通过审核？'; 
            }else{
                tips = '确认驳回申请？';
            }
            layer.confirm(tips, {icon: 3, title: $(this).html()}, function (index) {
                submit($,params,'departApproveBorrow');
                return false;
            });
            return false;
        });
    });
    exports('controller/assets/borrow/approveBorrow', {});
});