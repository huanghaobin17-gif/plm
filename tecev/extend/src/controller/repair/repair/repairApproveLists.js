var gloabOptions = {};
layui.define(function(exports){
    layui.use(['table', 'suggest', 'laydate', 'form', 'tablePlug'], function () {

        layer.config(layerParmas());

        var table = layui.table, suggest = layui.suggest, laydate = layui.laydate, form = layui.form, tablePlug = layui.tablePlug;

        //初始化时间
        lay('.formatDate').each(function(){
            laydate.render(dateConfig(this));
        });

        //初始化搜索建议插件
        suggest.search();

        layui.form.render();
        //第一个实例
        table.render({
            elem: '#getRepairApproveLists'
            , limits: [20, 50, 100]
            , limit: 20
            , loading: true
            , height: 'full-100' //高度最大化减去差值
            , title: '维修审批列表'
            , url: repairApproveListsUrl //数据接口
            , where: {
                sort: 'repid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'repid' //排序字段，对应 cols 设定的各字段名
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
                    field: 'repid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'repnum',
                    hide: get_now_cookie(userid + cookie_url + '/repnum') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '维修单号',
                    width: 160,
                    align: 'center'
                }
                , {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 160, align: 'center'}
                , {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 140, align: 'center'}
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格型号', width: 120, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 140, align: 'center'}
                , {field: 'archives_num',hide: get_now_cookie(userid+cookie_url+'/archives_num')=='false'?true:false, title: '档案编号', width: 140, align: 'center'}
                , {field: 'cat_name',hide: get_now_cookie(userid+cookie_url+'/cat_name')=='false'?true:false, title: '设备分类', width: 160, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值', width: 130, sort: true, align: 'center'}
                , {field: 'appUser',hide: get_now_cookie(userid+cookie_url+'/appUser')=='false'?true:false, title: '报修人', width: 90, align: 'center'}
                , {field: 'appTime',hide: get_now_cookie(userid+cookie_url+'/appTime')=='false'?true:false, title: '报修时间', width: 110, sort: true, align: 'center'}
                , {field: 'breakdown',hide: get_now_cookie(userid+cookie_url+'/breakdown')=='false'?true:false, title: '故障描述', width: 150, align: 'center'}
                , {field: 'appRemark',hide: get_now_cookie(userid+cookie_url+'/appRemark')=='false'?true:false, title: '报修备注', width: 100, align: 'center'}
                , {field: 'response',hide: get_now_cookie(userid+cookie_url+'/response')=='false'?true:false, title: '检修人', width: 100, align: 'center'}
                , {field: 'rRemark',hide: get_now_cookie(userid+cookie_url+'/rRemark')=='false'?true:false, title: '检修人意见', width: 150, align: 'center'}
                , {field: 'part_num',hide: get_now_cookie(userid+cookie_url+'/part_num')=='false'?true:false, title: '配件数量', width: 100, sort: true, align: 'center'}
                , {field: 'pPrice',hide: get_now_cookie(userid+cookie_url+'/pPrice')=='false'?true:false, title: '配件总费用', width: 120, sort: true, align: 'center'}
                , {field: 'ePrice',hide: get_now_cookie(userid+cookie_url+'/ePrice')=='false'?true:false, title: '预计费用', width: 120, sort: true, align: 'center'}
                , {field: 'aPrice',hide: get_now_cookie(userid+cookie_url+'/aPrice')=='false'?true:false, title: '实际费用', width: 120, sort: true, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 150,
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
        //监听工具条
        table.on('tool(repairApproveData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showAssets':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情信息',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1050px', '100%'],
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid]
                    });
                    break;
                case 'approve':
                    var flag = 1;
                    top.layer.open({
                        id: 'approves',
                        type: 2,
                        title: '【' + rows.assets + '】审批 ▼',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?repid=' + rows.repid+'&assid='+rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('getRepairApproveLists', {
                                    url: repairApproveListsUrl
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
                case 'showDetails':
                    //报修详情
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】维修详情',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['75%', '100%'],
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
            }
        });
        //监听排序
        table.on('sort(repairApproveData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('getRepairApproveLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });


        var $ = layui.$, active = {
            getCheckData: function () { //获取选中数据
                var checkStatus = table.checkStatus('userLists')
                    , data = checkStatus.data;
                layer.alert(JSON.stringify(data));
            }
            , getCheckLength: function () { //获取选中数目
                var checkStatus = table.checkStatus('userLists')
                    , data = checkStatus.data;
                layer.msg('选中了：' + data.length + ' 个');
            }
            , isAll: function () { //验证是否全选
                var checkStatus = table.checkStatus('userLists');
                layer.msg(checkStatus.isAll ? '全选' : '未全选')
            }
        };
        $('.demoTable .layui-btn').on('click', function () {
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });

        $("#repairApproveListsAssetsName").bsSuggest(
            returnAssets()
        );


        /*
         /选择分类
         */

        $("#repairApproveListsAssetsCat").bsSuggest(
            returnCategory()
        );


        /*
         /选择设备编号
         */

        $("#repairApproveListsAssNum").bsSuggest(
            returnAssnum()
        );

        form.verify({
            price_min: function (value, item) {
                if (value) {
                    var reg = /^[0-9]*[1-9][0-9]*$/;
                    if (!reg.test(value)) {
                        return '请输入正整数的金额';
                    }
                }
            },
            price_max: function (value, item) {
                if (value) {
                    var reg = /^[0-9]*[1-9][0-9]*$/;
                    if (!reg.test(value)) {
                        return '请输入正整数的金额';
                    }
                }
            }
        });
        //监听提交
        form.on('submit(repairApproveSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('getRepairApproveLists', {
                url: repairApproveListsUrl
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
    });
    exports('repair/repair/repairApproveLists', {});
});








