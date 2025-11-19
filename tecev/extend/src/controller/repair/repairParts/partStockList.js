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
            elem: '#partStockListStartDate' //指定元素
        });
        laydate.render({
            elem: '#partStockListEndDate' //指定元素
        });


        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#partStockList'
            //,height: '600'
            , limits: [20, 50, 100]
            , loading: true
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            , url: partStockListUrl //数据接口
            , where: {
                sort: 'parts',
                order: 'DESC'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'parts' //排序字段，对应 cols 设定的各字段名
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
            , defaultToolbar: false
            , cols: [[ //表头
                {
                    field: 'parts',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'parts', title: '配件名称', fixed: 'left',width: 150, align: 'center'},
                {field: 'parts_model', title: '配件型号',fixed: 'left', width: 120, align: 'center'},
                {field: 'max_sum', title: '库存数量',fixed: 'left', width: 100, align: 'center'},
                {field: 'price', title: '配件单价',fixed: 'left', width: 100, align: 'center'},
                {field: 'total_price', title: '配件总金额', width: 130, align: 'center'},
                {field: 'supplier_name', title: '供应商', width: 250, align: 'center'},
                {field: 'leader', title: '库管/领用人', width: 110, align: 'center'},
                {field: 'unit', title: '单位', width: 75, align: 'center'},
                {field: 'brand', title: '品牌', minWidth: 180, align: 'center'}
            ]], done: function (res, curr) {
                var pages = this.page.pages;
                var thisId = '#' + this.id;
                if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else {
                    table.resize(this.id); //重置表格尺寸
                }
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
                        area: ['1100px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?action=partsOutWareApply&outwareid=' + rows.outwareid],
                        end: function () {
                            if (flag) {
                                table.reload('partStockList', {
                                    url: partStockListUrl
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
                                table.reload('partStockList', {
                                    url: partStockListUrl
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
        form.on('submit(partStockListSearch)', function(data){
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('入库时间设置不合理', {icon: 2});
                    return false;
                }
            }
            table.reload('partStockList', {
                url: partStockListUrl
                ,where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }, done: function (res, curr) {
                    var pages = this.page.pages;
                    var thisId = '#' + this.id;
                    if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else {
                        table.resize(this.id); //重置表格尺寸
                    }
                }
            });
            return false;
        });

        //出库单号建议搜索
        $("#partStockListParts").bsSuggest(
            returnPartsDic()
        );

        /*
        /选择供应商
        */
        $("#stock_supplier").bsSuggest(
            getAllSupplierFactoryOrRepair('supplier')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="supplier_id"]').val(data.olsid);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });
    });
    exports('repair/repairParts/partStockList', {});
});
