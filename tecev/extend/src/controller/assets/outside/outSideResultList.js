layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer
            , form = layui.form
            , suggest = layui.suggest
            , table = layui.table
            , tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();
        suggest.search();
        layer.config(layerParmas());

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#outSideResultList'
            , limits: [20, 50, 100]
            , loading: true
            , limit: 20
            , title: '外调验收列表'
            , height: 'full-100' //高度最大化减去差值
            , url: outSideResultListUrl //数据接口
            , where: {
                sort: 'assid',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-Assets-Outside-outSideResultListBar',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    type: 'checkbox',
                    fixed: 'left'
                },
                {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    rowspan: 2,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    title: '设备编号',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                },
                {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 180,
                    align: 'center'
                },
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 160, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 100, align: 'center'},
                {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 100, align: 'center'},
                {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', width: 110, align: 'center'},
                {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值(元)', width: 120, align: 'center'},
                {field: 'apply_type_name',hide: get_now_cookie(userid+cookie_url+'/apply_type_name')=='false'?true:false, title: '申请类型', width: 90, align: 'center'},
                {field: 'reason',hide: get_now_cookie(userid+cookie_url+'/reason')=='false'?true:false, title: '外调原因', width: 200, align: 'center'},
                {field: 'accept',hide: get_now_cookie(userid+cookie_url+'/accept')=='false'?true:false, title: '外调目的地', width: 200, align: 'center'},
                {field: 'approve_status',hide: get_now_cookie(userid+cookie_url+'/approve_status')=='false'?true:false, title: '审批状态',fixed: 'right', width: 100, align: 'center'},
                {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 260,
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
        //操作栏
        table.on('tool(outSideResultData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'checkOutSiteAsset':
                    var flag = 1;
                    top.layer.open({
                        id: 'checkOutSiteAssets',
                        type: 2,
                        title: '【' + rows.assets + '】 外调申请-验收单录入',
                        area: ['750px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid + '&outid=' + rows.outid],
                        end: function () {
                            if (flag) {
                                table.reload('outSideResultList', {
                                    url: outSideResultListUrl
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
                case 'showLife':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情',
                        area: ['1000px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'printReport':
                    //打印报告
                    top.layer.open({
                        type: 2,
                        offset:'r',
                        title: '打印模板',
                        shade: [0.8, '#393D49'],
                        shadeClose:true,
                        anim:5,
                        scrollbar:false,
                        // area: ['99%', '98%'],
                        area: ['950px', '100%'],
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'uploadReport':
                    //上传报告
                    top.layer.open({
                        type: 2,
                        offset:'r',
                        title: '上传/查看审批单',
                        shade: [0.8, '#393D49'],
                        shadeClose:true,
                        anim:5,
                        scrollbar:false,
                        // area: ['99%', '98%'],
                        area: ['950px', '100%'],
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
            }
        });
        //搜索
        form.on('submit(outSideResultListSearch)', function (data) {
            var field = data.field;
            gloabOptions.assetsName = field.assetsName;
            gloabOptions.assetsModel = field.assetsModel;
            gloabOptions.hospital_id = field.hospital_id;
            table.reload('outSideResultList', {
                url: outSideResultListUrl
                , where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                , page: {
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


        table.on('toolbar(outSideResultData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url');
            switch(event){
                case 'batch_print_outSide':
                    var checkStatus = table.checkStatus('outSideResultList');
                    var data = checkStatus.data;
                    if(data.length === 0){
                        layer.msg('请选择要打印审批单的设备！', {icon: 2, time: 2000});
                        return false;
                    }
                    var outid = '';
                    for(var j = 0,len=data.length; j < len; j++) {
                        if(parseInt(data[j]['status'])!==parseInt(OUTSIDE_STATUS_COMPLETE)){
                            layer.msg('请选择已验收的设备！',{icon : 2,time:2000});
                            return false;
                        }
                        outid += data[j]['outid']+',';
                    }
                    var params = {};
                    params.action = 'batchPrint';
                    params.outid = outid.substring(0, outid.length - 1);
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "html",
                        async:false,
                        beforeSend:beforeSend,
                        success: function (data) {
                            $('#outside_report').html(data);
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2},1000);
                        },
                        complete:complete
                    });
                    var outside_report=$('#outside_report');
                    outside_report.show();
                    outside_report.printArea();
                    outside_report.hide();
                    break;
            }
        });


        //判断搜索建议的位置
        var position = '';
        var outSideResultListObj = $("#LAY-Assets-Outside-outSideResultList");
        if (Math.floor(outSideResultListObj.find(".layui-form-item").width() / outSideResultListObj.find(".layui-inline").width()) == 3) {
            position = '';
        } else {
            position = 1;
        }

        //设备名称 搜索建议
        $("#outSideResultListAssetsName").bsSuggest(
            returnAssets()
        );

        //规格型号 搜索建议
        $("#outSideResultListModel").bsSuggest(
            returnAssetsModel(position)
        );
    });
    exports('assets/outside/outSideResultList', {});
});