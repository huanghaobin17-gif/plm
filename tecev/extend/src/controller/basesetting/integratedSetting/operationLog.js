layui.define(function(exports){
    layui.use(['form', 'laydate', 'table', 'tablePlug'], function () {
        var form = layui.form, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();

        //操作时间元素渲染
        laydate.render(dateConfig('#operationListAction_timeStart'));

        laydate.render(dateConfig('#operationListAction_timeEnd'));

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
            elem: '#operationList'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '日志列表'
            ,url: operationLog //数据接口
            ,where: {
                sort: 'logid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'logid' //排序字段，对应 cols 设定的各字段名
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
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    field: 'logid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'username',
                    title: '用户名',
                    width: 130,
                    fixed: 'left',
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/username') == 'false' ? true : false
                }
                , {
                    field: 'modulename',
                    title: '模块名称',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/modulename') == 'false' ? true : false
                }
                , {
                    field: 'actionname',
                    title: '事件',
                    width: 160,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/actionname') == 'false' ? true : false
                }
                , {
                    field: 'remark',
                    title: '行为',
                    minWidth: 500,
                    align: 'left',
                    hide: get_now_cookie(userid + cookie_url + '/remark') == 'false' ? true : false
                }
                , {
                    field: 'ip',
                    title: 'IP地址',
                    width: 160,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/ip') == 'false' ? true : false
                }
                , {
                    field: 'action_time',
                    title: '事件操作时间',
                    width: 160,
                    fixed: 'right',
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/action_time') == 'false' ? true : false
                }
            ]]
        });

        //列排序
        table.on('sort(operationList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('operationList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(operationLogSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('operationList', {
                url: operationLog
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //切换医院
        form.on('select(hospital_id)', function(data){
            var params = {};
            params.job_hospitalid = data.value;
            var html = '<option value=""></option>';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Public/getAllUserSearch',
                data: params,
                dataType: "json",
                async: false,
                success: function (data) {
                    if(data.value.length > 0){
                        $.each(data.value, function(k,v){
                            html += '<option value="' + v.username + '" >' + v.username + '</option>';
                        });
                    }
                }
            });
            $("select[name='operationListUsername']").html(html);
            form.render('select');
        });

        //模块与事件select框联动
        form.on('select(module)', function(data){
            var params = {};
            params.action = 'getActionName';
            params.module = data.value;
            var html = '<option value="">请先选择模块名称</option>';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: operationLog,
                data: params,
                dataType: "json",
                async: false,
                success: function (data) {
                    if(data.length > 0){
                        html = '<option value="">请选择事件</option>';
                        $.each(data, function(k,v){
                            html += '<option value="' + v.name + '" >' + v.title + '</option>';
                        });
                    }
                }
            });
            $("select[name='operationListAction']").html(html);
            form.render('select');
        });

        //事件框重置
        $(".reset").click(function(){
            var html = '<option value="">请先选择模块名称</option>';
            $("select[name='operationListAction']").html(html);
            form.render();
        })
    });
    exports('basesetting/integratedSetting/operationLog', {});
});



