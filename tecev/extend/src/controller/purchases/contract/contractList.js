layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer,
            form = layui.form,
            formSelects = layui.formSelects,
            laydate = layui.laydate,
            table = layui.table,
            suggest = layui.suggest, tablePlug = layui.tablePlug;

        //渲染多选下拉
        formSelects.render();
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //录入时间元素渲染
        laydate.render({
            elem: '#adddate' //指定元素
        });


        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#contractList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: contractListUrl //数据接口
            , where: {
                sort: 'record_id',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'record_id' //排序字段，对应 cols 设定的各字段名
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
            , toolbar: '#LAY-Purchases-Contract-contractListBar'

            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
                {
                    field: 'record_id',
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
                    field: 'assets_name',
                    hide: get_now_cookie(userid + cookie_url + '/assets_name') == 'false' ? true : false,
                    title: '设备名称',
                    width: 180,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center'
                },
                {field: 'supplier_name',hide: get_now_cookie(userid+cookie_url+'/supplier_name')=='false'?true:false, title: '供应商', width: 240, align: 'center'},
                {field: 'factory_name',hide: get_now_cookie(userid+cookie_url+'/factory_name')=='false'?true:false, title: '生产厂家', width: 240,align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '型号', width: 140, align: 'center'},
                {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 140, align: 'center'},
                {field: 'company_price',hide: get_now_cookie(userid+cookie_url+'/company_price')=='false'?true:false, title: '单价', width: 120, align: 'center'},
                {field: 'nums',hide: get_now_cookie(userid+cookie_url+'/nums')=='false'?true:false, title: '数量', width: 100, align: 'center'},
                {field: 'total_price',hide: get_now_cookie(userid+cookie_url+'/total_price')=='false'?true:false, title: '总金额', width: 120, align: 'center'},
                {field: 'guarantee_year',hide: get_now_cookie(userid+cookie_url+'/guarantee_year')=='false'?true:false, title: '保修期限(年)', width: 120, align: 'center'},
                // {field: 'operation', title: '操作', minWidth: 160,fixed: 'right',align: 'center'}
            ]]
        });


        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
            var key=data.elem.name;
            var status=data.elem.checked;
            document.cookie=userid+cookie_url+'/'+key+'='+status+"; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }
           // 
        });
        //操作栏按钮
        table.on('tool(contractData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            switch (layEvent){
                case 'showContract'://处理标书
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.supplier+'】查看合同详情',
                        area: ['780px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?id='+rows.id]
                    });
                    break;
                case 'addContract'://编辑主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.supplier+'】生成合同',
                        area: ['1040px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?id='+rows.id]
                    });
                    break;
            }
        });

        //列排序
        table.on('sort(contractData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            table.reload('contractList', {
                initSort: obj
                ,where: {
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(contractListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('contractList', {
                url: contractListUrl
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('toolbar(contractData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addContract'://新增厂家
                    var checkStatus = table.checkStatus('contractList');
                    var data = checkStatus.data;
                    if(data.length === 0){
                        layer.msg('请选择需要生成合同的招标记录！',{icon : 2,time:2000});
                        return false;
                    }
                    var record_id='';
                    var error=false;
                    var intsupplier='';
                    $.each(data,function (key,value) {
                        if(intsupplier){
                            if(intsupplier!==value.supplier_name){
                                error=true;
                                return false;
                            }
                        }else{
                            intsupplier=value.supplier_name;
                        }
                        record_id+=','+value.record_id;
                    });
                    if(error){
                        layer.msg('请选择同一个供应商！',{icon : 2,time:1000});
                        return false;
                    }
                    record_id = record_id.substring(1);
                    top.layer.open({
                        id: 'addContract',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1080px', '100%'],
                        closeBtn: 1,
                        content: [url+'?record_id='+record_id],
                        end: function () {
                            if (flag) {
                                table.reload('contractList', {
                                    url: contractListUrl
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

        /*
         /选择供应商
         */
        $("#contract_supplier").bsSuggest(
            getAllSupplierFactoryOrRepair('supplier')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="supplier_id"]').val(data.olsid);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });

        /*
         /选择设备字典名称
         */
        $("#contract_assets_name").bsSuggest(
            returnDicAssets()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="unit"]').val(data.unit);
            $('input[name="asname"]').val('1');
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });

    });
    exports('purchases/contract/contractList', {});
});