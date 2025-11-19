layui.define(function(exports){
    layui.use(['layer', 'form','suggest','formSelects'], function() {
        var form = layui.form,
            $ = layui.jquery,
            layer = layui.layer,
            formSelects = layui.formSelects,
            suggest = layui.suggest;
        //初始化搜索建议插件
        suggest.search();
        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));

        form.verify({
            parts: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/^\d+\d+\d$/.test(value)) {
                    return '配件名称不能全为数字';
                }
            },
            price: function (value) {
                if(value){
                    if (!check_price(value)) {
                        return '请输入正确的金额';
                    }
                }
            }
        });

        //监听提交
        form.on('submit(add)', function (data) {
            var params = data.field;
            if(params.supplier_id){
                params.supplier_name = $('select[name="supplier_id"]').find('option[value="' + params.supplier_id + '"]').html();
            }
            submit($,params,addPartsDicUrl);
            return false;
        });

        $('#addSupplier').on('click',function () {
            var url = $(this).attr('data-url');
            var flag = 1;
            top.layer.open({
                id: 'addSuppliers',
                type: 2,
                title: '添加厂商',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['750px', '100%'],
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

        //点击补充厂家
        //$(document).on('click', '#addSupplier1', function () {
        //    layer.open({
        //        id: 'addPartsDicAddSupplier',
        //        type: 1,
        //        title: '添加厂商',
        //        area: ['750px', '450px'],
        //        offset: 'auto',
        //        shade: [0.8, '#393D49'],
        //        shadeClose: true,
        //        anim: 5,
        //        resize: false,
        //        scrollbar: false,
        //        isOutAnim: true,
        //        closeBtn: 1,
        //        content: $('#addSuppliersDiv'),
        //        success: function (layero, index) {
        //            //复位操作
        //            resetAddSuppliers();
        //            form.on('submit(addSuppliers)', function (data) {
        //                var params = data.field;
        //                params.action = 'addSuppliers';
        //                $.ajax({
        //                    timeout: 5000,
        //                    type: "POST",
        //                    url: addPartsDicUrl,
        //                    data: params,
        //                    dataType: "json",
        //                    success: function (data) {
        //                        if (data.status === 1) {
        //                            $('input[name="sup_num"]').val(data.result.sup_num);
        //                            var arr = params.suppliers_type.split(',');
        //                            var html = '';
        //                            if ($.inArray('2', arr) >= 0) {
        //                                html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
        //                                $('select[name="supplier_id"]').append(html);
        //                                form.render();
        //                            }
        //                            layer.close(layer.index);
        //                            layer.msg('补充成功', {icon: 1, time: 3000});
        //                        } else {
        //                            layer.msg(data.msg, {icon: 2, time: 3000});
        //                        }
        //                    },
        //                    error: function () {
        //                        layer.msg('网络访问失败', {icon: 2, time: 3000});
        //                    }
        //                });
        //                return false;
        //            });
        //            form.render('select');
        //        }
        //    });
        //});

        //已选省份、城市
        var old_provinces = 0;
        var old_city = 0;
        //选择省份
        form.on('select(provinces)', function (data) {
            var provinces = parseInt(data.value);
            if (data.value) {
                if (provinces !== old_provinces) {
                    var params = {};
                    params.action = 'getCity';
                    params.provinceid = provinces;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: addPartsDicUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if (data.result.length > 0) {
                                    var html = '<option value="">请选择城市</option>';
                                    $('select[name="areas"]').html(html);
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.cityid + '">' + value.city + '</option>';
                                    });
                                    $('select[name="city"]').html(html);
                                } else {
                                    $('select[name="city"]').html('<option>/</option>');
                                    $('select[name="areas"]').html('<option>/</option>');
                                }
                                form.render();
                                old_provinces = provinces;

                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请选择省份</option>';
                //选择空名称 复位填充数据
                $('select[name="city"]').html(html);
                $('select[name="areas"]').html(html);
                form.render();
                old_provinces = 0;
            }
        });
        //选择城市
        form.on('select(city)', function (data) {
            var city = parseInt(data.value);
            if (data.value) {
                if (city !== old_city) {
                    var params = {};
                    params.action = 'getAreas';
                    params.cityid = city;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: addPartsDicUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if (data.result.length > 0) {
                                    var html = '<option value="">请选择区/城镇</option>';
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.areaid + '">' + value.area + '</option>';
                                    });
                                    $('select[name="areas"]').html(html);
                                } else {
                                    $('select[name="areas"]').html('<option value="">/</option>');
                                }
                                form.render();
                                old_city = city;
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请选择城市</option>';
                //选择空名称 复位填充数据
                $('select[name="areas"]').html(html);
                form.render();
                old_city = 0;
            }
        });


        //复位添加厂商Div from
        //function resetAddSuppliers() {
        //    $('input[name="sup_name"]').val('');
        //    formSelects.value('suppliers_type', []);
        //    $('input[name="salesman_name"]').val('');
        //    $('input[name="salesman_phone"]').val('');
        //    $('input[name="address"]').val('');
        //    $('select[name="provinces"]').val('');
        //    $('select[name="city"]').html('<option value="">请选择省份</option>');
        //    $('select[name="areas"]').html('<option value="">请选择城市</option>');
        //    form.render();
        //}


        $("#addPartsDicCategory").bsSuggest('init',
            returnDicCategory('parts')
        );

        $("#addPartsDicBrand").bsSuggest('init',
            returnDicBrand()
        );

    });
    exports('controller/basesetting/dictionary/addPartsDic', {});
});

