layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'admin', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, admin = layui.admin, tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        //第一次进页面获取的id
        firstId = $("input[name='id']").val();

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#problems'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,where: {
                parentid:firstId
            }
            ,url: typeSetting //数据接口
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
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            }
            //,page: true //开启分页
            ,cols: [[ //表头
                {field: 'title', title: '明细名称', width: 250, align: 'center'},
                {field: 'solve', title: '解决办法', width: 400, align: 'center'},
                {field: 'remark', title: '备注', width: 300, align: 'center'},
                {field: 'operation', title: '操作', width: 90, fixed: 'right', align: 'center'}
            ]]
        });

        //操作栏按钮
        table.on('tool(problems)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'editProblem'://修改故障问题
                    top.layer.open({
                        id: 'editProblems',
                        type: 2,
                        title: '修改故障问题【'+ rows.title +'】',
                        area: ['500px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?id='+rows.id],
                        end: function () {
                            if(flag){
                                table.reload('problems', {
                                    url: typeSetting
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
                case 'deleteProblem'://删除故障问题
                    layer.confirm('故障问题删除后无法恢复，确定删除吗？', {icon: 3, title:'删除故障问题【'+rows.title+'】'}, function(index){
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            data: {id:rows.id},
                            url: url,
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    if (data.status == 1) {
                                        layer.msg(data.msg,{icon : 1,time:2000},function(){
                                            table.reload('problems', {
                                                url: typeSetting
                                                ,where: gloabOptions
                                                ,page: {
                                                    curr: 1 //重新从第 1 页开始
                                                }
                                            });
                                        });
                                    }else{
                                        layer.msg(data.msg,{icon : 2},1000);
                                    }
                                }else {
                                    layer.msg('数据异常',{icon : 2},1000);
                                }
                            },
                            error: function () {
                                layer.msg("网络访问失败！",{icon : 2},1000);
                            }
                        });
                        layer.close(index);
                    });
                    break;
            }
        });

        //列排序
        table.on('sort(problems)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('problems', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(typeSettingSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('problems', {
                url: typeSetting
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //新增类别
        $('#addType').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'addTypes',
                type: 2,
                title: '新增故障类型',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['400px', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    if(flag){
                        layui.index.render();
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });
        //新增故障问题
        $('#addProblem').on('click',function() {
            var name = $(".problemTitle").html();
            var flag = 1;
            var url = $(this).attr('data-url');
            var id = $("input[name='id']").val();
            top.layer.open({
                id: 'addProblems',
                type: 2,
                title: '新增'+ name +'--故障问题',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['600px', '100%'],
                closeBtn: 1,
                content: url+'?id='+id+'&type=addProblem',
                end: function () {
                    if(flag){
                        table.reload('problems', {
                            url: typeSetting
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
            return false;
        });

        //点击类别
        $('.getProblem').on('click',function() {
            //重载表格
            var id = $(this).attr('data-id');
            table.reload('problems', {
                url: typeSetting
                ,where: {parentid:id}
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            //更改显示标题
            $(".problemTitle").html($(this).html());
            //更改父id
            $("input[name='id']").val(id);
            return false;
        });

        //控制子侧边菜单文字太长时省略号变成提示
        var item = $("#LAY-Repair-RepairSetting-typeSetting .layui-nav-item");
        $.each(item,function(j,val){
            if ($(val).children().html().length>18){
                var tips = $(val).children().html();
                $(val).attr("lay-tips",tips)
            }
        });

        //监听伸缩事件，控制子侧边菜单的绝对定位
        admin.on('side(leftChildmenu)', function(obj){
            var layuiSideChild = $("#LAY-Repair-RepairSetting-typeSetting .layui-side-child");
            if (obj.status == null){
                layuiSideChild.css("left",80);
            }else {
                layuiSideChild.css("left",235);
            }
        });

    });
    exports('repair/repairSetting/typeSetting', {});
});

