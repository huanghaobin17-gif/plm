
layui.define(function(exports){
    layui.use(['layer', 'form', 'suggest', 'laydate', 'table', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, suggest = layui.suggest, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();


        //日期元素渲染
        lay('.formatDate').each(function(){
            laydate.render({
                elem: this
                ,trigger: 'click'
            });
        });

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#checkLists'
            //,height: '600'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            ,title: '转科验收列表'
            , height: 'full-100' //高度最大化减去差值
            ,url: checkLists //数据接口
            ,where: {
                sort: 'assid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Assets-Transfer-checkListsToolbar',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    type: 'checkbox',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left'
                },
                {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }, {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    title: '设备编号',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值', width: 100, align: 'center'}
                , {field: 'transfernum',hide: get_now_cookie(userid+cookie_url+'/transfernum')=='false'?true:false, title: '转科单号', width: 160, align: 'center'}
                , {field: 'tranout_depart_name',hide: get_now_cookie(userid+cookie_url+'/tranout_depart_name')=='false'?true:false, title: '转出科室', width: 120, align: 'center'}
                , {field: 'tranin_depart_name',hide: get_now_cookie(userid+cookie_url+'/tranin_depart_name')=='false'?true:false, title: '转入科室', width: 120, align: 'center'}
                , {field: 'applicant_user',hide: get_now_cookie(userid+cookie_url+'/applicant_user')=='false'?true:false, title: '申请人', width: 100, align: 'center'}
                , {field: 'applicant_time',hide: get_now_cookie(userid+cookie_url+'/applicant_time')=='false'?true:false, title: '申请日期', width: 110, sort: true, align: 'center'}
                , {field: 'transfer_date',hide: get_now_cookie(userid+cookie_url+'/transfer_date')=='false'?true:false, title: '转科日期', width: 110, sort: true, align: 'center'}
                , {field: 'tran_reason',hide: get_now_cookie(userid+cookie_url+'/tran_reason')=='false'?true:false, title: '转科原因', width: 150, align: 'center'}
                , {field: 'tran_docnum',hide: get_now_cookie(userid+cookie_url+'/tran_docnum')=='false'?true:false, title: '转科文号', width: 150, align: 'center'}
                , {
                    field: 'approve_status_name',
                    hide: get_now_cookie(userid + cookie_url + '/approve_status_name') == 'false' ? true : false,
                    title: '审核状态',
                    width: 100,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'center'
                }
                , {
                    field: 'is_check_name',
                    hide: get_now_cookie(userid + cookie_url + '/is_check_name') == 'false' ? true : false,
                    title: '验收状态',
                    width: 90,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'center'
                }
                , {
                    field: 'operation',
                    title: '操作',
                    width: 310,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'left'
                }
            ]], done: function (res, curr) {
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
        table.on('tool(checkData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            if (layEvent === 'check'){
                //转科验收操作
                top.layer.open({
                    id: 'checks',
                    type: 2,
                    title: '【' + rows.assets + '】转科验收表单 ▼',
                    area: ['760px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?transNum='+rows.transfernum+'&assid='+rows.assid],
                    end: function () {
                        if(flag){
                            table.reload('checkLists', {
                                url: checkLists
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
            }else if (layEvent === 'showTransfer'){
                top.layer.open({
                    type: 2,
                    title: '【' + rows.assets + '】转科信息查看表单 ▼',
                    area: ['760px', '100%'],
                    scrollbar:false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    closeBtn: 1,
                    content: [url+'?action=showTransfer&transNum='+rows.transfernum+'&assid='+rows.assid],
                    end: function () {
                        if(flag){
                            table.reload('checkLists', {
                                url: checkLists
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
            }else if(layEvent === 'showAssets'){
                //显示设备详情
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】设备详情',
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['1050px', '100%'],
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?assid='+rows.assid]
                });
            }else if(layEvent === 'showDetails'){
                top.layer.open({
                    type: 2,
                    title: '【' + rows.assets + '】转科详情',
                    area: ['760px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url]
                });
            }else if(layEvent === 'printReport'){
                //打印报告
                top.layer.open({
                    type: 2,
                    offset:'r',
                    title: '打印审批单',
                    shade: [0.8, '#393D49'],
                    shadeClose:true,
                    anim:5,
                    scrollbar:false,
                    // area: ['99%', '98%'],
                    area: ['880px', '98%'],
                    closeBtn: 1,
                    content: [url]
                });
            }else if(layEvent === 'uploadReport'){
                //上传查看报告
                top.layer.open({
                    type: 2,
                    offset:'r',
                    title: '上传/查看审批单',
                    shade: [0.8, '#393D49'],
                    shadeClose:true,
                    anim:5,
                    scrollbar:false,
                    // area: ['99%', '98%'],
                    area: ['880px', '98%'],
                    closeBtn: 1,
                    content: [url]
                });
            }
        });

        //列排序
        table.on('sort(checkData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('checkLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(checkSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('checkLists', {
                url: checkLists
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

        table.on('toolbar(checkData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url');
            switch(event){
                case 'batch_print_transfer'://添加主设备
                    var checkStatus = table.checkStatus('checkLists');
                    var data = checkStatus.data;
                    if(data.length == 0){
                        layer.msg('请选择要打印审批单的设备！', {icon: 2, time: 2000});
                        return false;
                    }
                    var atid = '';
                    for(j = 0,len=data.length; j < len; j++) {
                        if(data[j]['is_check'] != 1){
                            layer.msg('请选择已验收通过的设备！',{icon : 2,time:2000});
                            return false;
                        }
                        atid += data[j]['atid']+',';
                    }
                    var params = {};
                    params.action = 'batchPrint';
                    params.atid = atid;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "html",
                        async:false,
                        beforeSend:beforeSend,
                        success: function (data) {
                            $('#transfer_report').html(data);
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2},1000);
                        },
                        complete:complete
                    });
                    var transfer_reportObj = $('#transfer_report');
                    transfer_reportObj.show();
                    transfer_reportObj.printArea();
                    transfer_reportObj.hide();
                    break;
            }
        });

        //设备名称搜索建议
        $("#transferExamineAssets").bsSuggest(
            returnAssets('transfer','assets')
        );

        //科室转出
        $("#transferExamineAssetsDepartmentOut").bsSuggest(
            returnDepartment()
        );

        //科室转入
        $("#transferExamineAssetsDepartmentIn").bsSuggest(
            returnDepartment()
        );


        //设备编号搜索建议
        $("#transferExamineAssetsNum").bsSuggest(
            returnAssets('transfer','assnum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="examineAssets"]').val(data.assets);
        });

    });
    exports('assets/transfer/checkLists', {});
});





