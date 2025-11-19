layui.define(function(exports){
    layui.use(['table', 'layer', 'form', 'laydate', 'upload', 'tipsType', 'formSelects', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer,
            table = layui.table,
            formSelects = layui.formSelects,
            form = layui.form,
            laydate = layui.laydate,
            upload = layui.upload,
            tipsType = layui.tipsType,
            suggest = layui.suggest,
            $ = layui.jquery
            , tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();
        tipsType.choose();
        //初始化搜索建议插件
        suggest.search();
        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));
        laydate.render(dateConfig('#addOLSContractSign_date'));
        laydate.render(dateConfig('#addOLSContractEnd_date'));
        laydate.render(dateConfig('#addOLSContractCheck_date'));

        //数据验证
        form.verify({
            tel: function (value) {
                if (value !== '') {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '号码首尾不能出现下划线\'_\'';
                    }
                    if (!checkTel(value)) {
                        return '请正确填写号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                    }
                }
            },
            checkEmail: function (value) {
                if (value !== '') {
                    if (!/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(value)) {
                        return '请输入正确的邮箱';
                    }
                }
            },
            price: function (value) {
                if (!check_price(value)) {
                    return '请输入正确的合同金额';
                }
            },
            supplier_id:function (value) {
                if(!value){
                    return '请选择乙方单位';
                }
            }
        });

        //监听提交
        form.on('submit(add)', function (data) {
            var erro = false;
            var bug_msg = '';
            var params = data.field;
            params.supplier_name = $('select[name="supplier_id"]').find('option[value="' + params.supplier_id + '"]').html();
            //获取分期信息
            var phaseDataTr = $('.phaseDataTr');
            if (phaseDataTr.length > 0) {
                params.pay_amount = '';
                params.estimate_pay_date = '';
                params.real_pay_date = '';
                params.phase = '';
                params.phase_sum = 0;
                $.each(phaseDataTr, function (key, value) {
                    var phase = $(value).find('.phaseTd').html();
                    var pay_amount = $(value).find('input[name="pay_amount"]').val();
                    var estimate_pay_date = $(value).find('input[name="estimate_pay_date"]').val();
                    var real_pay_date = $(value).find('input[name="real_pay_date"]').val();
                    if (!pay_amount) {
                        bug_msg = '第' + phase + '期合同付款信息 付款金额未补充';
                        erro = true;
                        return true;
                    }
                    if (!estimate_pay_date) {
                        bug_msg = '第' + phase + '期合同付款信息 预计付款时间未补充';
                        erro = true;
                        return true;
                    }
                    params.phase_sum += Number(pay_amount);
                    params.pay_amount += pay_amount + '|';
                    params.phase += phase + '|';
                    params.estimate_pay_date += estimate_pay_date + '|';
                    params.real_pay_date += real_pay_date + '|';
                });
                if (erro === true) {
                    layer.msg(bug_msg, {icon: 2, time: 3000});
                    return false;
                }
                if (params.phase_sum !== Number(params.contract_amount)) {
                    layer.msg('合同付款明细总金额需要等于合同金额,不需分期的请先移除', {icon: 2, time: 3000});
                    return false;
                }
            }
            //获取上传文件信息
            var fileDataTr = $('.fileDataTr');
            if (fileDataTr.length > 0) {
                params.file_name = '';
                params.save_name = '';
                params.file_type = '';
                params.file_size = '';
                params.file_url = '';
                $.each(fileDataTr, function (key, value) {
                    params.file_name += $(value).find('input[name="file_name"]').val() + '|';
                    params.save_name += $(value).find('input[name="save_name"]').val() + '|';
                    params.file_type += $(value).find('input[name="file_type"]').val() + '|';
                    params.file_size += $(value).find('input[name="file_size"]').val() + '|';
                    params.file_url += $(value).find('input[name="file_url"]').val() + '|';
                });
            }
            console.log(params);
            switch (parseInt(params.contract_type)) {
                case parseInt(CONTRACT_TYPE_SUPPLIER):
                    //获取采购设备明细
                    params = getAddPurchaseAssetsData(params);
                    if (params.addAssets_sum !== Number(params.contract_amount)) {
                        bug_msg = '采购设备价格总价需要等于合同金额';
                        erro = true;
                    }
                    break;
                case parseInt(CONTRACT_TYPE_REPAIR):
                    if (joinedRepair === '') {
                        bug_msg = '合同维修明细单不能为空';
                        erro = true;
                    }
                    params.joinedRepair = joinedRepair;
                    break;
                case parseInt(CONTRACT_TYPE_RECORD_ASSETS):
                    if (joinedRecordAssets === '') {
                        bug_msg = '设备明细单不能为空';
                        erro = true;
                    }
                    params.joinedRecordAssets = joinedRecordAssets;
                    break;
            }
            if (erro === true) {
                layer.msg(bug_msg, {icon: 2, time: 3000});
                return false;
            }
            console.log(params);
            submit($, params, addOLSContractUrl);
            return false;
        });


        //记录被纳入的采购设备明细(补录)
        var joinedRecordAssets = '';
        var getRecordAssetsTable = false;
        var getCanJoinRecordAssetsTable = false;

        //点击添加采购明细(补录)
        $(document).on('click', '#pushRecordAssets', function () {
            layer.open({
                id: 'addOLSContractPushRecordAssets',
                type: 1,
                title: '添加采购设备明细',
                area: ['1024px', '98%'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addRecordAssetsDiv'),
                success: function (layero, index) {
                    if (getCanJoinRecordAssetsTable === false) {
                        getCanJoinRecordAssetsTable = true;
                        table.render({
                            elem: '#canJoinRecordAssetsList'
                            , limits: [10, 20, 50, 100]
                            , loading: true
                            , where: {
                                joinedRecordAssets: joinedRecordAssets,
                                action: 'canJoinRecordAssetsList'
                            }
                            , url: addOLSContractUrl
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
                            , toolbar: '#LAY-OfflineSuppliers-OfflineSuppliers-addOLSContractAddRecordAssetsbar'
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
                                    field: 'assid',
                                    title: '序号',
                                    width: 50,
                                    fixed: 'left',
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'assnum', fixed: 'left', title: '设备编码', width: 150, align: 'center'}
                                , {field: 'assets', fixed: 'left', title: '设备名称', width: 180, align: 'center'}
                                , {field: 'model', title: '规格/型号', width: 150, align: 'center'}
                                , {field: 'serialnum', title: '资产序列号', width: 150, align: 'center'}
                                , {field: 'brand', title: '品牌', width: 150, align: 'center'}
                                , {field: 'unit', title: '单位', width: 100, align: 'center'}
                                , {field: 'buy_price', title: '设备原值', width: 120, align: 'center'}
                                , {field: 'factorydate', title: '出厂日期', width: 120, align: 'center'}
                                , {field: 'storage_date', title: '入库日期', width: 120, align: 'center'}
                                , {field: 'capitalfrom', title: '资金来源', width: 100, align: 'center'}
                                , {field: 'assfromid', title: '设备来源', width: 100, align: 'center'}
                                , {field: 'operation', title: '操作', fixed: 'right', width: 100, align: 'center'}
                            ]]
                            , done: function (res) {
                            }
                        });
                    }
                }
            });
        });

        //监听可纳入的维修明细工具条
        table.on('tool(canJoinRecordAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'add':
                    joinedRecordAssets += ',' + rows.assid;
                    if (joinedRecordAssets.substr(0, 1) === ',') {
                        joinedRecordAssets = joinedRecordAssets.substr(1);
                    }
                    table.reload('joinedRecordAssetsList', {
                        where: {
                            action: 'joinedRecordAssetsList',
                            joinedRecordAssets: joinedRecordAssets
                        }
                    });
                    table.reload('canJoinRecordAssetsList', {
                        where: {
                            action: 'canJoinRecordAssetsList',
                            hospital_id: current_hosid,
                            joinedRecordAssets: joinedRecordAssets
                        }
                    });
                    break;
            }
        });

        //监听已纳入的采购明细(补录)工具条
        table.on('tool(joinedRecordAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'del':
                    joinedRecordAssets = joinedRecordAssets.split(',');
                    joinedRecordAssets.splice($.inArray(rows.assid, joinedRecordAssets), 1);
                    joinedRecordAssets = joinedRecordAssets.join(",");
                    table.reload('joinedRecordAssetsList', {
                        where: {
                            action: 'joinedRecordAssetsList',
                            joinedRecordAssets: joinedRecordAssets
                        }
                    });
                    table.reload('canJoinRecordAssetsList', {
                        where: {
                            action: 'canJoinRecordAssetsList',
                            hospital_id: current_hosid,
                            joinedRecordAssets: joinedRecordAssets
                        }
                    });
                    break;
            }
        });

        //监听可纳入的采购单明细(补录)工具条
        table.on('toolbar(canJoinRecordAssetsData)', function (obj) {
            var event = obj.event, url = $(this).attr('data-url');
            switch (event) {
                case 'addAllRecordAssets':
                    //批量纳入设备明细
                    var checkStatus = table.checkStatus('canJoinRecordAssetsList');
                    var data = checkStatus.data;
                    if (data.length === 0) {
                        layer.msg("请选择要纳入的设备明细", {icon: 2}, 1000);
                        return false;
                    }
                    $.each(data, function (k, v) {
                        joinedRecordAssets += ',' + v.assid;
                    });
                    if (joinedRecordAssets.substr(0, 1) === ',') {
                        joinedRecordAssets = joinedRecordAssets.substr(1);
                    }
                    //刷新
                    if (joinedRecordAssets) {
                        table.reload('joinedRecordAssetsList', {
                            where: {
                                action: 'joinedRecordAssetsList',
                                joinedRecordAssets: joinedRecordAssets
                            }
                        });
                        table.reload('canJoinRecordAssetsList', {
                            where: {
                                action: 'canJoinRecordAssetsList',
                                hospital_id: current_hosid,
                                joinedRecordAssets: joinedRecordAssets
                            }
                        });
                    }
                    break;
            }
        });

        //监听可纳入的维修单明细单搜索按钮
        form.on('submit(addOLSContractRecordAssetsSearch)', function (data) {
            var getCanJoinRecordAssetsTableWhere={};
            getCanJoinRecordAssetsTableWhere.joinedRecordAssets = joinedRecordAssets;
            getCanJoinRecordAssetsTableWhere.assets = data.field.assets;
            getCanJoinRecordAssetsTableWhere.model = data.field.model;
            table.reload('canJoinRecordAssetsList', {
                where: getCanJoinRecordAssetsTableWhere, page: {curr: 1}
            });
            return false;
        });

        //记录被纳入的维修单
        var joinedRepair = '';
        var getCanJoinRepairTable = false;
        var getCanJoinRepairTableWhere = {};


        //点击添加维修明细
        $(document).on('click', '#pushRepair', function () {
            var supplier_id = $('select[name="supplier_id"]').val();
            console.log(supplier_id);
            if (!supplier_id) {
                layer.msg('请先选择乙方单位', {icon: 2, time: 3000});
                return false;
            }
            layer.open({
                id: 'addOLSContractPushRepair',
                type: 1,
                title: '添加维修明细',
                area: ['1024px', '98%'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addRepairDiv'),
                success: function () {
                    if (getCanJoinRepairTable === false || getCanJoinRepairTableWhere.supplier_id !== supplier_id || contract_type_is_change_repair===true) {
                        getCanJoinRepairTableWhere.hospital_id = current_hosid;
                        getCanJoinRepairTableWhere.joinedRepair = joinedRepair;
                        getCanJoinRepairTableWhere.supplier_id = supplier_id;
                        getCanJoinRepairTableWhere.action = 'canJoinRepairList';
                        getCanJoinRepairTable = true;
                        table.render({
                            elem: '#canJoinRepairList'
                            , limits: [10, 20, 50, 100]
                            , loading: true
                            , where: getCanJoinRepairTableWhere
                            , url: addOLSContractUrl
                            , method: 'POST'
                            , request: {
                                pageName: 'page'
                                , limitName: 'limit'
                            }
                            , response: { //定义后端 json 格式，详细参见官方文档
                                statusName: 'code', //状态字段名称
                                statusCode: '200', //状态字段成功值
                                msgName: 'msg', //消息字段
                                countName: 'total', //总数字段
                                dataName: 'rows' //数据字段
                            }
                            , toolbar: '#LAY-OfflineSuppliers-OfflineSuppliers-addOLSContractAddRepairbar'
                            , defaultToolbar: false
                            , page: {
                                groups: 10
                                , prev: '上一页'
                                , next: '下一页'
                            }
                            //,page: true //开启分页
                            , cols: [[
                                {type: 'checkbox', fixed: 'left'},
                                {
                                    field: 'assid',
                                    title: '序号',
                                    width: 50,
                                    fixed: 'left',
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'repnum', fixed: 'left', title: '维修单编号', width: 150, align: 'center'}
                                , {field: 'assets', fixed: 'left', title: '设备名称', width: 170, align: 'center'}
                                , {field: 'model', title: '规格型号', width: 120, align: 'center'}
                                , {field: 'company_total_price', title: '第三方总费用', width: 120, align: 'center'}

                                , {field: 'operation', title: '操作', fixed: 'right', width: 100, align: 'center'}
                            ]]
                            , done: function (res) {
                                //更新后恢复默认false
                                contract_type_is_change_repair=false;
                            }
                        });
                    }
                }
            });
        });

        //监听可纳入的维修明细工具条
        table.on('tool(canJoinRepairData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'add':
                    joinedRepair += ',' + rows.repid;
                    if (joinedRepair.substr(0, 1) === ',') {
                        joinedRepair = joinedRepair.substr(1);
                    }
                    table.reload('joinedRepairList', {where: {action: 'joinedRepairList', joinedRepair: joinedRepair}});
                    table.reload('canJoinRepairList', {
                        where: {
                            action: 'canJoinRepairList',
                            hospital_id: current_hosid,
                            joinedRepair: joinedRepair
                        }
                    });
                    break;
            }
        });

        //监听已纳入的维修明细工具条
        table.on('tool(joinedRepairData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'del':
                    joinedRepair = joinedRepair.split(',');
                    joinedRepair.splice($.inArray(rows.repid, joinedRepair), 1);
                    joinedRepair = joinedRepair.join(",");
                    table.reload('joinedRepairList', {where: {action: 'joinedRepairList', joinedRepair: joinedRepair}});
                    table.reload('canJoinRepairList', {
                        where: {
                            action: 'canJoinRepairList',
                            hospital_id: current_hosid,
                            joinedRepair: joinedRepair
                        }
                    });
                    break;
            }
        });

        //监听可纳入的维修单明细单工具条
        table.on('toolbar(canJoinRepairData)', function (obj) {
            var event = obj.event, url = $(this).attr('data-url');
            switch (event) {
                case 'addAllRepair':
                    //批量纳入维修明细
                    var checkStatus = table.checkStatus('canJoinRepairList');
                    var data = checkStatus.data;
                    if (data.length === 0) {
                        layer.msg("请选择要纳入的维修明细", {icon: 2}, 1000);
                        return false;
                    }
                    $.each(data, function (k, v) {
                        joinedRepair += ',' + v.repid;
                    });
                    if (joinedRepair.substr(0, 1) === ',') {
                        joinedRepair = joinedRepair.substr(1);
                    }
                    //刷新
                    if (joinedRepair) {
                        table.reload('joinedRepairList', {where: {action: 'joinedRepairList', joinedRepair: joinedRepair}});
                        table.reload('canJoinRepairList', {
                            where: {action: 'canJoinRepairList', hospital_id: current_hosid, joinedRepair: joinedRepair}
                        });
                    }
                    break;
            }
        });

        //监听可纳入的维修单明细单搜索按钮
        form.on('submit(addOLSContractRepairSearch)', function (data) {
            getCanJoinRepairTableWhere.joinedRepair = joinedRepair;
            getCanJoinRepairTableWhere.assets = data.field.getRepairSearchListAssets;
            getCanJoinRepairTableWhere.repnum = data.field.getRepairSearchListRepnum;
            getCanJoinRepairTableWhere.model = data.field.getRepairSearchListModel;
            table.reload('canJoinRepairList', {
                where: getCanJoinRepairTableWhere, page: {curr: 1}
            });
            return false;
        });


        var old_contract_type = 0;
        var old_olsid = 0;
        var dateNum = 0;
        var phase = 1;
        var contract_type_is_change_repair=false;

        //选择合同类型
        form.on('select(contract_type)', function (data) {
            var contract_type = parseInt(data.value);
            if (data.value) {
                if (contract_type !== old_contract_type) {
                    //修改了类型
                    contract_type_is_change_repair=true;
                    //显示对应div
                    var addContractTypeDiv = $('.addContractTypeDiv');
                    //采购DIV
                    var addOLSContractAddAssets = $('#addOLSContractAddAssets');
                    //维修DIV
                    var addOLSContractAddRepair = $('#addOLSContractAddRepair');
                    //维保DIV
                    var addOLSContractAddInsurance = $('#addOLSContractAddInsurance');
                    //采购(补录)
                    var addOLSContractAddRecordAssets = $('#addOLSContractAddRecordAssets');
                    switch (contract_type) {
                        case parseInt(CONTRACT_TYPE_SUPPLIER):
                            addContractTypeDiv.hide();
                            addOLSContractAddAssets.show();
                            break;
                        case parseInt(CONTRACT_TYPE_REPAIR):
                            addContractTypeDiv.hide();
                            addOLSContractAddRepair.show();
                            //清空已纳入的维修单 并且更新
                            joinedRepair='';
                            initialRepairTable();
                            break;
                        case parseInt(CONTRACT_TYPE_INSURANCE):
                            addContractTypeDiv.hide();
                            addOLSContractAddInsurance.show();
                            break;
                        case parseInt(CONTRACT_TYPE_RECORD_ASSETS):
                            addContractTypeDiv.hide();
                            addOLSContractAddRecordAssets.show();
                            if (getRecordAssetsTable === false) {
                                initialRecordAssetsTable();
                                getRecordAssetsTable = true;
                            }
                            break;
                        default:
                            addContractTypeDiv.hide();
                            break;
                    }
                    var params = {};
                    params.action = 'getSuppliers';
                    params.contract_type = contract_type;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: addOLSContractUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                var html = '<option value="">请选择乙方单位</option>';
                                $.each(data.result, function (key, value) {
                                    html += '<option value="' + value.olsid + '">' + value.sup_name + '</option>';
                                });
                                $('select[name="supplier_id"]').html(html);
                                form.render();
                                old_contract_type = contract_type;

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
                var html = '<option value="">请先选择合同类型</option>';
                //选择空名称 复位填充数据
                $('select[name="supplier_id"]').html(html);
                $('.addContractTypeDiv').hide();
                form.render();
                old_contract_type = 0;
            }
        });

        //选择乙方单位
        form.on('select(supplier_id)', function (data) {
            var olsid = parseInt(data.value);
            if (data.value) {
                if (olsid !== old_olsid) {
                    var params = {};
                    params.action = 'getSalesman';
                    params.olsid = olsid;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: addOLSContractUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                $('input[name="supplier_contacts"]').val(data.result.salesman_name);
                                $('input[name="supplier_phone"]').val(data.result.salesman_phone);
                                form.render();
                                switch (old_contract_type) {
                                    case parseInt(CONTRACT_TYPE_SUPPLIER):
                                    case parseInt(CONTRACT_TYPE_RECORD_ASSETS):
                                        //采购 采购(补录)todo
                                        break;
                                    case parseInt(CONTRACT_TYPE_REPAIR):
                                        //如果是更换了维修类型的 乙方单位需要清空已纳入的维修单 并且刷新表格
                                        joinedRepair='';
                                        table.reload('joinedRepairList', {where: {action: 'joinedRepairList', joinedRepair: joinedRepair}});
                                        break;
                                    case parseInt(CONTRACT_TYPE_INSURANCE):
                                        //维保 todo
                                        break;
                                }
                                old_olsid = olsid;
                            } else {
                                //无录入数据 复位
                                $('input[name="supplier_contacts"]').val('');
                                $('input[name="supplier_phone"]').val('');
                                form.render();
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                //选择空名称 复位填充数据
                $('input[name="supplier_contacts"]').val('');
                $('input[name="supplier_phone"]').val('');
                form.render();
                old_olsid = 0;
            }
        });

        //点击补充厂家
        $(document).on('click', '#addSupplier', function () {
            layer.open({
                id: 'addOLSContractAddSupplier',
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
                            url: addOLSContractUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('input[name="sup_num"]').val(data.result.sup_num);
                                    console.log(old_contract_type);
                                    if (old_contract_type !== 0) {
                                        var arr = params.suppliers_type.split(',');
                                        var html = '';
                                        var set = false;
                                        switch (old_contract_type) {
                                            //采购
                                            case parseInt(CONTRACT_TYPE_SUPPLIER):
                                            case parseInt(CONTRACT_TYPE_RECORD_ASSETS):
                                                if ($.inArray('1', arr) >= 0 || $.inArray('2', arr) >= 0) {
                                                    set = true;
                                                }
                                                break;
                                            case parseInt(CONTRACT_TYPE_REPAIR):
                                                console.log(arr);
                                                if ($.inArray('3', arr) >= 0) {
                                                    set = true;
                                                }
                                                break;
                                            case parseInt(CONTRACT_TYPE_INSURANCE):
                                                console.log(arr);
                                                if ($.inArray('4', arr) >= 0) {
                                                    set = true;
                                                }
                                                break;
                                        }
                                        if (set === true) {
                                            html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                            $('select[name="supplier_id"]').append(html);
                                            form.render();
                                        }
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

        //点击添加期数
        $(document).on('click', '#addPhase', function () {
            var notPhaseDataTr = $('.notPhaseDataTr');
            if (notPhaseDataTr.length > 0) {
                notPhaseDataTr.remove();
            }
            var addPhaseTbody = $('.addPhaseTbody');
            var html = '<tr class="phaseDataTr">';
            html += ' <td class="phaseTd">' + phase + '</td>';
            html += '<td class="pay_amount_td"><input type="text" name="pay_amount" autocomplete="off" placeholder="" value="" class="layui-input"></td>';
            html += '<td><input type="text" name="estimate_pay_date" autocomplete="off" placeholder="请选择预计付款日期" class="layui-input" id="estimate_pay_date' + dateNum + '"></td>';
            html += '<td><input type="text" name="real_pay_date" autocomplete="off" placeholder="请选择付款日期" class="layui-input" id="real_pay_date' + dateNum + '"></td>';
            html += '<td class="pay_status">未付款</td>';
            html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_pay">移除</div></td>';
            addPhaseTbody.append(html);
            laydate.render({
                elem: '#estimate_pay_date' + dateNum
                , calendar: true
                , min: '1'
                , done: function (value, date, endDate) {

                }
            });
            laydate.render({
                elem: '#real_pay_date' + dateNum
                , calendar: true
                , min: '1'
                , done: function (value) {
                    var dateTd = $(this.elem[0]).parent('td');
                    var pay_amount = dateTd.siblings('.pay_amount_td').find('input[name="pay_amount"]').val();
                    if (value !== '' && pay_amount > 0) {
                        dateTd.siblings('.pay_status').html('<span style="color:green">已付款</span>');
                    }
                }
            });
            dateNum++;
            phase++;
        });

        //移除期次
        $(document).on('click', '.del_pay', function () {
            var thisTr = $(this).parents('tr');
            var addPhaseTbody = $('.addPhaseTbody');
            var phaseDataTr = $('.phaseDataTr');
            thisTr.remove();
            if (addPhaseTbody.find('tr').length > 0) {
                $.each(addPhaseTbody.find('tr'), function (key, value) {
                    console.log($(value).find('.phaseTd').html(key + 1));
                })
            } else {
                addPhaseTbody.html('<tr class="notPhaseDataTr"><td colspan="6" style="text-align: center!important;">暂无数据</td></tr>');
            }
            phase--;
            layer.msg('移除成功', {icon: 1}, 1000);
        });

        //上传文件
        uploadFile = upload.render({
            elem: '#addOLSContractFile'  //绑定元素
            , url: addOLSContractUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = $('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = $('.addFileTbody');
                    var input = '<input type="hidden" name="file_name" value="' + res.formerly + '">';
                    input += '<input type="hidden" name="save_name" value="' + res.title + '">';
                    input += '<input type="hidden" name="file_type" value="' + res.ext + '">';
                    input += '<input type="hidden" name="file_size" value="' + res.size + '">';
                    input += '<input type="hidden" name="file_url" value="' + res.path + '">';
                    var html = '<tr class="fileDataTr">';
                    html += '<td class="fileName">' + input + res.formerly + '</td>';
                    html += '<td class="addFileuser">' + res.adduser + '</td>';
                    html += '<td class="addFileTime">' + res.thisTime + '</td>';
                    html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
                    addFileTbody.append(html);
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = $('.addFileTbody');
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
        });

        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).siblings('input[name="path"]').val();
            var name = $(this).siblings('input[name="name"]').val();
            var ext = $(this).siblings('input[name="ext"]').val();
            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + '相关文件查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url + '?path=' + path + '&filename=' + name + '.' + ext]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
        });

        //已添加的设备
        var old_add_assets = [];
        //点击添加采购设备明细
        $(document).on('click', '#addPurchaseAssets', function () {
            layer.open({
                id: 'addOLSContractOpenAddAssets',
                type: 1,
                title: '添加采购设备明细',
                area: ['670px', '310px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addPurchaseAssetsDiv'),
                success: function (layero, index) {
                    //复位
                    resetAddPurchaseAssets();
                    form.on('submit(addPurchaseAssets)', function (data) {
                        var params = data.field;
                        if (old_add_assets[params.assets_name] === true) {
                            layer.msg(params.assets_name + '已添加请勿重复添加', {icon: 2}, 1000);
                            return false;
                        }
                        if (!params.assets_name) {
                            layer.msg('请选择补充需要添加的设备', {icon: 2}, 1000);
                            return false;
                        }
                        if (!params.assets_supplier_id) {
                            layer.msg('请选择补充需要添加的生产厂商', {icon: 2}, 1000);
                            return false;
                        }

                        if (!check_price(params.assets_price)) {
                            layer.msg('请补充单价', {icon: 2}, 1000);
                            return false;
                        }
                        if (!check_num(params.assets_num)) {
                            layer.msg('请补充数量', {icon: 2}, 1000);
                            return false;
                        }
                        params.supplier = $('select[name="assets_supplier_id"]').find('option[value="' + params.assets_supplier_id + '"]').html();
                        params.sum = params.assets_price * params.assets_num;
                        old_add_assets[params.assets_name] = true;
                        var notAssetsDataTr = $('.notAssetsDataTr');
                        if (notAssetsDataTr.length > 0) {
                            notAssetsDataTr.remove();
                        }
                        var addAssetsTbody = $('.addAssetsTbody');
                        var html = '<tr class="assetsDataTr">';
                        html += '<td class="addAssets_assets_td">' + params.assets_name + '</td>';
                        html += '<td class="addAssets_model_td">' + params.model + '</td>';
                        html += '<td class="addAssets_supplier_td">' + '<input type="hidden" name="addAssets_supplier_id" value="' + params.assets_supplier_id + '"><span class="addAssets_supplier_value">' + params.supplier + '</span></td>';
                        html += '<td class="addAssets_price_td">' + params.assets_price + '</td>';
                        html += '<td class="addAssets_num_td">' + params.assets_num + '</td>';
                        html += '<td class="addAssets_sum_td">' + params.sum + '</td>';
                        html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_assets">移除</div></td>';
                        addAssetsTbody.append(html);
                        layer.close(layer.index);
                        layer.msg('添加成功', {icon: 1}, 1000);
                        return false;
                    });
                    form.render('select');
                }
            });
        });

        //移除设备明细
        $(document).on('click', '.del_assets', function () {
            var thisTr = $(this).parents('tr');
            var addAssetsTbody = $('.addAssetsTbody');
            old_add_assets[thisTr.find('.addAssets_assets_td').html()] = false;
            thisTr.remove();
            if (addAssetsTbody.find('tr').length === 0) {
                addAssetsTbody.html('<tr class="notAssetsDataTr"><td colspan="7" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
        });

        //添加明细-补充厂家
        $(document).on('click', '#addAssetsSupplier', function () {
            layer.open({
                id: 'addOLSContractAddSupplier',
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
                            url: addOLSContractUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('input[name="sup_num"]').val(data.result.sup_num);
                                    var html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                    $('select[name="assets_supplier_id"]').append(html);
                                    form.render();
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

        //添加明细-补充设备
        $(document).on('click', '#addAssetsDic', function () {
            layer.open({
                id: 'addOLSContractAddAssetsDicDiv',
                type: 1,
                title: '新增设备字典',
                area: ['450px', '500px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addAssetsDicDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddDic();
                    form.on('submit(addDic)', function (data) {
                        var params = data.field;
                        params.action = 'addDic';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: addOLSContractUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('select[name="assets_name"]').append('<option value="' + params.assets + '">' + params.assets + '</option>');
                                    form.render();
                                    layer.close(layer.index);
                                    layer.msg(data.msg, {icon: 1, time: 3000});
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
                        url: addOLSContractUrl,
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
                        url: addOLSContractUrl,
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


        //初始化已纳入维修设备明细
        function initialRepairTable() {
            table.render({
                elem: '#joinedRepairList'
                , loading: true
                , where: {
                    joinedRepair: joinedRepair,
                    action: 'joinedRepairList'
                }
                , url: addOLSContractUrl //数据接口
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
                , cols: [[ //表头
                    {
                        field: 'repid',
                        title: '序号',
                        width: 50,
                        fixed: 'left',
                        align: 'center',
                        type: 'space',
                        templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    }
                    , {field: 'repnum', fixed: 'left', title: '维修单编号', width: 150, align: 'center'}
                    , {field: 'assets', fixed: 'left', title: '设备名称', width: 170, align: 'center'}
                    , {field: 'model', title: '规格型号', width: 120, align: 'center'}
                    , {field: 'department', title: '报修科室', width: 150, align: 'center'}
                    , {field: 'applicant', title: '申报人', width: 120, align: 'center'}
                    , {field: 'applicant_time', title: '申报时间', width: 120, align: 'center'}
                    , {field: 'response', title: '接单人', width: 100, align: 'center'}
                    , {field: 'response_date', title: '接单日期', width: 130, align: 'center'}
                    , {field: 'breakdown', title: '故障描述', width: 150, align: 'center'}
                    , {field: 'operation', title: '操作', fixed: 'right', width: 100, align: 'center'}
                ]]
                , done: function (res) {
                }
            });
        }

        //初始化已纳入采购设备明细(补录)
        function initialRecordAssetsTable() {
            table.render({
                elem: '#joinedRecordAssetsList'
                , loading: true
                , where: {
                    joinedRecordAssets: joinedRecordAssets,
                    action: 'joinedRecordAssetsList'
                }
                , url: addOLSContractUrl //数据接口
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
                , cols: [[ //表头
                    {
                        field: 'assid',
                        title: '序号',
                        width: 50,
                        fixed: 'left',
                        align: 'center',
                        type: 'space',
                        templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    }
                    , {field: 'assnum', fixed: 'left', title: '设备编码', width: 150, align: 'center'}
                    , {field: 'assets', fixed: 'left', title: '设备名称', width: 180, align: 'center'}
                    , {field: 'model', title: '规格/型号', width: 150, align: 'center'}
                    , {field: 'serialnum', title: '资产序列号', width: 150, align: 'center'}
                    , {field: 'brand', title: '品牌', width: 150, align: 'center'}
                    , {field: 'unit', title: '单位', width: 100, align: 'center'}
                    , {field: 'buy_price', title: '设备原值', width: 120, align: 'center'}
                    , {field: 'factorydate', title: '出厂日期', width: 120, align: 'center'}
                    , {field: 'storage_date', title: '入库日期', width: 120, align: 'center'}
                    , {field: 'capitalfrom', title: '资金来源', width: 100, align: 'center'}
                    , {field: 'assfromid', title: '设备来源', width: 100, align: 'center'}
                    , {field: 'operation', title: '操作', fixed: 'right', width: 100, align: 'center'}
                ]]
                , done: function (res) {

                }
            });
        }


        //复位添加字典Div from
        var getCat = true;

        function resetAddDic() {
            var catid_obj = $('select[name="catid"]');
            $('input[name="assets"]').val('');
            formSelects.value('assets_category', []);
            $('input[name="dic_category"]').val('');
            $('input[name="unit"]').val('');
            catid_obj.val('');
            if (getCat === true) {
                getDicCategory();
                var cathtml = initsuggestCate(current_hosid);
                catid_obj.html('');
                catid_obj.html(cathtml);
            }
            form.render();
        }

        //复位添加厂商Div from
        function resetAddSuppliers() {
            var catid_obj = $('select[name="catid"]');
            $('input[name="sup_name"]').val('');
            formSelects.value('suppliers_type', []);
            $('input[name="salesman_name"]').val('');
            $('input[name="salesman_phone"]').val('');
            $('input[name="address"]').val('');
            catid_obj.val('');
            $('select[name="provinces"]').val('');
            $('select[name="city"]').html('<option value="">请选择省份</option>');
            $('select[name="areas"]').html('<option value="">请选择城市</option>');
            if (getCat === true) {
                getDicCategory();
                var cathtml = initsuggestCate(current_hosid);
                catid_obj.html('');
                catid_obj.html(cathtml);
            }

            form.render();
        }

        //复位添加采购设备明细Div from
        function resetAddPurchaseAssets() {
            $('select[name="assets_name"]').val('');
            $('input[name="model"]').val('');
            $('select[name="assets_supplier_id"]').val('');
            $('input[name="assets_price"]').val('');
            $('input[name="assets_num"]').val('');
            form.render();
        }

        //获取采购设备明细
        function getAddPurchaseAssetsData(params) {
            //获取上传文件信息
            var assetsDataTr = $('.assetsDataTr');
            if (assetsDataTr.length > 0) {
                params.addAssets_assets = '';
                params.addAssets_model = '';
                params.addAssets_supplier = '';
                params.addAssets_supplier_id = '';
                params.addAssets_price = '';
                params.addAssets_num = '';
                params.addAssets_sum = 0;
                $.each(assetsDataTr, function (key, value) {
                    params.addAssets_assets += $(value).find('.addAssets_assets_td').html() + '|';
                    params.addAssets_model += $(value).find('.addAssets_model_td').html() + '|';
                    params.addAssets_supplier += $(value).find('.addAssets_supplier_value').html() + '|';
                    params.addAssets_supplier_id += $(value).find('input[name="addAssets_supplier_id"]').val() + '|';
                    params.addAssets_price += $(value).find('.addAssets_price_td').html() + '|';
                    params.addAssets_num += $(value).find('.addAssets_num_td').html() + '|';
                    params.addAssets_sum += Number($(value).find('.addAssets_sum_td').html());
                });
            }
            return params;
        }

        //获取分类
        function initsuggestCate(id) {
            var html = '<option value=""></option>';
            $.ajax({
                type: "POST",
                url: admin_name+'/Public/getAllCategorySearch?hospital_id=' + id,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                //成功返回之后调用的函数
                async: false,
                success: function (data) {
                    getCat = false;
                    if (data.value.length > 0) {
                        $.each(data.value, function (i, item) {
                            if (item.parentid > 0) {
                                html += '<option value="' + item.catid + '"> ➣ ' + item.category + '</option>';
                            } else {
                                html += '<option value="' + item.catid + '">' + item.category + '</option>';
                            }
                        });
                    } else {
                        html = '';
                    }
                }
            });
            return html;
        }

        //选择字典类型
        function getDicCategory() {
            $("#dic_category_add").bsSuggest('init',
                returnDicCategory(current_hosid)
            );
        }

        //设备名称搜索建议
        $("#addOLSContractSearchListAssets").bsSuggest(
            returnAssets()
        ).on('onDataRequestSuccess', function (e, result) {
            //console.log('onDataRequestSuccess: ', result);
        }).on('onSetSelectValue', function (e, keyword, data) {
        }).on('onUnsetSelectValue', function () {
            //不正确
        });

        //设备名称搜索建议
        $("#addOLSContractSearchdRecordAssets").bsSuggest(
            returnAssets()
        );
        //设备名称搜索建议
        $("#addOLSContractSearchdRecordModel").bsSuggest(
            returnAssetsModel()
        );


    });
    exports('controller/offlineSuppliers/offlineSuppliers/addOLSContract', {});
});
