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
            elem: '#partsOutWareAddtime' //指定元素
            , type: 'datetime',
            calendar: true
            , trigger: 'click'
            , done: function (value, date) {
                if (date.hours === 0 && date.minutes === 0 && date.seconds === 0) {
                    layer.confirm('当前选择日期暂无具体时间,是否需要补充具体时间？', {
                        btn: ['是', '否'],
                        title: '是否需要补充时间',
                        closeBtn: 0
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            $("#partsOutWareAddtime").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                    });
                }
            }
        });
        //监听提交
        form.verify({
            leader: function (value) {
                if (!value) {
                    return '请选择领用人';
                }
            },
            addtime: function (value) {
                if (!value) {
                    return '请选择选择出库日期';
                }
            }
        });
        suggest.search();

        form.on('submit(addPartsOutWare)', function (data) {
            var params = data.field;
            var parts_tr = $('.parts_tr');
            if (parts_tr.length === 0) {
                layer.msg('请至少补充一条配件出库明细', {icon: 2, time: 3000});
                return false;
            }
            var error = false;
            var msg = '';
            var parts = '', parts_model = '', supplier_name = '', supplier_id = '', unit = '', sum = '',max_sum='',
                price='',detailid='';
            $.each(parts_tr, function () {
                var parts_val = $(this).find('input[name="parts"]').val();
                var detailid_val = $(this).find('input[name="detailid"]').val();
                var parts_model_val = $(this).find('input[name="parts_model"]').val();
                var supplier_name_val = $(this).find('input[name="supplier_name"]').val();
                var supplier_id_val = $(this).find('input[name="supplier_id"]').val();
                var unit_val = $(this).find('input[name="unit"]').val();
                var sum_val = $(this).find('input[name="sum"]').val();
                var price_val = $(this).find('input[name="price"]').val();
                var max_sum_val = $(this).find('input[name="max_sum"]').val();
                if (!check_num(sum_val)) {
                    error = true;
                    msg = '出库配件：' + parts_val + ' 请输入合理的数量';
                    return false;
                } else {
                    if (parseInt(sum_val) > parseInt(max_sum_val)) {
                        error = true;
                        msg = '出库配件：' + parts_val + ' 数量不能多于库存数量' + max_sum_val + ' ' + unit_val;
                        return false;
                    }
                }
                parts += parts_val + '|';
                detailid += detailid_val + '|';
                parts_model += parts_model_val + '|';
                supplier_name += supplier_name_val + '|';
                supplier_id += supplier_id_val + '|';
                unit += unit_val + '|';
                sum += sum_val + '|';
                price += price_val + '|';
                max_sum += max_sum_val + '|';
            });
            if (error) {
                layer.msg(msg, {icon: 2, time: 3000});
                return false;
            }
            params.parts = parts;
            params.detailid = detailid;
            params.parts_model = parts_model;
            params.supplier_name = supplier_name;
            params.supplier_id = supplier_id;
            params.unit = unit;
            params.sum = sum;
            params.price = price;
            params.max_sum = max_sum;
            submit($, params, partsOutWareUrl);
            return false;
        });


        //记录被纳入的配件明细
        var joinedDetailid = '';
        var getCanJoinPartsTable = false;
        var getCanJoinPartsTableWhere = {};
        getCanJoinPartsTableWhere.action = 'canJoinOutWareList';

        //点击添加采购明细
        $(document).on('click', '#pushParts', function () {
            layer.open({
                id: 'partsOutWarePushParts',
                type: 1,
                title: '可纳入的配件信息',
                area: ['1024px', '600px'],
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
                            , url: partsOutWareUrl
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
                            , toolbar: '#LAY-Repair-RepairParts-partsOutWarePushPartsbar'
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
                                    field: 'detailid',
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
                                , {field: 'supplier_name', title: '供应商', width: 180, align: 'center'}
                                , {field: 'max_sum', title: '库存', width: 120, align: 'center'}
                                , {field: 'price', title: '单价', width: 120, align: 'center'}
                                , {field: 'unit', title: '单位', width: 100, align: 'center'}
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
                    joinedDetailid += '|' + rows.detailid;
                    // console.log(joinedDetailid);
                    if (joinedDetailid.substr(0, 1) === '|') {
                        joinedDetailid = joinedDetailid.substr(1);
                    }
                    var emptyJoinPartsTr = $('.emptyJoinPartsTr');
                    var html = get_parts_tr(rows);
                    var pushPartTbody = $('.pushPartTbody');
                    pushPartTbody.before(html);
                    //没有配件情况,添加时清空空数据
                    if (emptyJoinPartsTr.length === 1) {
                        emptyJoinPartsTr.remove();
                    }
                    getCanJoinPartsTableWhere.joinedDetailid = joinedDetailid;
                    //刷新可纳入配件表
                    table.reload('canJoinPartsList', {where: getCanJoinPartsTableWhere, page: {curr: 1}});
                    break;
            }
        });


        //监听可纳入的采购单明细工具条
        table.on('toolbar(canJoinPartsData)', function (obj) {
            var event = obj.event, url = $(this).attr('data-url');
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
                        joinedDetailid += '|' + v.detailid;
                        html += get_parts_tr(v);
                    });

                    if (joinedDetailid.substr(0, 1) === '|') {
                        joinedDetailid = joinedDetailid.substr(1);
                    }
                    var emptyJoinPartsTr = $('.emptyJoinPartsTr');
                    var pushPartTbody = $('.pushPartTbody');
                    pushPartTbody.before(html);
                    //没有配件情况,添加时清空空数据
                    if (emptyJoinPartsTr.length === 1) {
                        emptyJoinPartsTr.remove();
                    }
                    //刷新
                    getCanJoinPartsTableWhere.joinedDetailid = joinedDetailid;
                    table.reload('canJoinPartsList', {
                        where: getCanJoinPartsTableWhere, page: {curr: 1}
                    });

                    break;
            }
        });

        //监听可纳入的配件单明细单搜索按钮
        form.on('submit(partsOutWareCanJoinPartsSearch)', function (data) {
            getCanJoinPartsTableWhere.joinedDetailid = joinedDetailid;
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
            html += '<td><input type="hidden" name="detailid" value="' + data.detailid + '"><input type="hidden" name="parts" value="' + data.parts + '">' + data.parts + '</td>';
            html += '<td><input type="hidden" name="parts_model" value="' + data.parts_model + '">' + data.parts_model + '</td>';
            html += '<td><input type="hidden" name="supplier_name" value="' + data.supplier_name + '"><input type="hidden" name="supplier_id" value="' + data.supplier_id + '">' + data.supplier_name + '</td>';
            html += '<td><input type="hidden" name="unit" value="' + data.unit + '">' + data.unit + '</td>';
            html += '<td><input type="hidden" name="max_sum" value="' + data.max_sum + '">' + data.max_sum + '</td>';
            html += '<td class="no-padding-td"><input class="layui-input" type="text" name="sum" placeholder="请输入出库数量"></td>';
            html += '<td><input type="hidden" name="price" value="' + data.price + '">' + data.price + '</td>';
            html += '<td><button type="button" class="layui-btn layui-btn-xs layui-btn-danger remove_parts" ><i class="layui-icon">&#xe640;</i>移除</button></td>';
            html += '</tr>';
            return html;
        }


        //点击移除配件信息
        $(document).on('click', '.remove_parts', function () {
            var a_this = $(this).parent().parent();
            layer.confirm('请确认移除此配件明细？', {icon: 3, title: '移除提示'}, function (index) {
                var detailid = a_this.find('input[name="detailid"]').val();
                // console.log('点击前:'+joinedDetailid);
                joinedDetailid = joinedDetailid.split('|');
                // console.log('分割成数组');
                // console.log(joinedDetailid);
                // console.log('选中的'+detailid);
                joinedDetailid.splice($.inArray(detailid, joinedDetailid), 1);
                // console.log('排除后');
                // console.log(joinedDetailid);
                joinedDetailid = joinedDetailid.join("|");
                // console.log(joinedDetailid);
                getCanJoinPartsTableWhere.joinedDetailid = joinedDetailid;
                table.reload('canJoinPartsList', {where: getCanJoinPartsTableWhere, page: {curr: 1}});
                a_this.remove();
                var parts_tr = $('.parts_tr');
                if (parts_tr.length === 0) {
                    $('.pushPartTbody').html('<tr class="emptyJoinPartsTr"> <td colspan="8" style="text-align: center">暂无数据</td> </tr>');
                }
                layer.close(index);
            });
        });


        //选择配件名称
        $("#partsOutWareBsSuggestParts").bsSuggest(
            returnPartsDic()
        );
    });
    exports('controller/repair/repairParts/partsOutWare', {});
});
