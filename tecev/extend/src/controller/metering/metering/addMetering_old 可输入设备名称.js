layui.use(['layer', 'form', 'element', 'table', 'laydate'], function () {
    var addStyle=1;//默认是选择已入库的设备类型
    var layer = layui.layer, form = layui.form, laydate = layui.laydate;
    //先更新页面部分需要提前渲染的控件
    form.render();
    //日期初始化
    laydate.render({
        elem: '#next_date',
        festival: true,
        min: '1'
    });
    //选择科室
    form.on('select(department)', function (data) {
        var oldDepartmentid = $('input[name="oldDepartmentid"]');
        var value = Number(data.value);
        var falseAssetsSelect = $('#falseAssetsSelect');
        var div = $('#getAssetsTd').find('.input-group');
        var parent = $('#col-md-12');
        if (value === 0) {
            //修改的时候没选择数据
            falseAssetsSelect.show();
            div.html('');
            div.hide();
            return false;
        } else {
            falseAssetsSelect.hide();
            div.show();
            var intHtml = '<input type="text" readonly placeholder="请先补充设备名称" class="layui-input">';
            parent.html(intHtml);
        }
        if (Number(oldDepartmentid.val()) !== value) {
            var html = '<input type="text" class="form-control bsSuggest" id="addMeteringAssets" placeholder="请输入设备名称" name="assetsName">';
            html += '<div class="input-group-btn">';
            html += '<ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">';
            html += '</ul>';
            //插件问题 需要先删除原来的对象 重新插入 再执行初始化
            div.html('');
            div.html(html);
            $("#addMeteringAssets").bsSuggest(
                {
                    url: admin_name+'/Metering/addMetering?type=getAssets&departid=' + $('select[name="departid"] option:selected').val(),
                    effectiveFields: ["assnum", "assets"],
                    searchFields: ["assets"],
                    effectiveFieldsAlias: {assnum: "设备编号", assets: "设备名称"},
                    ignorecase: false,
                    showHeader: true,
                    listStyle: {
                        "max-height": "375px", "max-width": "500px",
                        "overflow": "auto", "width": "400px", "text-align": "center"
                    },
                    showBtn: false,     //不显示下拉按钮
                    delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                    idField: 'assid',
                    listAlign: 'left',
                    keyField: 'assets',
                    clearable: false
                }
            ).on('onSetSelectValue', function (e, keyword, data) {
                //点击事件
                parent.find('div').remove();
                var selectHtml = '<input type="text" id="onSetSelectValue" class="form-control" value="" placeholder="请输入查询关键字" >';
                parent.html(selectHtml);
                var params={};
                params.assets=data.assets;
                params.type='getSerialnum';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name+'/Metering/addMetering',
                    data: params,
                    dataType: "json",
                    beforeSend:beforeSend,
                    success: function (data) {
                        if(data.status===1){
                            $('input[name="unit"]').val(data.result.unit);
                            $('input[name="model"]').val(data.result.model);
                            $('input[name="factory"]').val(data.result.factory);
                            $('#onSetSelectValue').selectPage({
                                showField: 'serialnum',
                                keyField: 'serialnum',
                                data: data.result.assets,
                                multiple: true,
                                noResultClean: true
                            });
                        }else{
                            parent.html('');
                            var setHtml = '<input type="text" name="setSerialnum" placeholder="多台需英文分号(;)隔开" class="layui-input">';
                            parent.html(setHtml);
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败",{icon : 2},1000);
                    },
                    complete:complete
                });
            }).on('onUnsetSelectValue', function () {
                addStyle=2;
                parent.html('');
                var setHtml = '<input type="text" name="setSerialnum" placeholder="多台需英文分号(;)隔开" class="layui-input">';
                parent.html(setHtml);
            });
            oldDepartmentid.val(value);
        }
    });
    //重置按钮补充
    $('.layui-btn-primary').click(function () {
        var parent = $('#col-md-12');
        var falseAssetsSelect = $('#falseAssetsSelect');
        falseAssetsSelect.show();
        var div = $('#getAssetsTd').find('.input-group');
        div.hide();
        var intHtml = '<input type="text" readonly placeholder="请先补充设备名称" class="layui-input">';
        parent.html(intHtml);
    });
    //监听通知-接单步骤
    form.on('submit(add)', function (data) {
        var params=data.field;
        if(!params.departid){
            layer.msg('请选择科室', {icon: 2});
            return false;
        }
        if(!params.assetsName){
            layer.msg('请补充设备名称', {icon: 2});
            return false;
        }

        if(!check_num(params.count) || params.count<=0){
            layer.msg('请补充正确的设备数量', {icon: 2});
            return false;
        }
        if(!check_num(params.categorys)){
            layer.msg('请选择计量分类', {icon: 2});
            return false;
        }
        if(!params.next_date){
            layer.msg('请补充下次待检日期', {icon: 2});
            return false;
        }
        if(!check_num(params.remind_day) || params.remind_day<=0){
            layer.msg('请补充正确的提前提醒天数', {icon: 2});
            return false;
        }
        params.addStyle=addStyle;
        if(addStyle==1){
            if(!params.selectPage){
                addStyle=3;
            }
        }
        if(addStyle==2){
            if(!params.setSerialnum){
                addStyle=3;
            }
        }
        params.type='addMeteringPlan';
        console.log(params);
        var url=admin_name+'/Metering/addMetering';
        submit($,params,url);
        return false;
    });
    //新增分类
    $('#addMCategory').click(function () {
        layer.open({
            type: 1,
            title: '新增计量分类',
            area: ['450px', '200px'],
            offset: 'auto',
            shade: [0.8, '#393D49'],
            shadeClose:true,
            anim:5,
            resize:false,
            scrollbar:false,
            isOutAnim: true,
            closeBtn: 1,
            content: $('#addMCategoryBody'),
            end:function () {
                $('input[name="categorysTitle"]').val('');
            }
        });
    });
    //确认添加分类
    form.on('submit(addCategorys)', function (data) {
        if(!data.field.categorysTitle){
            layer.msg("请输入分类名称",{icon : 2},1000);
            return false;
        }
        var params={};
        params.categorys=data.field.categorysTitle;
        params.type='addCategorys';
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: admin_name+'/Metering/addMetering',
            data: params,
            dataType: "json",
            beforeSend:beforeSend,
            success: function (data) {
                if(data.status===1){
                    layer.msg(data.msg,{
                        icon: 1,
                        time: 1000
                    }, function(){
                        var html='';
                        $.each(data.result,function (key,value) {
                            html+='<option value="'+value.mrid+'">'+value.mcategory+'</option>';
                        });
                        $('select[name="categorys"]').html(html);
                        form.render('select');
                    });
                }
            },
            error: function () {
                layer.msg("网络访问失败",{icon : 2},1000);
            },
            complete:complete
        });
    });
});