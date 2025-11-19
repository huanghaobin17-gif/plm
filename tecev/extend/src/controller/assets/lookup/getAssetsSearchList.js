layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();
        laydate.render(dateConfig('#getAssetsSearchListAssetsDate')); //入库日期
        laydate.render(dateConfig('#getAssetsSearchListOpendate'));//启用日期元素渲染
        laydate.render(dateConfig('#getAssetsSearchListFactorydate'));//出厂日期元素渲染
        //先更新页面部分需要提前渲染的控件
        form.render();
        //数据表格
        table.render({
            elem: '#AssetsSearchList'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: admin_name+'/Lookup/getAssetsSearchList.html' //数据接口
            ,where: {
                sort: 'assid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                ,type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            }
            //,page: true //开启分页
            ,cols: [[ //表头
                {
                    type:  'checkbox',
                    fixed: 'left'
                }, {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'assnum',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '设备编号',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    title: '设备名称',
                    width: 180,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center'
                }
                , {field: 'category', title: '设备分类', width: 180, align: 'center'}
                , {field: 'model', title: '规格/型号', width: 150, align: 'center'}
                , {field: 'department', title: '使用科室', width: 160, align: 'center'}
                , {field: 'buy_price', title: '原值(元)', width: 130, align: 'center'}
                , {field: 'factorydate', title: '出厂日期', width: 110, align: 'center'}
                , {field: 'opendate', title: '启用日期', width: 110, align: 'center'}
                , {field: 'age', title: '年限', width: 100, align: 'center'}
                , {field: 'as_status', title: '当前状态', width: 100, align: 'center'}
                , {field: 'address', title: '存放地点', width: 100, align: 'center'}
                , {field: '', title: '折旧状态', width: 100, align: 'center'}
                , {field: 'operation', title: '操作', fixed: 'right', width: 100, align: 'center'}
            ]]
        });

        //列排序
        table.on('sort(AssetsSearchList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('AssetsSearchList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }

            });
        });
        //定义一个全局空对象
        var gloabOptions = {};
        //搜索按钮
        form.on('submit(AssetsSearchListSearch)', function(data){
            //console.log(data.field.isfirstaid);
            if(!data.field.isfirstaid){
                //console.log(66);
                delete data.field.isfirstaid;
            }
            console.log(data.field);
            gloabOptions = data.field;
            table.reload('AssetsSearchList', {
                url: admin_name+'/Lookup/getAssetsSearchList.html'
                ,where: data.field
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //操作栏按钮
        table.on('tool(AssetsSearchList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if(layEvent === 'showAssets'){
                //显示主设备详情
                top.layer.open({
                    type: 2,
                    title: '设备详情【'+rows.assets+'】',
                    area: ['99%', '98%'],
                    shade: [0.8, '#393D49'],
                    shadeClose:true,
                    anim:5,
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?assid='+rows.assid]
                });
            }
        });


        //设备名称搜索建议
        $("#getAssetsSearchListAssets").bsSuggest(
            returnAssets()
        );

        //科室搜索建议
        $("#getAssetsSearchListDepartment").bsSuggest(
            returnDepartment()
        );

        //分类搜索建议
        $("#getAssetsSearchListCategory").bsSuggest(
            returnCategory()
        );

        //设备编号搜索建议
        $("#getAssetsSearchListAssetsNum").bsSuggest(
            returnAssnum()
        );

        //管理科室搜索建议
        $("#getAssetsSearchListManagedepartment").bsSuggest(
            returnDepartment()
        );

        //急救资产多选框
        form.on('checkbox(isfirstaid)', function(data){
            if (data.elem.checked == true){
                $("input[name='checkFirstaid']").val('1')
            }else {
                $("input[name='checkFirstaid']").val('')
            }
        });
        //特种资产多选框
        form.on('checkbox(isspecial)', function(data){
            if (data.elem.checked == true){
                $("input[name='checkSpecial']").val('1')
            }else {
                $("input[name='checkSpecial']").val('')
            }
        });
        //是否资产多选框
        form.on('checkbox(isassets)', function(data){
            if (data.elem.checked == true){
                $("input[name='checkAssets']").val('1')
            }else {
                $("input[name='checkAssets']").val('')
            }
        });
        //计量资产多选框
        form.on('checkbox(ismetering)', function(data){
            if (data.elem.checked == true){
                $("input[name='checkMetering']").val('1')
            }else {
                $("input[name='checkMetering']").val('')
            }
        });


    });
    exports('assets/lookup/getAssetsSearchList', {});
});























