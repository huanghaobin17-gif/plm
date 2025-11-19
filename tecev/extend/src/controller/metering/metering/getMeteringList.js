layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'laydate', 'formSelects', 'tablePlug','suggest'], function () {
        var layer = layui.layer
            , form = layui.form
            , table = layui.table
            , laydate = layui.laydate
            , formSelects = layui.formSelects
            , suggest = layui.suggest
            , tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();
        //渲染所有多选下拉
        formSelects.render('mer_department', selectParams(1));
        formSelects.btns('mer_department', selectParams(2), selectParams(3));
        suggest.search();
        layer.config(layerParmas());
        laydate.render({
            elem: '#getMeteringListStartDate',
            festival: true,
            min: '1'
        });
        laydate.render({
            elem: '#getMeteringListEndDate',
            festival: true,
            min: '1'
        });

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#getMeteringList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '计量计划制定列表'
            , url: getMeteringList //数据接口
            , where: {
                is_metering:1,
                type:'getMeteringList',
                sort: 'mpid',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'mpid' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Metering-Metering-getMeteringListToolbar',
            defaultToolbar: ['filter','exports']
            , cols:[ [ //表头
                {type: 'checkbox', fixed: 'left'},
                {field: 'mpid', title: '序号', width: 65, fixed: 'left', align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }},
                {field:'plan_num',hide: get_now_cookie(userid+cookie_url+'/plan_num')=='false'?true:false,fixed:'left',title:'计划编号',width:140, align: 'center'},//同一个质控计划名称的合并行，如何合并问俊生
                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 160, align: 'center'},
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 160, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格 / 型号', width: 140, align: 'center'},
                {field: 'asset_count',hide: get_now_cookie(userid+cookie_url+'/asset_count')=='false'?true:false, title: '设备数量', width: 90, align: 'center'},
                {field: 'unit',hide: get_now_cookie(userid+cookie_url+'/unit')=='false'?true:false, title: '单位', width: 80, align: 'center'},
                {field: 'factory',hide: get_now_cookie(userid+cookie_url+'/factory')=='false'?true:false, title: '生产厂商', width: 280, align: 'center'},
                {field: 'productid',hide: get_now_cookie(userid+cookie_url+'/productid')=='false'?true:false, title: '产品序列号', width: 140, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 160, align: 'center'},
                {field: 'mcategory',hide: get_now_cookie(userid+cookie_url+'/mcategory')=='false'?true:false, title: '计量分类', width: 100, align: 'center'},
                {field: 'cycle',hide: get_now_cookie(userid+cookie_url+'/cycle')=='false'?true:false, title: '计量周期(月)', width: 110, align: 'center'},
                {field: 'test_way',hide: get_now_cookie(userid+cookie_url+'/test_way')=='false'?true:false, title: '检定方式', width: 100, align: 'center'},
                {field: 'next_date',hide: get_now_cookie(userid+cookie_url+'/next_date')=='false'?true:false, title: '下次待检日期', width: 120, align: 'center'},
                {field: 'respo_user',hide: get_now_cookie(userid+cookie_url+'/respo_user')=='false'?true:false, title: '计量负责人', width: 100, align: 'center'},
                {field: 'remark',hide: get_now_cookie(userid+cookie_url+'/remark')=='false'?true:false, title: '备注', width: 240, align: 'center'},
                {field: 'remind_day',hide: get_now_cookie(userid+cookie_url+'/remind_day')=='false'?true:false, fixed: 'right', title: '提前提醒天数', width:120, align: 'center'},
                {field: 'status',hide: get_now_cookie(userid+cookie_url+'/status')=='false'?true:false, fixed: 'right', title: '计划状态', width:90, align: 'center'},
                {field: 'operation', fixed: 'right',title: '操作', minWidth: 140, align: 'center'}
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


        table.on('tool(getMeteringData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'saveMetering':
                    var flag = 1;
                    top.layer.open({
                        id: 'saveMeterings',
                        type: 2,
                        title: '【' + rows.assets + '】编辑',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?mpid=' + rows.mpid],
                        end: function () {
                            console.log(flag);
                            if(flag){
                                table.reload('getMeteringList', {
                                    url: getMeteringList
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
                case 'delMetering':
                    var params={};
                    params.mpid=rows.mpid;
                    params.type='delMetering';
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                    layui.index.render();
                                });
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 2000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2, time: 2000});
                        }
                    });
                    break;
                case 'showMetering':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】详情',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?mpid=' + rows.mpid+'&type=showMetering']
                    });
                    break;
            }
        });

        form.on('submit(getMeteringListSearch)', function (data) {
            gloabOptions = data.field;
            if (gloabOptions.startDate   && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (gloabOptions.day_min   && gloabOptions.day_max) {
                if (gloabOptions.day_max-gloabOptions.day_min<0) {
                    layer.msg('提醒天数范围不合理', {icon: 2});
                    return false;
                }
            }
            gloabOptions.type='getMeteringList';
            var is_metering = '';
            $("input[name='getMeteringList_is_metering']:checked").each(function () {
                is_metering = $(this).val();
            });
            gloabOptions.is_metering=is_metering;
            table.reload('getMeteringList', {
                url: getMeteringList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('toolbar(getMeteringData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addMetering'://新增计量计划
                    top.layer.open({
                        id: 'addMeterings',
                        type: 2,
                        anim: 2, //动画风格
                        title: '新增计量计划',
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('getMeteringList', {
                                    url: getMeteringList
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
                case 'batchSaveMetering'://批量维护
                    var checkStatus = table.checkStatus('getMeteringList');
                    var data = checkStatus.data;
                    if(data.length === 0){
                        layer.msg('请选择要维护的计划！',{icon : 2,time:1000});
                        return false;
                    }
                    var mpid = '';
                    var params = {};
                    params.type = 'batchEdit';
                    for(j = 0,len=data.length; j < len; j++) {
                        mpid += data[j]['mpid']+',';
                    }
                    url += '?mpid='+mpid+'&type=batchEdit';
                    top.layer.open({
                        id: 'batchEdits',
                        type: 2,
                        title: $(this).html(),
                        shade: [0.8, '#393D49'],
                        shadeClose:true,
                        anim:5,
                        scrollbar:false,
                        maxmin: true,
                        area: ['90%', '98%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table.reload('getMeteringList', {
                                url: getMeteringList
                                ,where: gloabOptions
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'batchAddMetering'://批量添加
                    top.layer.open({
                        id: 'batchAddMeterings',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar: false,
                        maxmin: true,
                        area: ['100%', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table.reload('getMeteringList', {
                                url: getMeteringList
                                , where: gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }

                    });
                    break;
                case 'batchDel':
                    var checkStatus = table.checkStatus('getMeteringList');
                    var data = checkStatus.data;
                    if (data.length == 0) {
                        layer.msg('请选择要删除的数据！', {icon: 2, time: 1000});
                        return false;
                    }
                    var mpid = '';
                    var params = {};
                    params.type = 'delBatchMetering';
                    for (j = 0, len = data.length; j < len; j++) {
                        mpid += data[j]['mpid'] + ',';
                    }
                    params.mpid = mpid;
                    params.type='delBatchMetering';
                    layer.confirm('确定删除选中的数据吗？', {icon: 3, title: '批量删除数据'}, function (index) {
                        $.ajax({
                            type: "POST",
                            url: admin_name+'/Metering/delMetering.html',
                            data: params,
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                layer.closeAll('loading');
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                        table.reload('getMeteringList', {
                                            url: getMeteringList
                                            , where: {"type": "getMeteringList"}
                                            , page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                } else {
                                    layer.msg(data.msg, {icon: 2});
                                }
                            },
                            //调用出错执行的函数
                            error: function () {
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 5});
                            }
                        });
                        layer.close(index);
                    });
                    break;
            }
        });


        //选择设备
        $("#getMeteringListAssetsName").bsSuggest(
            returnAssets()
        );

        //下载文件
        $(".downloadConfirmReport").on('click',function(){
            var params = {};
            params.path = '/Public/dwfile/confirmReport.docx';
            params.filename = '强制检定计量器具确认表.docx';
            postDownLoadFile({
                url: admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
            return false;
        });
    });
    exports('metering/metering/getMeteringList', {});
});