layui.define(function(exports){
    layui.use(['form', 'laydate', 'table', 'suggest', 'formSelects', 'tablePlug'], function () {
        var form = layui.form,
            formSelects = layui.formSelects,
            suggest = layui.suggest,
            laydate = layui.laydate,
            table = layui.table, tablePlug = layui.tablePlug;

        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));
        //报修时间元素渲染
        laydate.render({
            elem: '#partsInWareAddtime' //指定元素
        });
        //监听提交
        form.verify({
            supplier_id: function (value) {
                if (!value) {
                    return '请选择供应商';
                }
            },
            addtime: function (value) {
                if (!value) {
                    return '请选择选择入库日期';
                }
            }
        });
        suggest.search();

        form.on('submit(addPartsInWare)', function (data) {
            var params = data.field;
            var parts_tr = $('.parts_tr');
            if (parts_tr.length === 0) {
                layer.msg('请至少补充一条配件采购记录', {icon: 2, time: 3000});
                return false;
            }
            var error = false;
            var msg = '';
            var parts = '', parts_model = '', manufacturer_name = '', manufacturer_id = '', brand = '', unit = '', sum = '',
                price = '';
            $.each(parts_tr, function (key, value) {
                var parts_val = $(this).find('input[name="parts"]').val();
                var parts_model_val = $(this).find('input[name="parts_model"]').val();
                var manufacturer_name_val = $(this).find('input[name="manufacturer_name"]').val();
                var manufacturer_id_val = $(this).find('input[name="manufacturer_id"]').val();
                var brand_val = $(this).find('input[name="brand"]').val();
                var unit_val = $(this).find('input[name="unit"]').val();
                var sum_val = $(this).find('input[name="sum"]').val();
                var price_val = $(this).find('input[name="price"]').val();
                if (!check_num(sum_val)) {
                    error = true;
                    msg = '配件：' + parts_val + ' 请输入合理的数量';
                    return false;
                }
                if (!check_price(price_val)) {
                    error = true;
                    msg = '配件：' + parts_val + ' 请输入合理的单价';
                    return false;
                }
                parts += parts_val + '|';
                parts_model += parts_model_val + '|';
                manufacturer_name += manufacturer_name_val + '|';
                manufacturer_id += manufacturer_id_val + '|';
                brand += brand_val + '|';
                unit += unit_val + '|';
                sum += sum_val + '|';
                price += price_val + '|';
            });
            if (error) {
                layer.msg(msg, {icon: 2, time: 3000});
                return false;
            }
            params.supplier_name = $('select[name="supplier_id"]').find('option[value="' + params.supplier_id + '"]').html();
            params.parts = parts;
            params.parts_model = parts_model;
            params.manufacturer_name = manufacturer_name;
            params.manufacturer_id = manufacturer_id;
            params.brand = brand;
            params.unit = unit;
            params.sum = sum;
            params.price = price;
            submit($, params, partsInWareUrl);
            return false;
        });


        //记录被纳入的配件明细
        var joinedParts = '';
        var getCanJoinPartsTable = false;
        var getCanJoinPartsTableWhere = {};
        getCanJoinPartsTableWhere.action = 'canJoinPartsList';

        //点击添加采购明细
        $(document).on('click', '#pushParts', function () {
            layer.open({
                id: 'partsInWarePushParts',
                type: 1,
                title: '可纳入的配件信息',
                area: ['1024px', '98%'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addPartsDiv'),
                success: function (layero, index) {
                    if (getCanJoinPartsTable === false) {
                        getCanJoinPartsTable = true;
                        table.render({
                            elem: '#canJoinPartsList'
                            , limits: [10, 20, 50, 100]
                            , loading: true
                            , where: getCanJoinPartsTableWhere
                            , url: partsInWareUrl
                            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
                            , request: {
                                pageName: 'page' //页码的参数名称，默认：page
                                , limitName: 'limit' //每页数据量的参数名，默认：limit
                            } //如果无需自定义请求参数，可不加该参数
                            , response: { //定义后端 json 格式，详细参见官方文档
                                statusName: 'code', //状态字段名称
                                statusCode: '200', //状态字段成功值
                                msgName: 'msg', //消息字段
                                countName: 'total', //总数字段
                                dataName: 'rows' //数据字段
                            }
                            , toolbar: '#LAY-Repair-RepairParts-partsInWarePushPartsbar'
                            , defaultToolbar: false
                            , page: {
                                groups: 10 //只显示 1 个连续页码
                                , prev: '上一页'
                                , next: '下一页'
                            }
                            //,page: true //开启分页
                            , cols: [[ //表头
                                {type: 'checkbox', fixed: 'left'},
                                {
                                    field: 'dic_partsid',
                                    title: '序号',
                                    width: 80,
                                    fixed: 'left',
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'parts', fixed: 'left', title: '配件名称', width: 150, align: 'center'}
                                , {field: 'parts_model', fixed: 'left', title: '配件型号', width: 150, align: 'center'}
                                , {field: 'dic_category', title: '配件分类', width: 120, align: 'center'}
                                , {field: 'unit', title: '单位', width: 120, align: 'center'}
                                , {field: 'price', title: '单价', width: 100, align: 'center'}
                                , {field: 'brand', title: '品牌', width: 150, align: 'center'}
                                , {field: 'supplier_name', title: '生产厂商', width: 150, align: 'center'}
                                , {field: 'operation', title: '操作', fixed: 'right', width: 100, align: 'center'}
                            ]]
                            , done: function (res) {
                            }
                        });
                    }
                }
            });
        });

        //监听可纳入的配件明细工具条
        table.on('tool(canJoinPartsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'add':
                    joinedParts += ',' + rows.dic_partsid;
                    if (joinedParts.substr(0, 1) === ',') {
                        joinedParts = joinedParts.substr(1);
                    }
                    var emptyJoinPartsTr = $('.emptyJoinPartsTr');
                    var html = get_parts_tr(rows);
                    var pushPartTbody = $('.pushPartTbody');
                    pushPartTbody.before(html);
                    //没有配件情况,添加时清空空数据
                    if (emptyJoinPartsTr.length === 1) {
                        emptyJoinPartsTr.remove();
                    }
                    getCanJoinPartsTableWhere.joinedParts = joinedParts;
                    //刷新可纳入配件表
                    table.reload('canJoinPartsList', {where: getCanJoinPartsTableWhere, page: {curr: 1}});
                    break;
            }
        });


        //监听可纳入的采购单明细工具条
        table.on('toolbar(canJoinPartsData)', function (obj) {
            var event = obj.event, url = $(this).attr('data-url'),flag = 1;
            switch (event) {
                case 'addAllParts':
                    //批量纳入配件明细
                    var checkStatus = table.checkStatus('canJoinPartsList');
                    var data = checkStatus.data;
                    if (data.length === 0) {
                        layer.msg("请选择要纳入的配件明细", {icon: 2}, 1000);
                        return false;
                    }
                    var html = '';
                    $.each(data, function (k, v) {

                        joinedParts += ',' + v.dic_partsid;
                        html += get_parts_tr(v);
                    });
                    if (joinedParts.substr(0, 1) === ',') {
                        joinedParts = joinedParts.substr(1);
                    }

                    var emptyJoinPartsTr = $('.emptyJoinPartsTr');
                    var pushPartTbody = $('.pushPartTbody');
                    pushPartTbody.before(html);
                    //没有配件情况,添加时清空空数据
                    if (emptyJoinPartsTr.length === 1) {
                        emptyJoinPartsTr.remove();
                    }
                    //刷新
                    getCanJoinPartsTableWhere.joinedParts = joinedParts;
                    table.reload('canJoinPartsList', {
                        where: getCanJoinPartsTableWhere, page: {curr: 1}
                    });

                    break;
                case 'addPartsDic':
                    top.layer.open({
                        id: 'addDictorys',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['700px', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            if (flag) {
                                table.reload('canJoinPartsList', {
                                    where: getCanJoinPartsTableWhere, page: {curr: 1}
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        //监听可纳入的配件单明细单搜索按钮
        form.on('submit(partsInWareCanJoinPartsSearch)', function (data) {
            getCanJoinPartsTableWhere.joinedParts = joinedParts;
            getCanJoinPartsTableWhere.parts = data.field.parts;
            getCanJoinPartsTableWhere.dic_category = data.field.dic_category;
            getCanJoinPartsTableWhere.supplier_id = data.field.supplier_id;
            table.reload('canJoinPartsList', {
                where: getCanJoinPartsTableWhere, page: {curr: 1}
            });
            return false;
        });

        //组合已纳入的配件信息
        function get_parts_tr(data) {
            var html = '<tr class="parts_tr">';
            html += '<td><input type="hidden" name="dic_partsid" value="' + data.dic_partsid + '"><input type="hidden" name="parts" value="' + data.parts + '">' + data.parts + '</td>';
            html += '<td><input type="hidden" name="parts_model" value="' + data.parts_model + '">' + data.parts_model + '</td>';
            html += '<td><input type="hidden" name="manufacturer_name" value="' + data.supplier_name + '"><input type="hidden" name="manufacturer_id" value="' + data.supplier_id + '">' + data.supplier_name + '</td>';
            html += '<td><input type="hidden" name="brand" value="' + data.brand + '">' + data.brand + '</td>';
            html += '<td><input type="hidden" name="unit" value="' + data.unit + '">' + data.unit + '</td>';
            html += '<td class="no-padding-td"><input class="layui-input" type="text" name="sum" placeholder="请输入数量"></td>';
            html += '<td class="no-padding-td"><input class="layui-input" type="text" name="price" placeholder="请输入单价" value="' + data.price + '"></td>';
            html += '<td><button type="button" class="layui-btn layui-btn-xs layui-btn-danger remove_parts" ><i class="layui-icon">&#xe640;</i>移除</button></td>';
            html += '</tr>';
            return html;
        }


        //点击移除配件信息
        $(document).on('click', '.remove_parts', function () {
            var a_this = $(this).parent().parent();
            layer.confirm('请确认移除此配件明细？', {icon: 3, title: '移除提示'}, function (index) {
                var dic_partsid = a_this.find('input[name="dic_partsid"]').val();
                joinedParts = joinedParts.split(',');
                joinedParts.splice($.inArray(dic_partsid, joinedParts), 1);
                joinedParts = joinedParts.join(",");
                getCanJoinPartsTableWhere.joinedParts = joinedParts;
                table.reload('canJoinPartsList', {where: getCanJoinPartsTableWhere, page: {curr: 1}});
                a_this.remove();
                var parts_tr = $('.parts_tr');
                if (parts_tr.length === 0) {
                    $('.pushPartTbody').html('<tr class="emptyJoinPartsTr"> <td colspan="8" style="text-align: center">暂无数据</td> </tr>');
                }
                layer.close(index);
            });
        });

        //点击补充厂家
        $(document).on('click', '#addSupplier', function () {
            layer.open({
                id: 'partsInWareAddSupplier',
                type: 1,
                title: '添加厂商',
                area: ['750px', '450px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addSuppliersDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddSuppliers();
                    form.on('submit(addSuppliers)', function (data) {
                        var params = data.field;
                        params.action = 'addSuppliers';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: partsInWareUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('input[name="sup_num"]').val(data.result.sup_num);
                                    var arr = params.suppliers_type.split(',');
                                    var html = '';
                                    if ($.inArray('2', arr) >= 0 || $.inArray('1', arr) >= 0) {
                                        html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                        $('select[name="supplier_id"]').append(html);
                                        form.render();
                                    }
                                    layer.close(layer.index);
                                    layer.msg('补充成功', {icon: 1, time: 3000});
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                        return false;
                    });
                    form.render('select');
                }
            });
        });

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
                        url: partsInWareUrl,
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
                        url: partsInWareUrl,
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


        //选择字典类型
        $("#partsInWareDicCategory").bsSuggest(
            returnDicCategory('parts')
        );

        //选择配件名称
        $("#partsInWareBsSuggestParts").bsSuggest(
            returnPartsDic()
        );
    });
    exports('controller/repair/repairParts/partsInWare', {});
});
