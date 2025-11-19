layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'suggest', 'tablePlug','formSelects'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table, suggest = layui.suggest,formSelects = layui.formSelects, tablePlug = layui.tablePlug;
        var orderField = '',orderType='';
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());
        //先更新页面部分需要提前渲染的控件
        form.render();
        //渲染所有多选下拉
        formSelects.render('department', selectParams(1));
        formSelects.btns('department', selectParams(2), selectParams(3));

        //初始化时间
        lay('.formatDate').each(function(){
            laydate.render(dateConfig(this));
        });

        //定义一个全局空对象
        var gloabOptions = {};
        var exportData = {};
        var repairHisRepID = '';//导出设备assID
        window.localStorage.setItem('repairHisRepID', repairHisRepID);
        //数据表格
        table.render({
            elem: '#RepairSearchList'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            ,title: '维修记录查询'
            ,url: admin_name+'/RepairSearch/getRepairSearchList.html' //数据接口
            ,where: {
                sort: 'repid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'repid' //排序字段，对应 cols 设定的各字段名
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
            },
            toolbar: '#LAY-Repair-RepairSearch-getRepairSearchListToolbar',
            defaultToolbar: ['filter']
            ,cols: [[ //表头
                {type: 'checkbox', fixed: 'left', style: 'background-color: #f9f9f9;'},
                {
                    field: 'repid',
                    style: 'background-color: #f9f9f9;',
                    title: '序号',
                    width: 80,
                    fixed: 'left',
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }, {
                    field: 'repnum',
                    hide: get_now_cookie(userid + cookie_url + '/repnum') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '维修单编号',
                    width: 180,
                    align: 'center'
                },
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 160, align: 'center'},
                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 160, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 150, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 160, align: 'center'},
                {field: 'applicant',hide: get_now_cookie(userid+cookie_url+'/applicant')=='false'?true:false, title: '报修人', width: 100, align: 'center'},
                {field: 'engineer',hide: get_now_cookie(userid+cookie_url+'/engineer')=='false'?true:false, title: '维修工程师', width: 100, align: 'center'},
                {field: 'part_num',hide: get_now_cookie(userid+cookie_url+'/part_num')=='false'?true:false, title: '配件数', width: 100, align: 'center'},
                {field: 'applicant_time',hide: get_now_cookie(userid+cookie_url+'/applicant_time')=='false'?true:false, title: '报修时间', width: 160, sort: true, align: 'center'},
                {field: 'breakdown',hide: get_now_cookie(userid+cookie_url+'/breakdown')=='false'?true:false, title: '故障描述', width: 200, align: 'center'},
                {field: 'fault_problem',hide: get_now_cookie(userid+cookie_url+'/fault_problem')=='false'?true:false, title: '故障问题', width: 200, align: 'center'},
                {field: 'repair_remark',hide: get_now_cookie(userid+cookie_url+'/repair_remark')=='false'?true:false, title: '检修备注', width: 200, align: 'center'},
                {field: 'dispose_detail',hide: get_now_cookie(userid+cookie_url+'/dispose_detail')=='false'?true:false, title: '处理详情', width: 260, align: 'center'},
                {field: 'repairTypeName',hide: get_now_cookie(userid+cookie_url+'/repairTypeName')=='false'?true:false, title: '维修性质', width: 100, align: 'center'},
                {field: 'actual_price',hide: get_now_cookie(userid+cookie_url+'/actual_price')=='false'?true:false, title: '总维修费用', width: 100, align: 'center'},
                {field: 'working_hours',hide: get_now_cookie(userid+cookie_url+'/working_hours')=='false'?true:false, title: '维修工时', width: 100, align: 'center'},
                {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 260,
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                }
            ]], done: function (res, curr) {
                exportData = res.rows;
                var pages = this.page.pages;
                var thisId = '#' + this.id;
                if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else {
                    table.resize(this.id); //重置表格尺寸
                }

                var storage=window.localStorage;
                var key = location.pathname+location.hash+this.id;
                if (!storage[key]) {
                    var json = JSON.stringify(this.cols);
                    storage.setItem(key,json);
                }

                //勾选已经选择了的设备
                var tmpArr = [];
                if (storage['repairHisRepID']) {
                    tmpArr = storage['repairHisRepID'].split(",");
                }
                //已勾选的ID
                var tr = $('.layui-table-fixed-l').find('tr');
                $.each(tr,function(){
                    $.each($(this).find('td'),function (k,v) {
                        if($(v).attr('data-field') == 'repid'){
                            var frepid = $(v).attr('data-content');
                            var index = $.inArray(frepid, tmpArr);
                            if (index >= 0) {
                                $(v).prev().find('.layui-unselect').addClass('layui-form-checked')
                            }
                        }
                    });
                });
            }
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
        //列排序
        table.on('sort(RepairSearchList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            orderField = obj.field;
            orderType = obj.type;
            table.reload('RepairSearchList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(RepairSearchListSearch)', function(data){
            var startDate = data.field['startDate'];
            var endDate = data.field['endDate'];
            if (startDate && endDate) {
                if (endDate < startDate) {
                    layer.msg('报修时间设置不合理', {icon: 2});
                    return false;
                }
            }
            var engineerStartDate = data.field['engineerStartDate'];
            var engineerEndDate = data.field['engineerEndDate'];
            if (engineerStartDate && engineerEndDate) {
                if (engineerEndDate < engineerStartDate) {
                    layer.msg('结束时间不能小于开始时间', {icon: 2});
                    return false;
                }
            }
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('RepairSearchList', {
                url: admin_name+'/RepairSearch/getRepairSearchList.html'
                ,where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }, done: function (res, curr) {
                    var pages = this.page.pages;
                    var thisId = '#' + this.id;
                    if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else {
                        table.resize(this.id); //重置表格尺寸
                    }
                }
            });
            return false;
        });

        //操作栏按钮
        table.on('tool(RepairSearchList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'showAssets':
                    //显示主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】设备详情',
                        area: ['1050px', '100%'],
                        anim:2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?assid='+rows.assid]
                    });
                    break;
                case 'showRepair':
                    //显示维修单
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】维修单'+rows.repnum+'详情',
                        area: ['920px', '100%'],
                        anim:2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?repid='+rows.repid+'&assid='+rows.assid],
                        end: function () {
                            if(flag){
                                table.reload('RepairSearchList', {
                                    url: admin_name+'/RepairSearch/getRepairSearchList.html'
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
                case 'showUpload':
                    //显示上传文件
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】相关上传文件查看',
                        area: ['900px', '100%'],
                        anim:2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?repid='+rows.repid]
                    });
                    break;
                case 'cancelRepair':
                    //显示维修单
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】维修单'+rows.repnum+'撤单申请',
                        area: ['920px', '100%'],
                        anim:2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?repid='+rows.repid+'&assid='+rows.assid],
                        end: function () {
                            if(flag){
                                table.reload('RepairSearchList', {
                                    url: admin_name+'/RepairSearch/getRepairSearchList.html'
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

        //勾选复选框动作
        table.on('checkbox(RepairSearchList)', function (obj) {
            // if(obj.data.status == -1){
            //     layer.msg('请勿勾选已撤单的记录', {icon: 2, time: 1000});
            //     return false;
            // }
            var storage = window.localStorage;
            var key = 'repairHisRepID';
            var tmpArr = [];
            if (storage[key]) {
                tmpArr = storage[key].split(",");
            }
            if (obj.type == 'all') {
                var checkStatus = table.checkStatus('RepairSearchList');
                var data = checkStatus.data;
                if(data.length == 0){
                    var tr = $('.layui-table-main').find('tr');
                    $.each(tr,function(){
                        $.each($(this).find('td'),function () {
                            if($(this).attr('data-field') == 'repid'){
                                tmpArr.remove($(this).attr('data-content'));
                            }
                        });
                    });
                }else{
                    $.each(data, function (i, n) {
                        var index = $.inArray(n.repid, tmpArr);
                        if (index < 0) {
                            //不存在
                            tmpArr.push(n.repid);
                        }
                    });
                }
            } else {
                var index = $.inArray(obj.data.repid, tmpArr);
                if (index >= 0) {
                    //存在
                    tmpArr.remove(obj.data.repid);
                } else {
                    //不存在
                    tmpArr.push(obj.data.repid);
                }
            }
            storage[key] = tmpArr.toString();
        });

        table.on('toolbar(RepairSearchList)', function(obj){
            var event =  obj.event,url = $(this).attr('data-url');
            switch(event){
                case 'exportHistory'://添加主设备
                    var storage = window.localStorage;
                    if (!storage['repairHisRepID']) {
                        layer.msg('请选择要导出的设备！', {icon: 2, time: 1000});
                        return false;
                    }
                    var params = {};
                    var fields = '';
                    var field_arr = new Array();
                    $.each($('th'), function (index, e) {
                        if (e.className.indexOf('layui-hide') == '-1' && e.getAttribute("data-field") != 'operation' && e.getAttribute("data-field") != 'assid' && $.inArray(e.getAttribute("data-field"), field_arr) == -1) {
                            fields += e.getAttribute("data-field") + ',';
                            field_arr.push(e.getAttribute("data-field"));
                        }
                    });

                    params.repid = storage['repairHisRepID'];
                    params.fields = fields;
                    params.type = 'export';
                    params.orderField = orderField;
                    params.orderType = orderType;

                    postDownLoadFile({
                        url:admin_name+'/RepairSearch/getRepairSearchList.html',
                        data:params,
                        method:'POST'
                    });
                    break;
            }
        });


        //设备名称搜索建议
        $("#getRepairSearchListAssets").bsSuggest(
            returnAssets()
        );

        //设备编号搜索建议
        $("#getRepairSearchListAssetsNum").bsSuggest(
            returnAssnum()
        );

    });
    exports('repair/search/getRepairSearchList', {});
});




































