layui.define(function(exports){
    layui.use(['form', 'table', 'suggest', 'laydate', 'tablePlug'], function () {
        var $ = layui.$
            ,table = layui.table
            ,suggest = layui.suggest
            ,laydate = layui.laydate
            , form = layui.form
            , tablePlug = layui.tablePlug;
        form.render();
        suggest.search();
        laydate.render({
            elem: '#in_date' //指定元素
            ,max: nowday
        });
        var sup_id = 0;
        getLists(sup_id);
        function getLists(sup_id) {
            table.render({
                elem: '#addWareLists'
                //,height: '600'
                , limits: [10, 20, 50, 100]
                , loading: true
                , limit: 10
                , url: addWare //数据接口
                , where: {
                    sort: 'check_date',
                    order: 'desc',
                    action: 'getLists',
                    sup_id: sup_id
                } //如果无需传递额外参数，可不加该参数
                , initSort: {
                    field: 'check_date' //排序字段，对应 cols 设定的各字段名
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
                , page: false
                //,page: true //开启分页

                , cols: [[ //表头
                    {
                        type: 'checkbox',
                        fixed: 'left'
                    },
                    {
                        field: 'assets_id',
                        title: '序号',
                        width: 80,
                        style: 'background-color: #f9f9f9;',
                        fixed: 'left',
                        align: 'center',
                        type: 'space',
                        templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    },
                    // {field: 'assets_num', title: '设备编号', width: 180,fixed: 'left', align: 'center'},
                    {
                        field: 'assets_name',
                        title: '设备名称',
                        width: 180,
                        style: 'background-color: #f9f9f9;',
                        fixed: 'left',
                        align: 'center'
                    },
                    {field: 'model', title: '设备型号', width: 140, align: 'center'},
                    {field: 'factory', title: '生产厂家', width: 240,align: 'center'},
                    {field: 'factorynum', title: '出厂编号', width: 160, align: 'center'},
                    {field: 'category', title: '设备分类', width: 160, align: 'center'},
                    {field: 'check_date', title: '验收日期', width: 110, align: 'center'},
                    {field: 'unit', title: '单位', width: 100, align: 'center'},
                    {field: 'nums', title: '数量', width: 100, align: 'center'},
                    {field: 'buy_price', title: '单价', width: 120, align: 'center'},
                    {field: 'real_total', title: '总金额', width: 120, align: 'center'}
                ]]
                , done: function (res, curr, count) {
                    if(!$.isEmptyObject(res.rows)){
                        $('#showBtn').show();
                    }else{
                        $('#showBtn').hide();
                    }
                }
            });
        }

        //监听提交
        form.on('submit(addWare)', function(data){
            var params = data.field;
            params.action = 'saveWare';
            var checkStatus = table.checkStatus('addWareLists');
            //获取选中行数量，可作为是否有选中行的条件
            var length = checkStatus.data.length;
            if (length == 0) {
                top.layer.msg('请选择要入库的设备！', {icon: 2});
                return false;
            }
            var id = '';
            for (var i = 0; i < length; i++) {
                var tmpId = checkStatus.data[i]['assets_id'];
                id += tmpId + ',';
            }
            id = id.substring(0, id.length - 1);
            params.assets_ids = id;
            params.in_desc = $('input[name="in_desc"]').val();
            params.in_date = $('input[name="in_date"]').val();
            submit($,params,'addWare');
            return false;
        });
        /*
         /选择供应商
         */
        $("#dic_supplier").bsSuggest(
            getAllSupplierFactoryOrRepair('supplier')
        ).on('onSetSelectValue', function (e, keyword, data) {
            sup_id = data.olsid;
            getLists(sup_id);
        });
    });
    exports('controller/purchases/purchaseCheck/addWare', {});
});
