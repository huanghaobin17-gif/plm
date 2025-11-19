layui.define(function(exports){
    layui.use(['layer', 'form', 'element','laydate','table'], function(){
        var layer = layui.layer,form = layui.form,element = layui.element,laydate = layui.laydate,table = layui.table;


        //审核时间元素渲染
        laydate.render(dateConfig('#approvaldate'));

        /*
         /提交转科审核
         */
        $('#approval').bind('click',function(){
            var assid = $("input[name='assid']").val();
            var transnum = $("input[name='transnum']").val();
            var approvaldate = $("input[name='approvaldate']").val();
            var nowDate = getNowFormatDate();
            if(!approvaldate){
                layer.msg('请选择审核时间',{icon:2});
                return false;
            }else if(nowDate > approvaldate){
                layer.msg('审核时间不能小于当前时间',{icon:2});
                return false;
            }
            var res = $("input[name='approvalres']:checked").val();
            var tips = '';
            if(res == 1){
                tips = '确认通过审核？';
            }else{
                tips = '确认驳回申请？';
            }
            //审核意见
            var remark = $("textarea[name='remark']").val();
            var url = admin_name+"/Transfer/approval";
            var param = {};
            param['assid'] = assid;
            param['transnum'] = transnum;
            param['approvaldate'] = approvaldate;
            param['res'] = res;
            param['remark'] = remark;
            layer.confirm(tips, {icon: 3, title: $(this).html()}, function (index) {
                $.ajax({
                    timeout: 5000,
                    dataType: "json",
                    type:"POST",
                    url:url,
                    data:param,
                    async:false,
                    beforeSend:function(){
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success:function(data){
                        if(data.status == 1){
                            CloseWin(data.msg);
                        }else{
                            layer.msg(data.msg,{icon : 2,time:3000});
                        }
                    },
                    //调用出错执行的函数
                    error: function(){
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 2});
                    },
                    complete:function () {
                        layer.closeAll('loading');
                    }
                });
            });
        });
    });
    exports('controller/assets/transfer/approval', {});
});






