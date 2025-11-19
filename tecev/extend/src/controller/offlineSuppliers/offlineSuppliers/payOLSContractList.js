layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer
            , table = layui.table
            ,suggest = layui.suggest
            , form = layui.form
            , laydate = layui.laydate
            , tablePlug = layui.tablePlug;

        suggest.search();
        laydate.render(dateConfig('#payContractRealPayDate'));
        form.render();
        layer.config(layerParmas());
        var gloabOptions={};
        table.render({
            elem: '#payOLSContractList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '合同付款列表'
            , url: payOLSContractListUrl //数据接口
            , where: {
                sort: 'pay_id',
                order: 'ASC'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'pay_id' //排序字段，对应 cols 设定的各字段名
                , type: 'ASC' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
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
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: false
            , cols: [[ //表头
                {
                    field: 'pay_id',
                    title: '序号',
                    style: 'background-color: #f9f9f9;',
                    width: 50,
                    align: 'center',
                    type: 'space',
                    fixed: 'left',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'contract_name',
                    title: '合同名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 300,
                    align: 'center'
                },
                {field: 'contract_num', title: '合同编号', width: 180, align: 'center'},
                {field: 'contract_type_name', title: '合同类型', width: 110, align: 'center'},
                {field: 'pay_period', title: '期数', width: 80, align: 'center'},
                {field: 'estimate_pay_date', title: '预计付款日期', width: 120, align: 'center'},
                {field: 'pay_amount', title: '付款金额', width: 120, align: 'center'},
                {field: 'supplier_name', title: '乙方单位', width: 280, align: 'center'},
                {
                    field: 'pay_status_type',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    title: '付款状态',
                    width: 100,
                    align: 'center'
                },
                {
                    field: 'real_pay_date',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    title: '实际付款日期',
                    width: 150,
                    align: 'center'
                },
                {
                    field: 'operation',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    width: 100,
                    align: 'center'
                }
            ]]
        });

        table.on('tool(payOLSContractData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var real_pay_date = $(obj.tr[0]).find('.real_pay_date_v').data('value');
            //避免出现宽度很长的时候 不会出现右边固定 DIV 获取不到的问题
            real_pay_date=real_pay_date?real_pay_date:$(obj.tr[1]).find('.real_pay_date_v').data('value');
            real_pay_date=real_pay_date?real_pay_date:$(obj.tr[2]).find('.real_pay_date_v').data('value');
            var params = {};
            switch (layEvent) {
                case 'payOLSContract':
                    if (!real_pay_date) {
                        layer.msg('请先补充 ' + rows.contract_name + '[ '+rows.contract_num+' ] 第 '+ rows.pay_period +' 期的实际付款日期', {icon: 2});
                        return false;
                    }
                    params.pay_id = rows.pay_id;
                    params.contract_type = rows.contract_type;
                    params.real_pay_date = real_pay_date;
                    do_post(url,params);
                    break;
                default :
                    layer.msg('异常参数', {icon: 2});
            }
            return false;

        });

        function do_post(url,params) {
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: params,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {
                            icon: 1, time: 2000
                        }, function () {
                            layer.closeAll();
                            table.reload('payOLSContractList', {
                                url: payOLSContractListUrl
                                ,where:gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
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
        }

        //确认时间
        form.on('submit(setPayOLSContractListPayDate)', function (data) {
            var focu = $('.focu');
            var params = data.field;
            if (params.real_pay_date) {
                focu.html(params.real_pay_date);
                focu.prev('.real_pay_date_v').data('value', params.real_pay_date);
                layer.msg("成功录入,请点击 ‘确认付款’ 按钮完成付款录入", {
                    icon: 1,
                    time: 2000
                }, function () {
                    layer.closeAll();
                });
                $('input[name="real_pay_date"]').val('');
                return false;
            } else {
                layer.msg("请补充确认借入时间", {icon: 2}, 1000);
                return false;
            }
        });


        //点击 录入时间
        $(document).on('click', '.real_pay_date', function () {
            var thisDiv = $(this);
            thisDiv.addClass('focu');
            layer.open({
                type: 1,
                title: '【实际付款日期】',
                area: ['350px', '200px'],
                offset: 'auto',
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#real_pay_date'),
                end: function () {
                    thisDiv.removeClass('focu');
                }
            });
        });


        //搜索按钮
        form.on('submit(payOLSContractListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('payOLSContractList', {
                url: payOLSContractListUrl
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //合同名称搜索建议
        $("#payOLSContractListContractName").bsSuggest(
            getOLSContractContractName('')
        );

        //乙方名称搜索建议
        $("#payOLSContractListSuppliersName").bsSuggest(
            getOfflineSuppliersName('')
        );

    });
    exports('offlineSuppliers/offlineSuppliers/payOLSContractList', {});
});