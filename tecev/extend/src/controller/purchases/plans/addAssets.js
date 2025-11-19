layui.define(function(exports){
    layui.use(['form','laydate','formSelects','suggest'], function () {
        var form = layui.form,laydate = layui.laydate,suggest = layui.suggest,formSelects = layui.formSelects;
        suggest.search();
        formSelects.render('brand', selectParams(1));
        formSelects.btns('brand',selectParams(2));

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

        //监听提交
        form.on('submit(addAssets)', function(data){
            var asname = $('input[name="asname"]').val();
            if(asname == 0){
                layer.msg("设备名称错误，请重新选择！",{icon : 2,time:1000});
                return false;
            }
            params = data.field;
            params.action = 'addAssets';
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
                        layer.msg(data.msg,{icon : 2,time:2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2,time:2000});
                }
            });
            return false;
        });
        var hospital_id = $('input[name="hospital_id"]').val();
        $('#addDicAssets').on('click',function () {
            var url = $(this).attr('data-url');
            var flag = 1;
            top.layer.open({
                id: 'addDictorys',
                type: 2,
                title: '添加设备字典',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['680px', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    if (flag) {
                        location.reload();
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });
        /*
         /选择设备字典名称
         */
        $("#dist_assets_name").bsSuggest(
            returnDicAssets()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="unit"]').val(data.unit);
            $('input[name="asname"]').val('1');
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });

    });
    exports('controller/purchases/plans/addAssets', {});
});