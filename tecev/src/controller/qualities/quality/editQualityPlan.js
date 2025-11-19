layui.define(function(exports){
    layui.use(['layer', 'form', 'element','table','laydate'], function() {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table;
        //先更新页面部分需要提前渲染的控件
        form.render();

        //录入时间元素渲染
        laydate.render({
            elem: '#date' //指定元素
            ,min: startdate
        });
        //监听周期按钮
        form.on('switch(switchTest)', function(data){
            var params = {};
            if (data.elem.checked==true) {
                 $('#cycle').removeAttr("readonly");
            }else{
                 $('#cycle').attr("readonly","readonly")
            }
            return false;
        });
        //监听质控模板选项
        form.on('select(templates)', function(data){
            var params = {};
            params.type = 'getTemplatesSetting';
            params.tid = data.value; //得到被选中的值
            if(!params.tid){
                $('input[name="model"]').val('');
                $('input[name="serialnum"]').val('');
                $('input[name="num"]').val('');
                return false;
            }
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'startQualityPlan.html',
                data: params,
                dataType: "html",
                //beforeSend:beforeSend,
                success: function (data) {
                    if (data) {
                        $('#setDetail').html('');
                        $('#setDetail').html(data);
                        form.render();
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
                //complete:complete
            });
        });

        //监听检测仪器选项
        form.on('select(instrument)', function(data){
            var params = {};
            params.type = 'getInstrument';
            params.qiid = data.value; //得到被选中的值
            if(!params.qiid){
                $('input[name="model"]').val('');
                $('input[name="serialnum"]').val('');
                $('input[name="num"]').val('');
                return false;
            }
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'startQualityPlan.html',
                data: params,
                dataType: "json",
                //beforeSend:beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        $('input[name="model"]').val(data.model);
                        $('input[name="serialnum"]').val(data.productid);
                        $('input[name="num"]').val(data.metering_num);
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                //complete:complete
            });
        });

        //监听预览按钮
        form.on('submit(scan)',function (data) {
            var params = {};
            params = data.field;
            var qsid = params.qsid;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'scanTemplate',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        top.layer.open({
                            type: 2,
                            title: '模板预览',
                            area: ['60%', '100%'],
                            offset: 'r',//弹窗位置固定在右边
                            anim: 2, //动画风格
                            scrollbar:false,
                            closeBtn: 1,
                            content: [admin_name+'/Quality/scanTemplate.html?qsid='+qsid]
                        });
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

        form.on('submit(edit)',function (data) {
            var params = {};
            params = data.field;
            params.type = 'update';
            submit($,params,'startQualityPlan');
            return false;
        });
    });
    exports('controller/qualities/quality/editQualityPlan', {});
});
