layui.define(function(exports){
    layui.use(['layer', 'form', 'element','laydate','table'], function(){
        var layer = layui.layer,form = layui.form,laydate = layui.laydate;

        //验收时间元素渲染
        laydate.render(dateConfig('#acceptdate'));
        /*
         /提交转科验收
         */
        $('#acceptance').bind('click',function(){
            var assid = $("input[name='assid']").val();
            var transnum = $("input[name='transnum']").val();
            var checkdate = $("input[name='checkdate']").val();
            var nowDate = getNowFormatDate();
            if(!checkdate){
                layer.msg('请选择验收时间',{icon:2});
                return false;
            }else if(nowDate > checkdate){
                layer.msg('验收时间不能小于当前时间',{icon:2});
                return false;
            }
            var res = $("input[name='acceptres']:checked").val();
            //验收意见
            var check = $("textarea[name='check']").val();
            var url = admin_name+'/Transfer/check';
            var param = {};
            param['assid'] = assid;
            param['transnum'] = transnum;
            param['checkdate'] = checkdate;
            param['res'] = res;
            param['check'] = check;
            $.post(url,param,function (data) {
                if(data.status != 1){
                    layer.msg(data.msg,{icon:2});
                    return false;
                }else{
                    CloseWin(data.msg);
                    return false;
                }
            });
        });

    });
    exports('controller/assets/transfer/check', {});
});






