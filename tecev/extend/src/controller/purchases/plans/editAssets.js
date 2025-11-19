layui.define(function(exports){
    layui.use(['form','laydate','formSelects','suggest'], function () {
        var form = layui.form,laydate = layui.laydate,formSelects = layui.formSelects,suggest = layui.suggest;
        formSelects.render('brand', selectParams(1));
        formSelects.btns('brand',selectParams(2));
        suggest.search();
        form.render();
        form.verify({
            assets_name: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '设备名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '设备名称不能全为数字';
                }
            },
            market_price: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!/^(([1-9]\d*)|\d)(\.\d{1,3})?$/.test(value)) {
                    return '请输入正确的金额';
                }
            }
        });
        formSelects.value('brand', brand);
        form.val("editAssets", {
            "assets_name": assets_name
            ,"unit": unit
            ,"nums": nums
            ,"market_price": market_price
            ,"is_import": is_import
            ,"buy_type": buy_type
        });

        //编辑设备
        form.on('submit(editAssets)', function(data){
            params = data.field;
            params.action = 'editAssets';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'departReport',
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status == 1) {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
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

        /*
         /选择设备字典名称
         */
        $("#dic_assets_sel").bsSuggest(
            returnDicAssets()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="unit"]').val(data.unit);
            $('input[name="assets_name"]').val(data.assets);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });
    });
    exports('controller/purchases/plans/editAssets', {});
});