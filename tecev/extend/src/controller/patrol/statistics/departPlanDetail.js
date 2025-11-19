layui.define(function(exports){
    var id = $('input[name="departid"]').val();
    var startDate = $('input[name="startDate"]').val();
    var endDate = $('input[name="endDate"]').val();
    layui.use(['table', 'tablePlug'], function () {
        var table = layui.table, tablePlug = layui.tablePlug;
        //第一个实例
        table.render({
            elem: '#assetsLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,size: 'sm'
            ,loading:true
            ,limit: 10
            ,url: admin_name+'/PatrolStatis/patrolPlanSurvey.html' //数据接口
            ,where: {
                sort: 'assid'
                ,order: 'asc'
                ,action:'departPlanDetail'
                ,id:id
                ,startDate:startDate
                ,endDate:endDate
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                //{type:'checkbox',fixed: 'left'}
                {field: 'assid', title: '序号', width:80, fixed: 'left',align:'center',type:'space',templet: function(d){
                    return d.LAY_INDEX;
                }}
                ,{field: 'assets',fixed: 'left', title: '设备名称', width:130,align:'center'}
                ,{field: 'assnum',fixed: 'left', title: '设备编码', width: 140,align:'center'}
                ,{field: 'assorignum',fixed: 'left', title: '设备原编码', width: 130,align:'center'}
                ,{field: 'model', title: '设备型号', width: 120,align:'center'}
                ,{field: 'category', title: '所属分类', width: 170, align:'center'}
                ,{field: 'pmNum', title: '预防性维护次数', width: 130,align:'center'}
                ,{field: 'xcNum', title: '巡查保养次数', width: 118,align:'left'}
                ,{field: 'rcNum', title: '日常保养次数', width: 118, align:'center'}
                ,{field: 'abNum', title: '异常次数', width: 100, align:'center',templet: function(d){
                    if(d.abNum > 0){
                        return '<span style="color: red;">'+d.abNum+'</span>';
                    }else{
                        return '<span>'+d.abNum+'</span>';
                    }
                }}
                ,{field: 'abTermNum', title: '异常项总数', width: 100, align:'center',templet: function(d){
                    if(d.abTermNum > 0){
                        return '<span style="color: red;">'+d.abTermNum+'</span>';
                    }else{
                        return '<span>'+d.abTermNum+'</span>';
                    }
                }}
                ,{field: 'a', title: '操作',fixed: 'right', width: 70, align:'center',templet: function (d) {
                    if (d.cid == ''){
                        return '<button class="layui-btn layui-btn-disabled layui-btn-xs">查看</button>';
                    }else {
                        return '<button lay-event="view" class="layui-btn layui-btn-normal layui-btn-xs">查看</button>';
                    }


                }}
            ]]
        });
        //监听工具条
        table.on('tool(assetsData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = admin_name+'/PatrolStatis/patrolPlanSurvey.html';
            if(layEvent === 'view'){ //编辑
                if(!rows.cid){
                    layer.msg('该设备没有巡查计划记录！',{icon : 2});
                }else{
                    top.layer.open({
                        type: 2,
                        title: '计划记录【'+rows.assets+'】',
                        area: ['92%', '100%'],
                        shade: 0,
                        anim:2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?action=view&assnum='+rows.assnum+'&cid='+rows.cid+'&startDate='+startDate+'&endDate='+endDate]
                    });
                }
            } else if(layEvent === 'unbundling'){ //解绑
                layer.confirm('解除绑定后会导致该用户无法收取微信消息，确定解绑？', {icon: 3, title:$(this).html()}, function(index){
                    var params = {};
                    params['userid']  = rows.userid;
                    $.ajax({
                        type:"POST",
                        url:url,
                        data:params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend:function(){
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success:function(data){
                            if(data.status == 1){
                                layer.msg(data.msg,{icon : 1,time:2000},function(){
                                    parent.layer.closeAll(); //再执行关闭
                                    parent.location.reload(); // 父页面刷新
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2});
                            }
                        },
                        //调用出错执行的函数
                        error: function(){
                            //请求出错处理
                            layer.msg('服务器繁忙', {icon: 2});
                        },
                        complete:function(){
                            layer.closeAll('loading');
                        }
                    });
                    layer.close(index);
                });
            } else if(layEvent === 'delete'){ //删除
                //do something
                layer.confirm('用户删除后无法恢复，确定删除吗？', {icon: 3, title:$(this).html()+'【'+rows.username+'】'}, function(index){
                    var params = {};
                    params['userid']  = rows.userid;
                    $.ajax({
                        type:"POST",
                        url:url,
                        data:params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend:function(){
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success:function(data){
                            layer.closeAll('loading');
                            if(data.status == 1){
                                layer.msg(data.msg,{icon : 1,time:2000},function(){
                                    parent.layer.closeAll(); //再执行关闭
                                    parent.location.reload(); // 父页面刷新
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2});
                            }
                        },
                        //调用出错执行的函数
                        error: function(){
                            //请求出错处理
                            layer.msg('服务器繁忙', {icon: 2});
                        }
                    });
                    layer.close(index);
                });
            }
        });

        //监听排序
        table.on('sort(userData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('userLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });
        //监听表格复选框选择
        // table.on('checkbox(userData)', function(obj){
        //     console.log(obj)
        // });

        var $ = layui.$, active = {
            getCheckData: function(){ //获取选中数据
                var checkStatus = table.checkStatus('userLists')
                    ,data = checkStatus.data;
                layer.alert(JSON.stringify(data));
            }
            ,getCheckLength: function(){ //获取选中数目
                var checkStatus = table.checkStatus('userLists')
                    ,data = checkStatus.data;
                layer.msg('选中了：'+ data.length + ' 个');
            }
            ,isAll: function(){ //验证是否全选
                var checkStatus = table.checkStatus('userLists');
                layer.msg(checkStatus.isAll ? '全选': '未全选')
            }
        };
        $('.demoTable .layui-btn').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });
    });
    exports('controller/patrol/statistics/departPlanDetail', {});
});