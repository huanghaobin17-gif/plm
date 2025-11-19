layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //渲染多选下拉
        formSelects.render();
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#qualityReportLists'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: qualityReportLists //数据接口
            , where: {
                sort: 'ware_assets_id',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'ware_assets_id' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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

            , cols: [[ //表头
                {
                    field: 'ware_assets_id',
                    title: '序号',
                    width: 60,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'assets_num',
                    title: '设备编号',
                    width: 180,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center'
                },
                {
                    field: 'assets_name',
                    title: '设备名称',
                    width: 180,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center'
                },
                {field: 'department', title: '领用科室', width: 180, align: 'center'},
                {field: 'category', title: '设备分类', width: 200, align: 'center'},
                {field: 'model', title: '设备型号', width: 200, align: 'center'},
                {field: 'buy_price', title: '设备单价', width: 120, align: 'center'},
                {field: 'supplier', title: '供应商', width: 240, align: 'center'},
                {field: 'factory', title: '生产厂家', width: 240, align: 'center'},
                {field: 'factorynum', title: '出厂编号', width: 180, align: 'center'},
                {field: 'check_date', title: '验收日期', width: 120, align: 'center'},
                {
                    field: 'quality_operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    minWidth: 120,
                    fixed: 'right',
                    align: 'center'
                }
            ]]
        });

        //监听搜索
        form.on('submit(qualityReportListsSearch)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('qualityReportLists', {
                url: qualityReportLists
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        //操作栏按钮
        table.on('tool(qualityReportData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'qualityReport'://上传质量验收报告
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets_name+'】上传设备质量验收报告',
                        area: ['880px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?id='+rows.ware_assets_id],
                        end: function () {
                            if(flag){
                                table.reload('qualityReportLists', {
                                    url: qualityReportLists
                                    ,where: gloabOptions
                                    ,page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'showQuality'://查看质量验收报告
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets_name+'】临床培训查看',
                        area: ['880px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?action=showQuality&id='+rows.ware_assets_id]
                    });
                    break;
            }
        });
        $('#qualityreset').on('click',function () {
            $('input[name="quality_departid"]').val(0);
        });
        //科室搜索建议
        $("#qualityReportListsDepartment").bsSuggest(
            returnDepartment()
        ).on('onSetSelectValue', function (e, keyword, data) {
            departid = data.departid;
            $('input[name="quality_departid"]').val(departid);
        });
        /*
         /选择设备字典名称
         */
        $("#quality_report_assets_name").bsSuggest(
            returnDicAssets()
        );


    });
    exports('purchases/purchaseCheck/qualityReportLists', {});
});