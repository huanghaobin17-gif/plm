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
            elem: '#partsOutWareListStartDate' //指定元素
        });
        laydate.render({
            elem: '#partsOutWareListEndDate' //指定元素
        });

        var rowspandiv = '<table class="outwareDetail_table"><tr><td colspan="3" class="QualificationsTd">配件出库信息</td></tr><tr><td class="outware_parts">配件名称</td><td class="outware_parts_model">配件型号</td><td class="outwareSum">出库数量</td></tr></table>';


        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#partsOutWareList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: partsOutWareListUrl //数据接口
            , where: {
                sort: 'outwareid',
                order: 'DESC'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'outwareid' //排序字段，对应 cols 设定的各字段名
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
            , toolbar: '#LAY-Repair-RepairParts-partsOutWareListbar'
            , defaultToolbar: false
            , cols: [[ //表头
                {
                    field: 'outwareid',
                    title: '序号',
                    width: 65,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'outware_num', title: '出库单号', width: 140, align: 'center'},
                {field: 'outwareDetail', title: rowspandiv, align: 'center', width: 470},
                {field: 'sum', title: '出库总数量', width: 100, align: 'center'},
                {field: 'total_price', title: '出库总金额', width: 100, align: 'center'},
                {field: 'outdate', title: '出库日期', width: 100, align: 'center'},
                {field: 'leader', title: '领用人', width: 120, align: 'center'},
                {field: 'remark', title: '备注', width: 250, align: 'center'},
                {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    minWidth: 100,
                    fixed: 'right',
                    align: 'center'
                }
            ]], done: function (res) {
                //避免因为选项卡原因(会出现很多同名的元素)获取到其他页面的元素 先找到对应页面的父元素
                var thisTableList=$('#LAY-Repair-RepairParts-partsOutWareList');
                var outwareDetail=thisTableList.find("td[data-field='outwareDetail']");
                var t_height='0';
                if(outwareDetail){
                    $.each(outwareDetail, function (key, value) {
                        var html='';
                        if(res.rows[key].outwareDetail){
                            html = '<table class="table_center_td outwareDetail_table">';
                            $.each(res.rows[key].outwareDetail, function (result_k, result_v) {
                                html += '<tr><td class="outware_parts"><span>' + result_v.parts + '</span></td><td class="outware_parts_model"><span>' + result_v.parts_model + '</span></td><td class="outwareSum">' + result_v.sum + '</td></tr>';
                            });
                            html += '</table>';
                        }else{
                            html = '<table class="table_center_td outwareDetail_table"><tr><td class="outware_parts">&nbsp;</td><td class="outware_parts_model"></td><td class="outwareSum"></td></tr>';
                        }
                        $(value).find('div').html(html);
                        $(value).css('padding', 0);
                        //var height = $(thisTableList.find("td[data-field='outwareDetail']")[key]).css('height');
                        //获取精确的表格高度
                        var height = $(thisTableList).find("td[data-field='outwareDetail']")[key].getBoundingClientRect().height.toFixed(2);
                        t_height = Number(t_height)+Number(height);
                        height = height + 'px';
                        /*$(thisTableList.find('.layui-table-fixed-l .layui-table-body')).css('height', t_height);
                        $(thisTableList.find('.layui-table-fixed-r .layui-table-body')).css('height', t_height);*/
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


        table.on('tool(partsOutWareData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var flag = 1;
            switch (layEvent) {
                case 'partsOutWare'://编辑主设备详情
                    top.layer.open({
                        id: 'partsOutWare',
                        type: 2,
                        title: '出库申请信息【' + rows.outware_num + '】',
                        area: ['850px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?action=partsOutWareApply&outwareid=' + rows.outwareid],
                        end: function () {
                            if (flag) {
                                table.reload('partsOutWareList', {
                                    url: partsOutWareListUrl
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
                        id: 'showPartsOutwareDetails',
                        type: 2,
                        title: '出库单信息【' + rows.outware_num + '】',
                        area: ['700px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')]
                    });
                    break;
            }
        });

        table.on('toolbar(partsOutWareData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addPartsOutWare'://新增出库单
                    top.layer.open({
                        id: 'addPartsOutWare',
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
                                table.reload('partsOutWareList', {
                                    url: partsOutWareListUrl
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
        form.on('submit(partsOutWareListSearch)', function(data){
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('入库时间设置不合理', {icon: 2});
                    return false;
                }
            }
            table.reload('partsOutWareList', {
                url: partsOutWareListUrl
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //出库单号建议搜索
        $("#partsOutWareListOutware_num").bsSuggest(
            getOutwareNum()
        );
    });
    exports('repair/repairParts/partsOutWareList', {});
});
