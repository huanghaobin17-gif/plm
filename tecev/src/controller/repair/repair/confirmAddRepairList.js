layui.define(function(exports){
    layui.use(['form', 'table', 'suggest', 'element', 'tablePlug'], function () {
        var form = layui.form, table = layui.table, suggest = layui.suggest, element = layui.element, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};

        table.render({
            elem: '#confirmAddRepairList'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: admin_name+'/Repair/getAssetsLists.html' //数据接口
            ,where: {
                action:'confirmAddRepairList'
                ,sort: 'confirmId'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'confirmId' //排序字段，对应 cols 设定的各字段名
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            }
            //,page: true //开启分页
            ,cols: [ //表头
                [
                    {
                        field: 'confirmId',
                        title: '序号',
                        width: 70,
                        fixed: 'left',
                        align: 'center',
                        style: 'background-color: #f9f9f9;',
                        type:  'space',
                        templet: function(d){
                            return d.LAY_INDEX;
                        }
                    },
                    {field: 'assnum', title: '设备编号', width: 150, align: 'center'}
                    , {field: 'assets', title: '设备名称', width: 180, align: 'center'}
                    , {field: 'department_name', title: '使用科室', width: 110, align: 'center'}
                    , {field: 'patroluser', title: '巡查人员', width: 150, align: 'center'}
                    , {field: 'patrolname', title: '巡查计划名称', width: 180, align: 'center'}
                    , {field: 'patrol_level', title: '保养级别', width: 140, align: 'center'}
                    , {field: 'abnormalText', title: '报修原因', width: 260, align: 'center'}
                    , {field: 'model', title: '规格型号', width: 130, align: 'center'}
                    , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 100,
                    align: 'center'
                }
                ]
            ]
        });

        //操作栏按钮
        table.on('tool(confirmAddRepairList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'confirmed'://编辑主设备详情
                    gloabOptions.action='confirmAddRepairList';
                    top.layer.open({
                        type: 2,
                        title: '确认转至报修-'+rows.assets,
                        area: ['980px', '100%'],
                        shade: 0,
                        anim:2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url + '?confirmId=' + rows.confirmId+'&action=confirmAddRepair'],
                        end: function () {
                            if(flag){
                                table.reload('confirmAddRepairList', {
                                    url: admin_name+'/Repair/getAssetsLists.html'
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
            }
        });

        //列排序
        table.on('sort(confirmAddRepairList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('confirmAddRepairList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(confirmAddRepairListSearch)', function(data){
            gloabOptions = data.field;
            gloabOptions.action='confirmAddRepairList';
            var table = layui.table;
            table.reload('confirmAddRepairList', {
                url: admin_name+'/Repair/getAssetsLists.html'
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });



        //建议性搜索计划名称
        $("#confirmAddRepairListPatrolname").bsSuggest(
            returnProject(1)
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='adduser']").val(data.adduser);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='adduser']").val('');
        });

        //选择设备
        $("#confirmAddRepairListAssets").bsSuggest(
            returnAssets()
        );
        //建议性搜索执行人
        $("#confirmAddRepairListPatroluser").bsSuggest(
            returnExecutor()
        );
        //建议性搜索科室
        $("#confirmAddRepairListDepartment").bsSuggest(
            returnDepartment()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='patrolname']").val(data.patrolname);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='patrolname']").val('');
        });
    });
    exports('repair/repair/confirmAddRepairList', {});
});


