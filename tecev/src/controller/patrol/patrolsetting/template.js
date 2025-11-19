layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();
        suggest.search();
        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#template'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '模板维护列表'
            ,url: template //数据接口
            ,where: {
                sort: 'tpid'
                ,order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'tpid' //排序字段，对应 cols 设定的各字段名
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
            },
            toolbar: '#LAY-patrol-patrolsetting-templateToolbar',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    field: 'tpid',
                    title: '序号',
                    width: 60,
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'name', title: '模板名称', width: 250, align: 'center'}
                , {field: 'remark', title: '备注', align: 'center'}
                , {width: 300, field: 'operation', title: '操作', fixed: 'right', align: 'center'}
            ]]
        });

        //操作栏按钮
        table.on('tool(template)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'showTemplate'://模板预览
                    top.layer.open({
                        type: 2,
                        title:'【'+rows.name+'】模板预览',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?id='+rows.tpid+'&type=showTemplate']
                    });
                    break;
                case 'copyAddTemplate'://拷贝为新模板
                    top.layer.open({
                        id: 'copyAddTemplates',
                        type: 2,
                        title: '新增模板【模板源：'+rows.name+'】',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?id='+rows.tpid+'&type=copyAddTemplate'],
                        end: function () {
                            if(flag){
                                table.reload('template', {
                                    url: template
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
                case 'editTemplate'://修改模板
                    top.layer.open({
                        id: 'editTemplates',
                        type: 2,
                        title: '修改模板信息【' + rows.name + '】',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?id='+rows.tpid],
                        end: function () {
                            if(flag){
                                table.reload('template', {
                                    url: template
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
                case 'deleteTemplate'://删除模板
                    layer.confirm('该模板与设备的绑定关系也将一并删除，确定删除吗？', {icon: 3, title:'删除模板【'+rows.name+'】'}, function(index){
                        $.ajax({
                            timeout: 5000,
                            type: "GET",
                            url: url+'?id='+rows.tpid,
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    if (data.status == 1) {
                                        layer.msg(data.msg,{icon : 1,time:2000},function(){
                                            table.reload('template', {
                                                url: template
                                                ,where: gloabOptions
                                                ,page: {
                                                    curr: 1 //重新从第 1 页开始
                                                }
                                            });
                                        });
                                    }else{
                                        layer.msg(data.msg,{icon : 2},1000);
                                    }
                                } else {
                                    layer.msg("数据异常！");
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
        table.on('sort(template)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('template', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(templateSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('template', {
                url: template
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('toolbar(template)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addTemplate'://新增模板
                    top.layer.open({
                        id: 'addTemplate_1',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            if(flag){
                                table.reload('template', {
                                    url: template
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

        /*
         /选择模板搜索建议
         */
        $("#templateName").bsSuggest({
            url: admin_name+'/Public/getAllTemplate',
            /*effectiveFields: ["userName", "shortAccount"],
             searchFields: [ "shortAccount"],*/
            effectiveFieldsAlias:{tpid:"序号",name:"模板名称"},
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "tpid",
            keyField: "name",
            listStyle: {
                "max-height": "330px", "max-width": "480px",
                "overflow": "auto", "width": "400px", "text-align": "center"
            },
            clearable: false
        });

    });
    exports('patrol/patrolsetting/template', {});
});






