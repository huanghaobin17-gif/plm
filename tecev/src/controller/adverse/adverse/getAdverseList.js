layui.define(function(exports){
    layui.use(['layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, formSelects = layui.formSelects, form = layui.form, suggest = layui.suggest, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;

    //初始化搜索建议插件
    suggest.search();
    //多选框
    formSelects.render('department', selectParams(1));
    formSelects.btns('department', selectParams(2));
    layer.config(layerParmas());

    //先更新页面部分需要提前渲染的控件
    form.render();

    //报告日期元素渲染
    laydate.render(dateConfig('#getAdverseListStartDate'));
    laydate.render(dateConfig('#getAdverseListEndDate'));
    

    //定义一个全局空对象
    var gloabOptions = {};
    table.render({
        elem: '#getAdverseLists'
        //,height: '600'
        , limits: [5, 10, 20, 50, 100, 200, 500, 1000, 2000]
        , loading: true
        , limit: 10
        ,title: '器械不良报告列表'
        , url: getAdverseList //数据接口
        , where: {
            sort: 'id'
            , order: 'desc'
        } //如果无需传递额外参数，可不加该参数
        , initSort: {
            field: 'id' //排序字段，对应 cols 设定的各字段名
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
        },
        toolbar: '#LAY-Adverse-Adverse-getAdverseListToolbar',
        defaultToolbar: ['filter','exports']
        , cols: [[ //表头
            {
                field: 'id',
                title: '序号',
                width: 65,
                fixed: 'left',
                align: 'center',
                style: 'background-color: #f9f9f9;',
                type: 'space',
                templet: function (d) {
                    return d.LAY_INDEX;
                }
            }
            , {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 160, align: 'center'}
            , {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 160, align: 'center'}
            , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '设备型号', width: 150, align: 'center'}
            , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '科室名称', width: 150, align: 'center'}
            , {field: 'status',hide: get_now_cookie(userid+cookie_url+'/status')=='false'?true:false, title: '状态', width: 100, align: 'center'}
            , {field: 'sign',hide: get_now_cookie(userid+cookie_url+'/sign')=='false'?true:false, title: '上报人员', width: 100, align: 'center'}
            , {field: 'reporter',hide: get_now_cookie(userid+cookie_url+'/reporter')=='false'?true:false, title: '上报人员职称', width: 120, align: 'center'}
            , {field: 'report_from',hide: get_now_cookie(userid+cookie_url+'/report_from')=='false'?true:false, title: '报告来源', width: 200, align: 'center'}
            , {field: 'report_date',hide: get_now_cookie(userid+cookie_url+'/report_date')=='false'?true:false, title: '报告日期', width: 150, align: 'center'}
            , {field: 'express_date',hide: get_now_cookie(userid+cookie_url+'/express_date')=='false'?true:false, title: '事件发生日期', width: 120, align: 'center'}
            , {field: 'express',hide: get_now_cookie(userid+cookie_url+'/express')=='false'?true:false, title: '事件主要表现', width: 300, align: 'center'}
            , {field: 'cause',hide: get_now_cookie(userid+cookie_url+'/cause')=='false'?true:false, title: '事件发生初步原因分析', width: 300, align: 'center'}
            , {field: 'situation',hide: get_now_cookie(userid+cookie_url+'/situation')=='false'?true:false, title: '事件初步处理情况', width: 300, align: 'center'}
            , {
                field: 'consequence',
                hide: get_now_cookie(userid+cookie_url+'/consequence')=='false'?true:false,
                title: '事件后果',
                width: 300,
                align: 'center',
                templet: function (d) {
                    return (d.consequence == -1) ? '' : d.consequence;
                }
            }
            , {field: 'report_status',hide: get_now_cookie(userid+cookie_url+'/report_status')=='false'?true:false, title: '事件报告状态', width: 450, align: 'center'}
            , {
                field: 'getAdverseListsOperation',
                title: '操作',
                fixed: 'right',
                minWidth: 120,
                style: 'background-color: #f9f9f9;',
                align: 'center'
            }
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
    table.on('tool(getAdverseLists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        var rows = obj.data; //获得当前行数据
        var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
        //var tr = obj.tr; //获得当前行 tr 的DOM对象
        var url = $(this).attr('data-url');
        var flag = 1;
        switch (layEvent) {
            case 'showAdverse'://查看不良事件
                top.layer.open({
                    type: 2,
                    title: '查看【' + rows.assets + '】不良事件',
                    area: ['1000px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url + '?id=' + rows.id + '&type=showAdverse']
                });
                break;
            case 'editAdverse'://编辑不良事件
                top.layer.open({
                    id: 'editAdverses',
                    type: 2,
                    title: '编辑【' + rows.assets + '】不良事件',
                    area: ['1000px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url + '?id=' + rows.id],
                    end: function () {
                        if (flag) {
                            table.reload('getAdverseLists', {
                                url: getAdverseList
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

    //列排序
    table.on('sort(getAdverseLists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        //尽管我们的 table 自带排序功能，但并没有请求服务端。
        //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
        table.reload('getAdverseLists', {
            initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
            , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                sort: obj.field //排序字段
                , order: obj.type //排序方式
            }
        });
    });

    //搜索按钮
    form.on('submit(getAdverseListsSearch)', function (data) {
        gloabOptions = data.field;
        if (gloabOptions.getAdverseListReport_from == undefined) {
            gloabOptions.getAdverseListReport_from = '';
        }
        var table = layui.table;
        gloabOptions.department = formSelects.value('department', 'valStr');
        table.reload('getAdverseLists', {
            url: getAdverseList
            , where: gloabOptions
            , page: {
                curr: 1 //重新从第 1 页开始
            }
        });
        return false;
    });
    table.on('toolbar(getAdverseLists)', function(obj){
        var event =  obj.event,
            url = $(this).attr('data-url'),
            flag = 1;
        switch(event){
            case 'addAdverse'://添加不良事件
                top.layer.open({
                    id: 'addAdverses',
                    type: 2,
                    title: $(this).html(),
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    area: ['1000px', '100%'],
                    closeBtn: 1,
                    content: [url],
                    end: function () {
                        if (flag) {
                            table.reload('getAdverseLists', {
                                url: getAdverseList
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
    //重置按钮
    $("#reset").click(function () {
        $("input[name='getAdverseListsDepartid']").val('');
    });

    //设备名称搜索建议
    $("#getAdverseListsAssets").bsSuggest(
        returnAssets()
    );

    //科室搜索建议
    $("#getAdverseListsDepartment").bsSuggest(
        returnDepartment()
    );


});
    exports('adverse/adverse/getAdverseList', {});
});
