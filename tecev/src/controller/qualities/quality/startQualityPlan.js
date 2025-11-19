layui.define(function(exports){
    layui.use(['layer', 'form', 'element','table','laydate'], function() {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table;
        //先更新页面部分需要提前渲染的控件
        form.render();

        var thisbody=$('#LAY-Qualities-Quality-startQualityPlan');
        var assid=thisbody.find('input[name="assid"]').val();
        var assnum=thisbody.find('input[name="assnum"]').val();
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
                            $('#setDetail').html('');
                            $('#setDetail').html(data);
                            form.render();
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

        //监听启用按钮
        form.on('submit(start)',function (data) {
            var params = {};
            params = data.field;
            params.type = 'save';
            submit($,params,'startQualityPlan');
            return false;
        });

        //点击查看模板
        thisbody.find('.showTemplate').click(function () {
            var url = $(this).attr('data-url');
            top.layer.open({
                type: 2,
                title:'【'+thisbody.find('.tpname').html()+'】模板预览',
                area: ['75%', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [url]
            });
            return false;
        });

        //点击设置模板
        thisbody.find('.settingTemplate').click(function () {
            var flag = 1;
            top.layer.open({
                id: 'settingTemplates',
                type: 2,
                title: '设定模板【' + thisbody.find('.assetsName').html() + '】',
                area: ['75%', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [$(this).attr('data-url')+'?assid='+assid+'&assnum='+assnum],
                end: function () {
                    if(flag){
                        location.reload();
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
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
                            title: '【'+params.planName+'】模板预览',
                            area: ['900px', '100%'],
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
    });
    exports('controller/qualities/quality/startQualityPlan', {});
});
