layui.define(function(exports){
    layui.use(['form', 'laydate', 'formSelects'], function () {
        var form = layui.form,
            formSelects = layui.formSelects,
            laydate = layui.laydate;

        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));
        //报修时间元素渲染
        laydate.render({
            elem: '#partsInWareApplyAddtime' //指定元素
            ,max:now_date
        });
        //监听提交
        form.verify({
            addtime: function (value) {
                if (!value) {
                    return '请选择选择入库日期';
                }
            }
        });


        form.on('submit(addPartsInWareApply)', function (data) {
            var params = data.field;
            var parts_tr = $('.parts_tr');
            if (parts_tr.length === 0) {
                layer.msg('异常申请单', {icon: 2, time: 3000});
                return false;
            }
            var error = false;
            var msg = '';
            var parts = '', parts_model = '', manufacturer_name = '', manufacturer_id = '', brand = '', unit = '', sum = '',
                price = '', apply_sum = '', supplier_id = '', supplier_name = '';
            $.each(parts_tr, function (key, value) {
                var parts_val = $(this).find('input[name="parts"]').val();
                var parts_model_val = $(this).find('input[name="parts_model"]').val();
                var manufacturer_id_val = $(this).find('select[name="manufacturer_id"]').val();
                var supplier_id_val = $(this).find('select[name="supplier_id"]').val();
                var supplier_name_val = $(this).find('select[name="supplier_id"]').find('option[value="' + supplier_id_val + '"]').html();
                var manufacturer_name_val = $('select[name="manufacturer_id"]').find('option[value="' + manufacturer_id_val + '"]').html();
                var brand_val = $(this).find('select[name="brand"]').val();
                var unit_val = $(this).find('input[name="unit"]').val();
                var sum_val = $(this).find('input[name="sum"]').val();
                var price_val = $(this).find('input[name="price"]').val();
                var apply_sum_val = $(this).find('input[name="min_sum"]').val();
                if (!check_num(sum_val)) {
                    error = true;
                    msg = '配件：' + parts_val + ' 请输入合理的数量';
                    return false;
                } else {
                    if (parseInt(sum_val) < parseInt(apply_sum_val)) {
                        error = true;
                        msg = '采购配件：' + parts_val + ' 数量不能少于申请的数量' + apply_sum_val + ' ' + unit_val;
                        return false;
                    }
                }
                if (!check_price(price_val)) {
                    error = true;
                    msg = '配件：' + parts_val + ' 请输入合理的单价';
                    return false;
                }
                if (!supplier_id_val) {
                    error = true;
                        msg = '请选择供应商';
                        return false;
                }
                parts += parts_val + '|';
                supplier_id += supplier_id_val + '|';
                supplier_name += supplier_name_val + '|';
                parts_model += parts_model_val + '|';
                manufacturer_id += manufacturer_id_val + '|';
                if (!manufacturer_id_val) {
                    manufacturer_name += '|';
                } else {
                    manufacturer_name += manufacturer_name_val + '|';
                }
                brand += brand_val + '|';
                unit += unit_val + '|';
                sum += sum_val + '|';
                price += price_val + '|';
                apply_sum += apply_sum_val + '|';
            });
            if (error) {
                layer.msg(msg, {icon: 2, time: 3000});
                return false;
            }
            params.parts = parts;
            params.supplier_id = supplier_id;
            params.supplier_name = supplier_name;
            params.parts_model = parts_model;
            params.manufacturer_name = manufacturer_name;
            params.manufacturer_id = manufacturer_id;
            params.brand = brand;
            params.unit = unit;
            params.sum = sum;
            params.apply_sum = apply_sum;
            params.price = price;
            params.action = 'partsInWareApply';
            submit($, params, partsInWareApplyUrl);
            return false;
        });

        $("#addSupplier").on('click',function () {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'addSuppliers',
                type: 2,
                title: '添加厂商',
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['790px', '100%'],
                closeBtn: 1,
                content: [url],
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
        });


        //点击补充厂家
        // $(document).on('click', '#addSupplier', function () {
        //     layer.open({
        //         id: 'partsInWareAddSupplier',
        //         type: 1,
        //         title: '添加厂商',
        //         area: ['750px', '450px'],
        //         offset: 'auto',
        //         shade: [0.8, '#393D49'],
        //         shadeClose: true,
        //         anim: 5,
        //         resize: false,
        //         scrollbar: false,
        //         isOutAnim: true,
        //         closeBtn: 1,
        //         content: $('#addSuppliersDiv'),
        //         success: function (layero, index) {
        //             //复位操作
        //             resetAddSuppliers();
        //             form.on('submit(addSuppliers)', function (data) {
        //                 var params = data.field;
        //                 params.action = 'addSuppliers';
        //                 $.ajax({
        //                     timeout: 5000,
        //                     type: "POST",
        //                     url: partsInWareApplyUrl,
        //                     data: params,
        //                     dataType: "json",
        //                     success: function (data) {
        //                         if (data.status === 1) {
        //                             $('input[name="sup_num"]').val(data.result.sup_num);
        //                             var arr = params.suppliers_type.split(',');
        //                             var html = '';
        //                             if ($.inArray('2', arr) >= 0 || $.inArray('1', arr) >= 0) {
        //                                 html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
        //                                 $('select[name="supplier_id"]').append(html);
        //                                 form.render();
        //                             }
        //                             layer.close(layer.index);
        //                             layer.msg('补充成功', {icon: 1, time: 3000});
        //                         } else {
        //                             layer.msg(data.msg, {icon: 2, time: 3000});
        //                         }
        //                     },
        //                     error: function () {
        //                         layer.msg('网络访问失败', {icon: 2, time: 3000});
        //                     }
        //                 });
        //                 return false;
        //             });
        //             form.render('select');
        //         }
        //     });
        // });

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
                        url: partsInWareApplyUrl,
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
                        url: partsInWareApplyUrl,
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
        function resetAddSuppliers() {
            $('input[name="sup_name"]').val('');
            formSelects.value('suppliers_type', []);
            $('input[name="salesman_name"]').val('');
            $('input[name="salesman_phone"]').val('');
            $('input[name="address"]').val('');
            $('select[name="provinces"]').val('');
            $('select[name="city"]').html('<option value="">请选择省份</option>');
            $('select[name="areas"]').html('<option value="">请选择城市</option>');
            form.render();
        }


    });
    exports('controller/repair/repairParts/partsInWareApply', {});
});
