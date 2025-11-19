layui.define(function(exports){
    layui.use(['form', 'table', 'laydate', 'formSelects', 'suggest', 'tablePlug'], function () {
        var $ = layui.$
            , laydate = layui.laydate
            , formSelects = layui.formSelects
            , table = layui.table
            ,suggest = layui.suggest
            , form = layui.form
            , tablePlug = layui.tablePlug;
        form.render();
        suggest.search();
        //管理科室 多选框初始配置
        formSelects.render('presence', selectParams(1));
        formSelects.btns('presence',selectParams(2));
        laydate.render({
            elem: '#out_date' //指定元素
            ,max: nowday
        });
        laydate.render({
            elem: '#installStartDate' //指定元素
            ,min: nowday
        });
        laydate.render({
            elem: '#installEendDate' //指定元素
            ,min: nowday
        });
        var departid = 0;
        getLists(departid);
        function getLists(departid) {
            table.render({
                elem: '#addOutLists'
                //,height: '600'
                , limits: [10, 20, 50, 100]
                , loading: true
                , limit: 10
                , url: addOut //数据接口
                , where: {
                    sort: 'ware_assets_id',
                    order: 'asc',
                    action: 'getLists',
                    departid: departid
                } //如果无需传递额外参数，可不加该参数
                , initSort: {
                    field: 'ware_assets_id' //排序字段，对应 cols 设定的各字段名
                    , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                        field: 'ware_assets_id',
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
                    {
                        field: 'assets_num',
                        title: '设备编号',
                        width: 160,
                        style: 'background-color: #f9f9f9;',
                        fixed: 'left',
                        align: 'center'
                    },
                    {
                        field: 'assets_name',
                        title: '设备名称',
                        width: 160,
                        style: 'background-color: #f9f9f9;',
                        fixed: 'left',
                        align: 'center'
                    },
                    {field: 'model', title: '设备型号', width: 120, align: 'center'},
                    {field: 'factory', title: '生产厂家', width: 240,align: 'center'},
                    {field: 'factorynum', title: '出厂编号', width: 120, align: 'center'},
                    {field: 'category', title: '设备分类', width: 160, align: 'center'},
                    {field: 'check_date', title: '验收日期', width: 110, align: 'center'},
                    {field: 'unit', title: '单位', width: 80, align: 'center'},
                    {field: 'buy_price', title: '单价', width: 100, align: 'center'}
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
        form.on('submit(saveOut)', function(data){
            var params = data.field;
            params.action = 'saveOut';
            var checkStatus = table.checkStatus('addOutLists');
            //获取选中行数量，可作为是否有选中行的条件
            var length = checkStatus.data.length;
            if (length == 0) {
                top.layer.msg('请选择要出库的设备！', {icon: 2});
                return false;
            }
            var id = '';
            for (var i = 0; i < length; i++) {
                var tmpId = checkStatus.data[i]['ware_assets_id'];
                id += tmpId + ',';
            }
            id = id.substring(0, id.length - 1);
            params.assets_ids = id;
            if(!params.installStartDate || !params.installEendDate){
                top.layer.msg('调试时间不能为空！', {icon: 2});
                return false;
            }
            if(params.installStartDate > params.installEendDate){
                top.layer.msg('调试时间设置不合理！', {icon: 2});
                return false;
            }
            submit($,params,'addOut');
            return false;
        });
        //科室搜索建议
        $("#outDepartment").bsSuggest(
            returnDepartment()
        ).on('onSetSelectValue', function (e, keyword, data) {
            departid = data.departid;
            $('input[name="departid"]').val(departid);
            getLists(data.departid);
        });
    });
    exports('controller/purchases/purchaseCheck/addOut', {});
});
