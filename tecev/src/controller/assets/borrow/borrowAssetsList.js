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
        elem: '#borrowAssetsList'
        //,height: '600'
        , limits: [20, 50, 100]
        , loading: true
        , limit: 20
        ,title: '借调申请列表'
        , height: 'full-100' //高度最大化减去差值
        , url: borrowAssetsListUrl //数据接口
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
            //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
            //,curr: 5 //设定初始在第 5 页
            //,theme: '#428bca' //当前页码背景色
            groups: 10 //只显示 1 个连续页码
            , prev: '上一页'
            , next: '下一页'
        },
        toolbar: 'true',
        defaultToolbar: ['filter','exports']
        , cols: [[ //表头
            {
                field: 'assid',
                title: '序号',
                width: 65,
                fixed: 'left',
                style: 'background-color: #f9f9f9;',
                align: 'center',
                type: 'space',
                rowspan: 2,
                templet: function (d) {
                    return d.LAY_INDEX;
                }
            },
            {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 150, align: 'center'},//列表的同个科室rowspan合并为一个
            {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 150, align: 'center'},
            {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 150, align: 'center'},
            {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 120, align: 'center'},
            {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 180, align: 'center'},
            {field: 'statusName',hide: get_now_cookie(userid+cookie_url+'/statusName')=='false'?true:false, title: '设备状态', width: 120, align: 'center'},
            {
                field: 'operation',
                title: '操作',
                fixed: 'right',
                minWidth: 150,
                style: 'background-color: #f9f9f9;',
                align: 'center'
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
    table.on('tool(borrowAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        var rows = obj.data; //获得当前行数据
        var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
        var url = $(this).attr('data-url');
        switch (layEvent) {
            case 'applyBorrow':
                var flag = 1;
                top.layer.open({
                    id: 'applyBorrows',
                    type: 2,
                    title: '<i class="layui-icon layui-icon-zzexchange"></i>【' + rows.assets + '】 申请借调',
                    area: ['700px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url + '?assid=' + rows.assid],
                    end: function () {
                        if (flag) {
                            table.reload('borrowAssetsList', {
                                url: borrowAssetsListUrl
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
            case 'over':
                //结束进程
                var params = {};
                    params.type = 'end';
                    params.borid = $(this).attr('data-id');
                layer.confirm('本次申请借调审批不通过，确认结束该进程吗？', {icon: 3, title: '结束进程'}, function () {
                        $.ajax({
                            timeout: 5000,
                            dataType: "json",
                            type:"POST",
                            url:url,
                            data:params,
                            async:false,
                            beforeSend:function(){
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success:function(data){
                                if(data.status == 1){
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('borrowAssetsList', {
                                            url: borrowAssetsListUrl
                                            ,where: gloabOptions
                                            ,page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                }else{
                                    layer.msg(data.msg,{icon : 2,time:3000});
                                }
                            },
                            //调用出错执行的函数
                            error: function(){
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 2});
                            },
                            complete:function () {
                                layer.closeAll('loading');
                            }
                        });
                    });
                break;
            case 'edit':
                //报废申请
                var flag = 1;
                top.layer.open({
                    id: 'applys',
                    type: 2,
                    title: '【' + rows.assets + '】借调重审表单 ▼',
                    area: ['700px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url],
                    end: function () {
                        if(flag){
                            table.reload('borrowAssetsList', {
                                url: borrowAssetsListUrl
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
            case 'showDetails':
                top.layer.open({
                    type: 2,
                    title: '【' + rows.projectName + '】审批详情',
                    area: ['700px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url]
                });
                break;
        }
    });

    //搜索
    form.on('submit(borrowAssetsListSearch)', function (data) {
        var field = data.field;
        gloabOptions.departid = field.departid;
        gloabOptions.assnum = field.assnum;
        gloabOptions.status = field.status; 
        gloabOptions.assetsName = field.assetsName; 
        gloabOptions.assetsModel = field.assetsModel;
        gloabOptions.hospital_id = field.hospital_id;
        table.reload('borrowAssetsList', {
            url: borrowAssetsListUrl
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


    //判断搜索建议的位置
    position = '';
    if (Math.floor($("#LAY-Assets-Borrow-borrowAssetsList .layui-form-item").width() / $("#LAY-Assets-Borrow-borrowAssetsList .layui-inline").width()) == 3) {
        position = '';
    } else {
        position = 1;
    }

    //设备名称 搜索建议
    $("#borrowAssetsListAssetsName").bsSuggest(
        returnAssets()
    );

    //规格型号 搜索建议
    $("#borrowAssetsListModel").bsSuggest(
        returnAssetsModel(position)
    );
    //设备编号搜索建议
    $("#borrowAssetsListAssnum").bsSuggest(
        returnAssnum()
    );

});
    exports('assets/borrow/borrowAssetsList', {});
});