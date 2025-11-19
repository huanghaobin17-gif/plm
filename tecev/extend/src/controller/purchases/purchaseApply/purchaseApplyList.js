layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //渲染多选下拉
        formSelects.render();
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //录入时间元素渲染
        laydate.render({
            elem: '#apply_date_start' //指定元素
        });
        laydate.render({
            elem: '#apply_date_end' //指定元素
        });

//定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#purchaseApplyList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: purchaseApplyList //数据接口
            , where: {
                sort: 'apply_id',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'apply_id' //排序字段，对应 cols 设定的各字段名
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
            },
            toolbar: '#LAY-Purchases-PurchaseApply-purchaseApplyListToolbar',
            defaultToolbar: ['filter','exports']
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            //,page: true //开启分页

            , cols: [[ //表头
                {
                    field: 'apply_id',
                    title: '序号',
                    width: 80,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '申请科室', width: 180, align: 'center'},
                {field: 'apply_date',hide: get_now_cookie(userid+cookie_url+'/apply_date')=='false'?true:false, title: '申请日期', width: 120, align: 'center'},
                {field: 'project_name',hide: get_now_cookie(userid+cookie_url+'/project_name')=='false'?true:false, title: '项目名称', width: 300, align: 'center'},
                {field: 'apply_type_name',hide: get_now_cookie(userid+cookie_url+'/apply_type_name')=='false'?true:false, title: '申请方式', width: 120, align: 'center'},//正常申请（计划内）、特殊申请（计划外）
                {field: 'assets_nums',hide: get_now_cookie(userid+cookie_url+'/assets_nums')=='false'?true:false, title: '设备总数', width: 120, align: 'center'},
                {field: 'assets_amount',hide: get_now_cookie(userid+cookie_url+'/assets_amount')=='false'?true:false, title: '预计总金额(元)', width: 150, align: 'center'},
                {
                    field: 'approve_status_name',
                    hide: get_now_cookie(userid + cookie_url + '/approve_status_name') == 'false' ? true : false,
                    title: '审核状态',
                    width: 120,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'center'
                },
                {
                    field: 'depart_apply_operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    minWidth: 120,
                    fixed: 'right',
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
        //监听排序
        table.on('sort(purchaseApplyData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('userLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //监听搜索
        form.on('submit(departApplySearch)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('purchaseApplyList', {
                url: purchaseApplyList
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //监听工具条
        table.on('tool(purchaseApplyData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'showApply':
                    top.layer.open({
                        type: 2,
                        title: '查看科室申请【' + rows.department + '】',
                        area: ['800px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'approve':
                    var flag = 1;
                    top.layer.open({
                        id: 'approve_apply',
                        type: 2,
                        title: '审核科室申请【' + rows.department + '】',
                        area: ['800px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url + '?id=' + rows.apply_id],
                        end: function () {
                            if (flag) {
                                table.reload('purchaseApplyList', {
                                    url: purchaseApplyList
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

        table.on('toolbar(purchaseApplyData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addPurchaseApply'://添加主设备
                    top.layer.open({
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['980px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('purchaseApplyList', {
                                    url: purchaseApplyList
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

        //申请审批
        $('#purchaseApplyApprove').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                type: 2,
                title: $(this).html(),
                scrollbar:false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['50%', '100%'],
                closeBtn: 1,
                content: [url],
                end: function () {
                    if(flag){
                        table.reload('assetsLists', {
                            url: purchaseApplyList
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

        //项目名称搜索建议
        $("#getProjects").bsSuggest(
            returnDepartProjects(hospital_id)
        );

    });
    exports('purchases/purchaseApply/purchaseApplyList', {});
});