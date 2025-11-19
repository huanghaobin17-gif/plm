layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form, $ = layui.jquery;
        var thisbody=$('#LAY-BaseSetting-SmsModule-smsSetting');
        //总开关起始状态
        if(parseInt(thisbody.find("input[name='setting_open[status]']:checked").val()) === 0){
            $(".total_setting input[type='radio']").attr("disabled","disabled");
            $(".total_setting input[type='text']").attr("disabled","disabled");
        }
        
        var arr = new Array();
        arr.push('Subsidiary');//附属设备分配
        arr.push('Purchases');//采购
        arr.push('Qualities');//质控
        arr.push('Metering');//计量
        arr.push('Scrap');//报废
        arr.push('Transfer');//转科
        arr.push('Outside');//外调
        arr.push('Borrow');//借调
        arr.push('Patrol');//巡查
        arr.push('Repair');//维修
        var type;
        for (var i = 0; i < arr.length; i++) {
            //获取当前按钮状态是否关闭，使对应的控件不可编辑
            if(parseInt(thisbody.find("input[name='"+arr[i]+"[status]']:checked").val()) === 0){
            $("."+arr[i].toLowerCase()+"_sms_setting input[type='radio']").attr("disabled","disabled");
            $("."+arr[i].toLowerCase()+"_sms_setting input[type='text']").attr("disabled","disabled");
            }
            //监听对应按钮的状态，根据按钮状态修改控件编辑状态
            form.on('radio('+arr[i].toLowerCase()+'_sms_open)', function(status){
            type = status.elem.name;
            type = type.replace('[status]','').toLowerCase();
            if (status.value == 1){
                $("."+type+"_sms_setting input[type='radio']").removeAttr("disabled");
                $("."+type+"_sms_setting input[type='text']").removeAttr("disabled");
            }else{
                $("."+type+"_sms_setting input[type='radio']").attr("disabled","disabled");
                $("."+type+"_sms_setting input[type='text']").attr("disabled","disabled");
            }
            form.render();
        });
        }
        form.render();
        //监听提交
        form.on('submit(setSmsSetting)', function (data) {
            params = data.field;
            submit($,params,smsSettingUrl);
            return false;
        });

        //总开关事件
        form.on('radio(total_open)', function(status){
            if (status.value == 1){
                $(".total_setting input[name='Repair[status]']").removeAttr("disabled");
                $(".total_setting input[name='Patrol[status]']").removeAttr("disabled");
                $(".total_setting input[name='Borrow[status]']").removeAttr("disabled");

                $(".total_setting input[name='Outside[status]']").removeAttr("disabled");
                $(".total_setting input[name='Transfer[status]']").removeAttr("disabled");
                $(".total_setting input[name='Scrap[status]']").removeAttr("disabled");

                $(".total_setting input[name='Metering[status]']").removeAttr("disabled");
                $(".total_setting input[name='Qualities[status]']").removeAttr("disabled");
                $(".total_setting input[name='Purchases[status]']").removeAttr("disabled");

                $(".total_setting input[name='Subsidiary[status]']").removeAttr("disabled");

                if($(".total_setting input[name='Repair[status]']:checked").val() == 1){
                    //原开启
                    $(".repair_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".repair_sms_setting input[type='text']").removeAttr("disabled");
                }
                if($(".total_setting input[name='Patrol[status]']:checked").val() == 1){
                    //原开启
                    $(".patrol_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".patrol_sms_setting input[type='text']").removeAttr("disabled");
                }

                if($(".total_setting input[name='Borrow[status]']:checked").val() == 1){
                    //原开启
                    $(".borrow_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".borrow_sms_setting input[type='text']").removeAttr("disabled");
                }
                if($(".total_setting input[name='Outside[status]']:checked").val() == 1){
                    //原开启
                    $(".outside_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".outside_sms_setting input[type='text']").removeAttr("disabled");
                }

                if($(".total_setting input[name='Transfer[status]']:checked").val() == 1){
                    //原开启
                    $(".transfer_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".transfer_sms_setting input[type='text']").removeAttr("disabled");
                }
                if($(".total_setting input[name='Scrap[status]']:checked").val() == 1){
                    //原开启
                    $(".scrap_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".scrap_sms_setting input[type='text']").removeAttr("disabled");
                }

                if($(".total_setting input[name='Metering[status]']:checked").val() == 1){
                    //原开启
                    $(".metering_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".metering_sms_setting input[type='text']").removeAttr("disabled");
                }
                if($(".total_setting input[name='Qualities[status]']:checked").val() == 1){
                    //原开启
                    $(".qualities_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".qualities_sms_setting input[type='text']").removeAttr("disabled");
                }

                if($(".total_setting input[name='Purchases[status]']:checked").val() == 1){
                    //原开启
                    $(".purchases_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".purchases_sms_setting input[type='text']").removeAttr("disabled");
                }
                if($(".total_setting input[name='Subsidiary[status]']:checked").val() == 1){
                    //原开启
                    $(".subsidiary_sms_setting input[type='radio']").removeAttr("disabled");
                    $(".subsidiary_sms_setting input[type='text']").removeAttr("disabled");
                }
            }else{
                $(".total_setting input[type='radio']").attr("disabled","disabled");
                $(".total_setting input[type='text']").attr("disabled","disabled");
            }
            form.render();
        });
    });
    exports('basesetting/smsModule/smsSetting', {});
});









