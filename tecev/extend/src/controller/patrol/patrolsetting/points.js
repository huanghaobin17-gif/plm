layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'admin', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, admin = layui.admin, tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#points'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: admin_name+'/PatrolSetting/points?type=getDetail' //数据接口
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
                {field: 'num', title: '编号', width: '11%', align: 'center'}
                , {field: 'name', title: '明细名称', width: '38%', align: 'center'}
                , {field: 'result', title: '默认结果',width: '12%', align: 'center'}
                , {field: 'require', title: '保养内容及要求',width: '27%', align: 'center'}
                , {field: 'operation', title: '操作', width: '12%', fixed: 'right', align: 'center'}
            ]]
        });

        //操作栏按钮
        table.on('tool(points)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'editDetail'://修改明细
                    top.layer.open({
                        id: 'editDetails',
                        type: 2,
                        title: '修改明细【'+ rows.name +'】',
                        area: ['40%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?ppid='+rows.ppid],
                        end: function () {
                            if(flag){
                                table.reload('points', {
                                    url: admin_name+'/PatrolSetting/points?type=getDetail'
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
                case 'deleteDetail'://删除明细
                    var data = {};
                    data.ppid = rows.ppid;
                    layer.confirm('明细删除后无法恢复，确定删除吗？', {icon: 3, title:'删除明细【'+rows.name+'】'}, function(index){
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            data:data,
                            url: url,
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    if (data.status == 1) {
                                        layer.msg(data.msg,{icon : 1,time:2000},function(){
                                            table.reload('points', {
                                                url: admin_name+'/PatrolSetting/points?type=getDetail'
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
        table.on('sort(points)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('points', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(pointsSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('points', {
                url: admin_name+'/PatrolSetting/points?type=getDetail'
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
                title: '新增类别',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['40%', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    if(flag){
                        location.reload();
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });
        //新增明细
        $('#addDetail').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            var typeid = $("input[name='typeid']").val();
            top.layer.open({
                id: 'addDetails',
                type: 2,
                title: '新增明细',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['40%', '100%'],
                closeBtn: 1,
                content: url+'?typeid='+typeid+'&type=addDetail',
                end: function () {
                    if(flag){
                        table.reload('points', {
                            url: admin_name+'/PatrolSetting/points?type=getDetail'
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
        $("body").on("click", ".getDetail", function() {
            //重载表格
            var params = {};
            params.ppid = $(this).attr('data-ppid');
            table.reload('points', {
                url: admin_name+'/PatrolSetting/points?type=getDetail'
                ,where: params
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });

            //更改备注
            $.ajax({
                timeout: 5000,
                type: "get",
                url: admin_name+'/PatrolSetting/points',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        $(".pointsRemark").html(data.remark);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            //更改显示标题
            $(".detailName").html($(this).html());
            //更改父id
            $("input[name='typeid']").val(params.ppid);
            return false;
        });

        //控制子侧边菜单文字太长时省略号变成提示
        var item = $("#LAY-patrol-patrolsetting-points .layui-nav-item");
        $.each(item,function(j,val){
            if ($(val).children().html().length>18){
                var tips = $(val).children().html();
                $(val).attr("lay-tips",tips)
            }
        });

        //监听伸缩事件，控制子侧边菜单的绝对定位
        admin.on('side(leftChildmenu)', function(obj){
            if (obj.status == null){
                $("#LAY-patrol-patrolsetting-points .layui-side-child").css("left",80);
            }else {
                $("#LAY-patrol-patrolsetting-points .layui-side-child").css("left",235);
            }
        });

        $(".getDetail").each(function(k,v){
            var titleLength = $(v).html().length;
            if (titleLength > 12){
                $(v).attr('title',$(v).html());
            }
        });
    });
    exports('patrol/patrolsetting/points', {});
});
