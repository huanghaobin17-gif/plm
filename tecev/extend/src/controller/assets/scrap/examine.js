layui.define(function(exports){
    layui.use(['form','laydate'], function(){
        var form = layui.form,laydate = layui.laydate;
        //提交报废审批
        form.on('submit(add)', function (data) {
            var proposer = $("input[name='proposer']").val();
            var proposer_time = $("input[name='proposer_time']").val();
            params = data.field;
            params.proposer = proposer;
            params.proposer_time = proposer_time;
            if(params.is_adopt == 1){
                tips = '确认通过审核？';
            }else{
                tips = '确认驳回申请？';
            }
            layer.confirm(tips, {icon: 3, title: $(this).html()}, function (index) {
                submit($,params,'examine');
                return false;
            });
        });

        //录入时间元素渲染
        laydate.render(dateConfig('#approve_time'));
    });
    exports('controller/assets/scrap/examine', {});
});

