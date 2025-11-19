layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate,
            table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;
        //渲染多选下拉
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();
        //录入时间元素渲染
        laydate.render({
            elem: '#partsInWareListStartDate' //指定元素
        });
        laydate.render({
            elem: '#partsInWareListEndDate' //指定元素
        });


        var rowspandiv = '<table class="inwareDetail_table">' +
            '<tr>' +
            '<td colspan="5" class="QualificationsTd">配件入库信息</td>' +
            '</tr>' +
            '<tr>' +
            '<td class="inware_supplier">供应商</td>' +
            '<td class="inware_parts">配件名称</td>' +
            '<td class="inware_parts_model">配件型号</td>' +
            '<td class="inwarePrice">单价（元）</td>' +
            '<td class="inwareSum">入库数量</td>' +
            '<td class="inwareTotal">总价（元）</td>' +
            '</tr>' +
            '</table>';

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#partsInWareList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: partsInWareListUrl //数据接口
            , where: {
                sort: 'buydate',
                order: 'DESC'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'buydate' //排序字段，对应 cols 设定的各字段名
                , type: 'DESC' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            }
            //,page: true //开启分页
            , toolbar: '#LAY-Repair-RepairParts-partsInWareListbar'
            , defaultToolbar: false
            , cols: [[ //表头
                {
                    field: 'inwareid',
                    title: '序号',
                    width: 65,
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'inware_num', title: '入库单号', width: 110, align: 'center'},
                {field: 'buydate', title: '入库日期', width: 100, align: 'center'},
                {field: 'inwareDetail', title: rowspandiv, align: 'center', width: 900},
                {field: 'sum', title: '入库总数量', width: 100, align: 'center'},
                {field: 'total_price', title: '入库总金额', width: 100, align: 'center'},
                {field: 'remark', title: '备注', minWidth: 250, align: 'center'},
                {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    minWidth: 65,
                    fixed: 'right',
                    align: 'center'
                }
            ]], done: function (res) {
                //避免因为选项卡原因(会出现很多同名的元素)获取到其他页面的元素 先找到对应页面的父元素
                var thisTableList=$('#LAY-Repair-RepairParts-partsInWareList');
                var inwareDetail=thisTableList.find("td[data-field='inwareDetail']");
                var t_height='0';
                if(inwareDetail){
                    $.each(inwareDetail, function (key, value) {
                        var html='';
                        if(res.rows[key].inwareDetail){
                            html = '<table class="table_center_td inwareDetail_table">';
                            $.each(res.rows[key].inwareDetail, function (result_k, result_v) {
                                if(typeof(result_v.price)==='undefined'){
                                    result_v.price='/';
                                    result_v.total_price='/';
                                }
                                if(typeof(result_v.supplier_name)==='undefined'){
                                    result_v.supplier_name='/';
                                }
                                html += '<tr>' +
                                    '<td class="inware_supplier"><span>' + result_v.supplier_name + '</span></td>' +
                                    '<td class="inware_parts"><span>' + result_v.parts + '</span></td>' +
                                    '<td class="inware_parts_model"><span>' + result_v.parts_model + '</span></td>' +
                                    '<td class="inwarePrice">' + result_v.price + '</td>' +
                                    '<td class="inwareSum">' + result_v.sum + '</td>' +
                                    '<td class="inwareTotal">' + result_v.total_price + '</td>' +
                                    '</tr>';
                            });
                            html += '</table>';
                        }else{
                            html = '<table class="table_center_td inwareDetail_table">' +
                                '<tr>' +
                                '<td class="inware_supplier">&nbsp;</td>' +
                                '<td class="inware_parts">&nbsp;</td>' +
                                '<td class="inware_parts_model"></td>' +
                                '<td class="inwarePrice"></td>' +
                                '<td class="inwareSum"></td>' +
                                '<td class="inwareTotal"></td>' +
                                '</tr>';
                        }
                        $(value).find('div').html(html);
                        $(value).css('padding', 0);
                        //var height = $(thisTableList.find("td[data-field='inwareDetail']")[key]).css('height');
                        var height = $(thisTableList).find("td[data-field='inwareDetail']")[key].getBoundingClientRect().height.toFixed(2);
                        t_height = Number(t_height)+Number(height);
                        height = height + 'px';
                        $(thisTableList.find('.layui-table-fixed-l').find('td')[key]).css('height', height);
                        $(thisTableList.find('.layui-table-fixed-r').find('td')[key]).css('height', height);
                    });
                    $(thisTableList.find('.layui-table-fixed-r')).css('height', '100%');
                    $(thisTableList.find('.layui-table-fixed-r')).css('height', '100%');
                    $(thisTableList.find('.layui-table-fixed-l .layui-table-body')).css('height', t_height + 'px');
                    $(thisTableList.find('.layui-table-fixed-r .layui-table-body')).css('height', t_height + 'px');
                }
                var table = $('.QualificationsTd').parents('th');
                table.css('padding', 0);
            }
        });


        table.on('tool(partsInWareData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var flag = 1;
            switch (layEvent) {
                case 'partsInWare'://编辑主设备详情
                    top.layer.open({
                        id: 'partsInWare',
                        type: 2,
                        title: '入库申请信息【' + rows.inware_num + '】',
                        area: ['1100px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?action=partsInWareApply&inwareid=' + rows.inwareid],
                        end: function () {
                            if (flag) {
                                table.reload('partsInWareList', {
                                    url: partsInWareListUrl
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'showDetails':
                    top.layer.open({
                        id: 'showPartsInwareDetails',
                        type: 2,
                        title: '入库单信息【' + rows.inware_num + '】',
                        area: ['900px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')]
                    });
                    break;
                case 'editInware'://修改入库单
                    top.layer.open({
                        id: 'showPartsInwareDetails',
                        type: 2,
                        title: '修改入库单信息【' + rows.inware_num + '】',
                        area: ['900px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')]
                    });
                    break;

            }
        });

        table.on('toolbar(partsInWareData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addPartsInWare'://新增入库单
                    top.layer.open({
                        id: 'addPartsInWare',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1100px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if (flag) {
                                table.reload('partsInWareList', {
                                    url: partsInWareListUrl
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
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

        //搜索按钮
        form.on('submit(partsInWareListSearch)', function(data){
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('入库时间设置不合理', {icon: 2});
                    return false;
                }
            }
            table.reload('partsInWareList', {
                url: partsInWareListUrl
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //入库单号建议搜索
        $("#partsInWareListInware_num").bsSuggest(
            getInwareNum()
        );

        /*
        /选择供应商
        */
        //$("#dic_supplier").bsSuggest(
        //    getAllSupplierFactoryOrRepair('supplier')
        //).on('onSetSelectValue', function (e, keyword, data) {
        //    $('input[name="supplier_id"]').val(data.olsid);
        //}).on('onUnsetSelectValue', function () {
        //    //不正确
        //    $('input[name="asname"]').val('0');
        //});
    });
    exports('repair/repairParts/partsInWareList', {});
});
