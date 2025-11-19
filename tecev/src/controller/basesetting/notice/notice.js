layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#noticeLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '公告栏列表'
            ,url: getNoticeList //数据接口
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
            },
            toolbar: '#LAY-BaseSetting-Notice-getNoticeListToolbar',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {field: 'notid',title: '序号',width: 65,align: 'center',type:'space',templet: function(d){return d.LAY_INDEX;}},
                {
                    field: 'hospital_name',
                    title: '所属医院',
                    width: 200,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/hospital_name') == 'false' ? true : false
                },
                {
                    field: 'title',
                    title: '公告标题',
                    minWidth: 350,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/title') == 'false' ? true : false
                },
                {
                    field: 'top_name',
                    title: '是否置顶',
                    width: 150,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/top_name') == 'false' ? true : false
                },
                {
                    field: 'adduser',
                    title: '发布者',
                    width: 150,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/adduser') == 'false' ? true : false
                },
                {
                    field: 'adddate',
                    title: '发布时间',
                    width: 180,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/adddate') == 'false' ? true : false
                },
                {field: 'operation', title: '操作', width: 140, align: 'center', fixed: 'right'}
            ]]
        });

        //监听操作
        table.on('tool(noticeLists)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var flag = 1;
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'showNotice'://显示公告
                    top.layer.open({
                        type: 2,
                        title: '公告详情【'+rows.title+'】',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url+'?notid='+rows.notid+'&type=showNotice'],
                        end: function () {
                            if(flag){
                                table.reload('noticeLists', {
                                    url: getNoticeList
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
                case 'editNotice'://编辑公告
                    top.layer.open({
                        id: 'editNotices',
                        type: 2,
                        title: '编辑公告【'+rows.title+'】',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url+'?notid='+rows.notid],
                        end: function () {
                            if(flag){
                                table.reload('noticeLists', {
                                    url: getNoticeList
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
                case 'deleteNotice'://删除公告
                    layer.confirm('公告删除后无法恢复，确定删除吗？', {icon: 3, title:$(this).html()+'【'+rows.title+'】'}, function(index){
                        var params = {};
                        params['notid']  = rows.notid;
                        $.ajax({
                            type:"POST",
                            url:url,
                            data:params,
                            beforeSend:function(){
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success:function(data){
                                layer.closeAll('loading');
                                if(data.status == 1){
                                    layer.msg(data.msg,{icon : 1,time:2000},function(){
                                        table.reload('noticeLists', {
                                            url: getNoticeList
                                            ,where: gloabOptions
                                            ,page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                }else{
                                    layer.msg(data.msg,{icon : 2});
                                }
                            },
                            //调用出错执行的函数
                            error: function(){
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 5});
                            }
                        });
                        layer.close(index);
                    });
                    break;
            }
        });
        table.on('toolbar(noticeLists)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addnotice'://发布公告
                    top.layer.open({
                        id: 'addnotices',
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('noticeLists', {
                                    url: getNoticeList
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
    });
    exports('basesetting/notice/notice', {});
});
