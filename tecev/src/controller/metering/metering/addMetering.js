layui.define(function (exports) {
    layui.use(['layer', 'form', 'element', 'table', 'laydate', 'suggest', 'formSelects'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate, suggest = layui.suggest,
            formSelects = layui.formSelects;

        //初始化搜索建议插件
        suggest.search();

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
            var assetsSelect = $('#addMeteringAssets');
            // var serial = $('#serial');
            // var intHtml = '<input type="text" readonly placeholder="请先选择设备名称" class="layui-input">';
            if (value === 0) {
                var assetsInt = '<option value="">请先选择科室</option>';
                assetsSelect.html(assetsInt);
                form.render('select');
                // serial.html(intHtml);
                oldDepartmentid.val(value);
                return false;
            }
            if (Number(oldDepartmentid.val()) !== value) {
                var params = {};
                params.type = 'getAssets';
                params.departid = value;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name + '/Metering/addMetering',
                    data: params,
                    dataType: "json",
                    beforeSend: beforeSend,
                    success: function (data) {
                        if (data.status === 1) {
                            var html = '<option value="">请选择设备</option>';
                            $.each(data.result, function (key, value) {
                                html += '<option value="' + value.assets + '">' + value.assets + '</option>';
                            });
                            assetsSelect.html(html);
                            form.render('select');
                        } else {
                            var assetsInt = '<option value="">' + data.msg + '</option>';
                            assetsSelect.html(assetsInt);
                            form.render('select');
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2}, 1000);
                    },
                    complete: complete
                });
                // serial.html(intHtml);
            }
            oldDepartmentid.val(value);
            return false;
        });


        form.on('select(assets)', function (data) {
            var oldDepartmentid = $('input[name="oldDepartmentid"]');
            oldDepartmentid = Number(oldDepartmentid.val());
            var oldAssets = $('input[name="oldAssets"]');
            var value = data.value;
            // var serial = $('#serial');
            // var intHtml = '<input type="text" readonly placeholder="请先选择设备名称" class="layui-input">';
            if (value === '') {
                // serial.html(intHtml);
                $('input[name="unit"]').val('');
                $('input[name="model"]').val('');
                $('input[name="factory"]').val('');
                oldAssets.val(value);
                return false;
            }
            if (oldAssets.val() !== value) {
                // serial.find('div').remove();
                // var selectHtml = '<input type="text" id="onSetSelectValue" class="form-control" value="" placeholder="请输入查询关键字" >';
                // serial.html(selectHtml);
                var params = {};
                params.assets = value;
                params.departid = oldDepartmentid;
                params.type = 'getSerialnum';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name + '/Metering/addMetering',
                    data: params,
                    dataType: "json",
                    beforeSend: beforeSend,
                    success: function (data) {
                        if (data.status === 1) {
                            $('input[name="unit"]').val(data.result.unit);
                            $('input[name="model"]').val(data.result.model);
                            $('input[name="factory"]').val(data.result.factory);
                            var selectHtml = '';

                            $.each(data.result.assets, function (key, value) {
                                selectHtml += `<option value="` + value.serialnum + `">` + value.serialnum + `</option>`
                            })
                            console.log(selectHtml)
                            $('#selectPage').html(selectHtml)
                            formSelects.render('selectPage');
                            // $('#onSetSelectValue').selectPage({
                            //     showField: 'serialnum',
                            //     keyField: 'serialnum',
                            //     data: data.result.assets,
                            //     // noselect: data.result.noselect,
                            //     // multiple: true,
                            //     // noResultClean: true
                            // });

                        } else {
                            // var setHtml = '<input type="text" name="setSerialnum" placeholder="多台需英文分号(;)隔开" class="layui-input">';
                            // serial.html(setHtml);
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2}, 1000);
                    },
                    complete: complete
                });
            }
            oldAssets.val(value);
            return false;
        });


        //重置按钮补充
        // $('.layui-btn-primary').click(function () {
        //     var parent = $('#col-md-12');
        //     var falseAssetsSelect = $('#falseAssetsSelect');
        //     falseAssetsSelect.show();
        //     var div = $('#getAssetsTd').find('.input-group');
        //     div.hide();
        //     var intHtml = '<input type="text" readonly placeholder="请先选择设备名称" class="layui-input">';
        //     parent.html(intHtml);
        // });
        //新增操作
        form.on('submit(add)', function (data) {
            var params = data.field;
            if (!params.departid) {
                layer.msg('请选择科室', {icon: 2});
                return false;
            }
            if (!params.assetsName) {
                layer.msg('请补充设备名称', {icon: 2});
                return false;
            }

            if (!check_num(params.count) || params.count <= 0) {
                layer.msg('请补充正确的设备数量', {icon: 2});
                return false;
            }
            if (!check_num(params.categorys)) {
                layer.msg('请选择计量分类', {icon: 2});
                return false;
            }
            if (!params.next_date) {
                layer.msg('请补充下次待检日期', {icon: 2});
                return false;
            }
            if (!check_num(params.remind_day) || params.remind_day <= 0) {
                layer.msg('请补充正确的提前提醒天数', {icon: 2});
                return false;
            }
            var addStyle = 1;//默认是选择已入库的设备类型
            if (!params.selectPage) {
                addStyle = 2;
            }
            params.addStyle = addStyle;
            params.type = 'addMeteringPlan';
            var url = admin_name + '/Metering/addMetering';
            submit($, params, url);
            return false;
        });
        //新增分类
        $('#addMCategory').click(function () {
            layer.open({
                type: 1,
                title: '新增计量分类',
                area: ['450px', '300px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addMCategoryBody'),
                end: function () {
                    $('textarea[name="categorysTitle"]').val('');
                }
            });
        });
        //确认添加分类
        form.on('submit(addCategorys)', function (data) {
            if (!data.field.categorysTitle) {
                layer.msg("请输入分类名称", {icon: 2}, 1000);
                return false;
            }
            var categorysTitle = data.field.categorysTitle.split("\n");
            data.field.categorysTitle = '';
            $.each(categorysTitle, function (k, v) {
                if (v) {
                    data.field.categorysTitle += ',' + v;
                }
            });
            var params = {};
            params.categorys = data.field.categorysTitle;
            params.type = 'addCategorys';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name + '/Metering/addMetering',
                data: params,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {
                            icon: 1,
                            time: 1000
                        }, function () {
                            var html = '';
                            $.each(data.result, function (key, value) {
                                html += '<option value="' + value.mcid + '">' + value.mcategory + '</option>';
                            });
                            $('select[name="categorys"]').html(html);
                            form.render('select');
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete: complete
            });
            return false;
        });
    });
    $(document).on('click', '.sp_element_box', function () {
        $('.sp_control_box').hide();
    });
    exports('controller/metering/metering/addMetering', {});
});