layui.define(function(exports){

    layui.use(['form','upload'], function(){
        var form = layui.form,upload = layui.upload;

        //报告logo
        uploadFile = upload.render({
            elem: '#uploadRepairReportLogo'  //绑定元素
            , url: admin_name+'/AssetsSetting/assetsModuleSetting' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg' //格式 用|分隔
            , method: 'POST'
            , data: {otherAction: 'uploadReportLogo'}
            , done: function (res) {
                if (res.status === 1) {
                    setTimeout(function(){
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: admin_name+'/AssetsSetting/assetsModuleSetting',
                            data: {src:res.src},
                            dataType: "json",
                            async: true,
                            beforeSend:beforeSend,
                            success: function (data) {
                                if (data.status == 1) {
                                    layer.msg('上传医疗器械报告logo成功', {icon: 1}, 1000);
                                    setTimeout(function(){
                                        layui.index.render();
                                    },2000)
                                }else{
                                    layer.msg(data.msg,{icon : 2},1000);
                                }
                            },
                            error: function () {
                                layer.msg("网络访问失败",{icon : 2},1000);
                            },
                            complete:complete
                        });
                    },1000)
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function () {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        //预览文件
        $("#showRepairReportLogo").click(function(){
            var path = $(this).siblings('input[name="file_url"]').val();
            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: '医疗器械报告logo查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url + '?path=' + path]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
            return false;
        });

        var thisbody=$('#LAY-BaseSetting-ModuleSetting-module');
        //资产
        if(parseInt(thisbody.find("input[name='assets[assets_open][is_open]']:checked").val()) === 0){
            $(".assets input[type='checkbox']").attr("disabled","disabled");
            $(".assets input[type='text']").attr("disabled","disabled");
        }
        //维修
        if(parseInt(thisbody.find("input[name='repair[repair_open][is_open]']:checked").val()) === 0){
            $(".repair input[type='checkbox']").attr("disabled","disabled");
            $(".repair input[type='text']").attr("disabled","disabled");
            $(".repair input[type='radio']").attr("disabled","disabled");
            $(".repair button").attr("disabled","disabled");
        }
        //巡查
        if(parseInt(thisbody.find("input[name='patrol[patrol_open][is_open]']:checked").val()) === 0){
            $(".patrol input[type='checkbox']").attr("disabled","disabled");
            $(".patrol input[type='text']").attr("disabled","disabled");
            $(".patrol textarea").attr("disabled","disabled");
        }
        //质控
        if(parseInt(thisbody.find("input[name='qualities[qualities_open][is_open]']:checked").val()) === 0){
            $(".qualities input[type='checkbox']").attr("disabled","disabled");
            $(".qualities input[type='text']").attr("disabled","disabled");
            $(".qualities input[type='radio']").attr("disabled","disabled");
        }
        //微信公众号
        if(parseInt(thisbody.find("input[name='wx_setting[wx_setting_open][open]']:checked").val()) === 0){
            $(".wx input[type='radio']").attr("disabled","disabled");
        }
        //渲染页面元素
        form.render();

        form.verify({
            uptime: function(value,item){
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '到场时间首尾不能出现下划线\'_\'';
                }
                if (value > 720){
                    return '到场时间最大不能超过720分钟';
                }
                var a = /^[0-9]*[1-9][0-9]*$/;
                if (!a.test(value)){
                    return '到场时间只能为正整数';
                }
            },
            life_assets_remind: function(value,item){
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '重提醒时间首尾不能出现下划线\'_\'';
                }
                if (value > 2880){
                    return '重提醒时间最大不能超过2880分钟';
                }
                if(value != 0){
                    var a = /^[0-9]*[1-9][0-9]*$/;
                    if (!a.test(value)){
                        return '重提醒时间只能为正整数';
                    }
                }
            },
            normal_assets_remind: function(value,item){
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '重提醒时间首尾不能出现下划线\'_\'';
                }
                if (value > 2880){
                    return '重提醒时间最大不能超过2880分钟';
                }
                if(value != 0){
                    var a = /^[0-9]*[1-9][0-9]*$/;
                    if (!a.test(value)){
                        return '重提醒时间只能为正整数';
                    }
                }
            },
            parts_warning: function(value,item){
                var a = /^[0-9]*[1-9][0-9]*$/;
                if (!a.test(value)){
                    return '配件预警提示数量正整数';
                }
            },

            hospital_name:function (value,item) {
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '医院名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '医院名称不能全为数字';
                }
                if (value.length>14) {
                    return '医院名称不能长于14字符';
                }
            },
            contacts:function (value,item) {
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '医院联系人首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '医院联系人不能全为数字';
                }
            },
            amount_limit: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!/^(([1-9]\d*)|\d)(\.\d{1,3})?$/.test(value)) {
                    return '请输入正确的金额';
                }
            },
            address:function (value,item) {
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '医院详细地址首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '医院详细地址不能全为数字';
                }
            }
        });
        form.on('radio(target_chart_depart_repair)', function(data){
            if(data.value == '1'){
                $('.target_chart_depart_repair').show();
            }else{
                $('.target_chart_depart_repair').hide();
            }
        });
        form.on('radio(target_chart_assets_add)', function(data){
            if(data.value == '1'){
                $('.target_chart_assets_add').show();
            }else{
                $('.target_chart_assets_add').hide();
            }
        });
        form.on('radio(target_chart_assets_scrap)', function(data){
            if(data.value == '1'){
                $('.target_chart_assets_scrap').show();
            }else{
                $('.target_chart_assets_scrap').hide();
            }
        });
        form.on('radio(target_chart_assets_purchases)', function(data){
            if(data.value == '1'){
                $('.target_chart_assets_purchases').show();
            }else{
                $('.target_chart_assets_purchases').hide();
            }
        });
        form.on('radio(target_chart_assets_benefit)', function(data){
            if(data.value == '1'){
                $('.target_chart_assets_benefit').show();
            }else{
                $('.target_chart_assets_benefit').hide();
            }
        });
        form.on('radio(target_chart_assets_adverse)', function(data){
            if(data.value == '1'){
                $('.target_chart_assets_adverse').show();
            }else{
                $('.target_chart_assets_adverse').hide();
            }
        });
        form.on('radio(hospital_status)', function(status){
            if (status.value == 1){
                $(".hospital input[type='checkbox']").removeAttr("disabled");
                $(".hospital input[type='text']").removeAttr("readonly")
            }else{
                $(".hospital input[type='checkbox']").attr("disabled","true");
                $(".hospital input[type='text']").attr("readonly","true");
            }
        });
        form.on('radio(assets_open)', function(status){
            if (status.value == 1){
                $(".assets input[type='checkbox']").removeAttr("disabled");
                $(".assets input[type='text']").removeAttr("disabled");
                form.render();
            }else{
                $(".assets input[type='checkbox']").attr("disabled","disabled");
                $(".assets input[type='text']").attr("disabled","disabled");
            }
        });
        form.on('radio(repair_open)', function(status){
            if (status.value == 1){
                $(".repair input[type='checkbox']").removeAttr("disabled");
                $(".repair input[type='radio']").removeAttr("disabled");
                $(".repair input[type='text']").removeAttr("disabled");
                $(".repair textarea").removeAttr("disabled");
                $(".repair button").removeAttr("disabled");
                form.render();
            }else{
                $(".repair input[type='checkbox']").attr("disabled","disabled");
                $(".repair input[type='radio']").attr("disabled","disabled");
                $(".repair input[type='text']").attr("disabled","disabled");
                $(".repair textarea").attr("disabled","disabled");
                $(".repair button").attr("disabled","disabled");
            }
        });
        form.on('radio(repair_water)', function(data){
            if(data.value == '2'){
                $('.watermark').show();
            }else{
                $('.watermark').hide();
            }
        });
        form.on('radio(patrol_open)', function(status){
            if (status.value == 1){
                $(".patrol input[type='checkbox']").removeAttr("disabled");
                $("#level").removeAttr("disabled");
                $(".patrol input[type='text']").removeAttr("disabled");
                $(".patrol textarea").removeAttr("disabled")
            }else{
                $(".patrol input[type='checkbox']").attr("disabled","disabled");
                $("#level").attr("disabled","disabled");
                $(".patrol input[type='text']").attr("disabled","disabled");
                $(".patrol textarea").attr("disabled","disabled");
            }
        });
        form.on('radio(qualities_open)', function(status){
            if (status.value == 1){
                $(".qualities input[type='radio']").removeAttr("disabled");
                $(".qualities input[type='text']").removeAttr("disabled");
                form.render();
            }else{
                $(".qualities input[type='radio']").attr("disabled","disabled");
                $(".qualities input[type='text']").attr("disabled","disabled");
            }
        });
        form.on('radio(wx_setting_open)', function(status){
            if (status.value == 1){
                $(".wx input[type='radio']").removeAttr("disabled");
                form.render();
            }else{console.log(status);
                $(".wx input[type='radio']").attr("disabled","disabled");
            }
        });
        form.on('radio(statis_status)', function(status){
            if (status.value == 1){
                $(".statis input[type='checkbox']").removeAttr("disabled");
            }else{
                $(".statis input[type='checkbox']").attr("disabled","true");
            }
        });
        //系统生成字段与必填的勾选关系
        form.on('checkbox(repair_date)', function(data){
            if (data.elem.checked){
                $("input[name='repair[repair_required][repair_date]']:checkbox").prop('checked',false);
                $("input[name='repair[repair_required][repair_date]']:checkbox").prop('disabled',true);
            }else {
                $("input[name='repair[repair_required][repair_date]']:checkbox").prop('checked',true);
                $("input[name='repair[repair_required][repair_date]']:checkbox").prop('disabled',true);
            }
            form.render('checkbox');
        });
        form.on('checkbox(repair_person)', function(data){
            if (data.elem.checked){
                $("input[name='repair[repair_required][repair_person]']:checkbox").prop('checked',false);
                $("input[name='repair[repair_required][repair_person]']:checkbox").prop('disabled',true);
            }else {
                $("input[name='repair[repair_required][repair_person]']:checkbox").prop('checked',true);
                $("input[name='repair[repair_required][repair_person]']:checkbox").prop('disabled',true);
            }
            form.render('checkbox');
        });
        form.on('checkbox(repair_phone)', function(data){
            if (data.elem.checked){
                $("input[name='repair[repair_required][repair_phone]']:checkbox").prop('checked',false);
                $("input[name='repair[repair_required][repair_phone]']:checkbox").prop('disabled',true);
            }else {
                $("input[name='repair[repair_required][repair_phone]']:checkbox").prop('checked',true);
                $("input[name='repair[repair_required][repair_phone]']:checkbox").prop('disabled',true);
            }
            form.render('checkbox');
        });
        form.on('checkbox(service_date)', function(data){
            if (data.elem.checked){
                $("input[name='repair[repair_required][service_date]']:checkbox").prop('checked',false);
                $("input[name='repair[repair_required][service_date]']:checkbox").prop('disabled',true);
            }else {
                $("input[name='repair[repair_required][service_date]']:checkbox").prop('checked',true);
                $("input[name='repair[repair_required][service_date]']:checkbox").prop('disabled',true);
            }
            form.render('checkbox');
        });
        form.on('checkbox(service_working)', function(data){
            if (data.elem.checked){
                $("input[name='repair[repair_required][service_working]']:checkbox").prop('checked',false);
                $("input[name='repair[repair_required][service_working]']:checkbox").prop('disabled',true);
            }else {
                $("input[name='repair[repair_required][service_working]']:checkbox").prop('checked',true);
                $("input[name='repair[repair_required][service_working]']:checkbox").prop('disabled',true);
            }
            form.render('checkbox');
        });
        form.on('checkbox(repair_check)', function(data){
            if (data.elem.checked){
                $("input[name='repair[repair_required][repair_check]']:checkbox").prop('checked',false);
                $("input[name='repair[repair_required][repair_check]']:checkbox").prop('disabled',true);
            }else {
                $("input[name='repair[repair_required][repair_check]']:checkbox").prop('checked',true);
                $("input[name='repair[repair_required][repair_check]']:checkbox").prop('disabled',true);
            }
            form.render('checkbox');
        });
        //监听提交
        form.on('submit(saveSetting)', function (data) {
            params = data.field;
            if(data.field['patrol[priceRange]']){
                var priceRange = data.field['patrol[priceRange]'].split("\n");
                params.priceRange = priceRange.join(',');
            }
            submit($,params,params.action);
            return false;
        });

        //新增分院
        $('.addhospital').click(function () {
            var html = '<tr>\n' +
                '<td style="text-align: center">分院</td>\n' +
                '<input type="hidden" name="hospital_id[]" value="-1"/>\n' +
                '<input type="hidden" name="is_general_hospital[]" value="0"/>\n' +
                '<td>\n' +
                '<div class="layui-input-inline">\n' +
                '<input type="text" name="hospital_name[]" value="" lay-verify="required|hospital_name" placeholder="医院名称" autocomplete="off" class="layui-input">\n' +
                '</div>\n' +
                '</td>\n' +
                '<td>\n' +
                '<div class="layui-input-inline">\n' +
                '<input type="text" name="hospital_code[]" value="" lay-verify="required|hospital_code" autocomplete="off" placeholder="医院代码" class="layui-input">\n' +
                '</div>\n' +
                '</td>\n' +
                '<td>\n' +
                '<div class="layui-input-inline">\n' +
                '<input type="text" name="contacts[]" value="" lay-verify="required|contacts" autocomplete="off" placeholder="联系人" class="layui-input">\n' +
                '</div>\n' +
                '</td>\n' +
                '<td>\n' +
                '<div class="layui-input-inline">\n' +
                '<input type="text" name="phone[]" value="" lay-verify="required" autocomplete="off" placeholder="联系电话" class="layui-input">\n' +
                '</div>\n' +
                '</td>\n' +
                '<td>\n' +
                '<div class="layui-input-inline">\n' +
                '<input type="text" name="amount_limit[]" value="" lay-verify="required" autocomplete="off" placeholder="采购年限下限" class="layui-input">\n' +
                '</div>\n' +
                '</td>\n' +
                '<td>\n' +
                '<div class="layui-input-inline">\n' +
                '<input type="text" name="address[]" value="" lay-verify="required|address" autocomplete="off" placeholder="详细地址" class="layui-input">\n' +
                '</div>\n' +
                '</td>\n' +
                '</tr>';
            $('#module_hospitals').append(html);
        });
    });
    $(function () {
        var target_chart_depart_repair_open = $('input[name="target_setting[target_chart_depart_repair][is_open]"]:checked ').val();
        if (target_chart_depart_repair_open == '1') {
            $('.target_chart_depart_repair').show();
        }

        var target_chart_assets_add_open = $('input[name="target_setting[target_chart_assets_add][is_open]"]:checked ').val();
        if (target_chart_assets_add_open == '1') {
            $('.target_chart_assets_add').show();
        }

        var target_chart_assets_scrap_open = $('input[name="target_setting[target_chart_assets_scrap][is_open]"]:checked ').val();
        if (target_chart_assets_scrap_open == '1') {
            $('.target_chart_assets_scrap').show();
        }

        var target_chart_assets_purchases_open = $('input[name="target_setting[target_chart_assets_purchases][is_open]"]:checked ').val();
        if (target_chart_assets_purchases_open == '1') {
            $('.target_chart_assets_purchases').show();
        }

        var target_chart_assets_benefit_open = $('input[name="target_setting[target_chart_assets_benefit][is_open]"]:checked ').val();
        if (target_chart_assets_benefit_open == '1') {
            $('.target_chart_assets_benefit').show();
        }

        var target_chart_assets_adverse_open = $('input[name="target_setting[target_chart_assets_adverse][is_open]"]:checked ').val();
        if (target_chart_assets_adverse_open == '1') {
            $('.target_chart_assets_adverse').show();
        }
    });
    exports('basesetting/modulesetting/module', {});
});

