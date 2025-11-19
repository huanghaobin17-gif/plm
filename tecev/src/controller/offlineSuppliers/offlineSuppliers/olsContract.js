layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer,
            form = layui.form,
            formSelects = layui.formSelects,
            laydate = layui.laydate,
            table = layui.table,
            suggest = layui.suggest
            , tablePlug = layui.tablePlug;

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
            elem: '#olsContractList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: olsContractUrl //数据接口
            , where: {
                sort: 'contract_id',
                order: 'DESC'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'contract_id' //排序字段，对应 cols 设定的各字段名
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
            , toolbar: '#LAY-OfflineSuppliers-OfflineSuppliers-olsContractbar'
            ,defaultToolbar: false
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
                    field: 'contract_id', title: '序号', width: 60, fixed: 'left', align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'contract_num', title: '合同编号', width: 180,fixed: 'left', align: 'center'},
                {field: 'supplier_name', title: '乙方单位', width: 240,fixed: 'left',align: 'center'},
                {field: 'contract_name', title: '合同名称', width: 300, align: 'center'},
                {field: 'supplier_contacts', title: '乙方联系人', width: 140, align: 'center'},
                {field: 'supplier_phone', title: '联系电话', width: 140, align: 'center'},
                {field: 'contract_type_name', title: '合同类型', width: 150, align: 'center'},
                {field: 'sign_date', title: '签订日期', width: 130, align: 'center'},
                {field: 'end_date', title: '截止日期', width: 130, align: 'center'},
                {field: 'contract_content', title: '合同内容', width: 300, align: 'center'},
                {field: 'operation', title: '操作', minWidth: 95,fixed: 'right',align: 'center'}
            ]]
        });
        //操作栏按钮
        table.on('tool(olsContractData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            switch (layEvent){
                case 'showDetails':
                    var width='800px';
                    if(rows.contract_type===CONTRACT_TYPE_RECORD_ASSETS){
                        width='1150px';
                    }
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.contract_name+'】查看合同详情',
                        area: [width, '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')]
                    });
                    break;
                case 'confirmContract'://编辑主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.contract_num+'】确认合同信息',
                        area: ['1150px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?contract_id='+rows.contract_id+'&type='+rows.contract_type],
                        end: function () {
                            if (flag) {
                                table.reload('olsContractList', {
                                    url: olsContractUrl
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
        form.on('submit(olsContractListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('olsContractList', {
                url: olsContractUrl
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });


        table.on('toolbar(olsContractData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addOLSContract'://新增厂家
                    top.layer.open({
                        id: 'addOLSContract',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1080px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if (flag) {
                                table.reload('olsContractList', {
                                    url: olsContractUrl
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

        //乙方名称搜索建议
        $("#olsContractSupName").bsSuggest(
            getOfflineSuppliersName('')
        );

        //乙方名称搜索建议
        $("#olsContractContractName").bsSuggest(
            getOLSContractContractName('')
        );
    });
    exports('offlineSuppliers/offlineSuppliers/olsContract', {});
});