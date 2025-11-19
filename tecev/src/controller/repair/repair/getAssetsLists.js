layui.define(function(exports){
    layui.use(['laydate', 'table', 'form', 'suggest', 'tablePlug'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, laydate = layui.laydate, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();
        //初始化时间
        lay('.formatDate').each(function(){
            laydate.render(dateConfig(this));
        });
        //初始化弹窗
        layer.config(layerParmas());
        //更新渲染
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};

        //第一个实例
        table.render({
            elem: '#getRepairAssetsLists'
            //,height: '600'
            , limits: [20, 50, 100]
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            , loading: true
            ,title: '设备报修列表'
            , url: getAssetsListsUrl //数据接口
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
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备编号',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'assorignum',
                    hide: get_now_cookie(userid + cookie_url + '/assorignum') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备原编号',
                    width: 150,
                    align: 'center'
                }
                , {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 160, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 160, align: 'center'}
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格型号', width: 130, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '分类名称', width: 140, align: 'center'}
                , {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', sort: true, width: 110, align: 'center'}
                , {field: 'lastrepairtime',hide: get_now_cookie(userid+cookie_url+'/lastrepairtime')=='false'?true:false, title: '上一次维修时间', sort: true, width: 140, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '原值(元)', sort: true, width: 130, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 140,
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

        //监听工具条
        table.on('tool(assetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'showAssets':
                    //设备详情
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
                case 'showDetails':
                    //报修详情
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】维修信息',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['900px', '100%'],
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'addRepair':
                    //报修操作
                    top.layer.open({
                        id: 'addRepairs',
                        type: 2,
                        title: '【' + rows.assets + '】报修申请表单',
                        area: ['680px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('getRepairAssetsLists', {
                                    url: getAssetsListsUrl
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                                //待开发 消息提醒
                                //newMessage();
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'checkRepair':
                    //验收
                    top.layer.open({
                        id: 'checkRepairs',
                        type: 2,
                        title: '【' + rows.assets + '】维修验收表单',
                        area: ['680px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?repid=' + rows.repid+'&assid=' + rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('getRepairAssetsLists', {
                                    url: getAssetsListsUrl
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

        //监听排序
        table.on('sort(assetsData)', function (obj) {
            if (obj.field === 'department') {
                obj.field = 'departid';
            }
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('getRepairAssetsLists', {
                url: getAssetsListsUrl
                ,initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }, page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
        });

        //监听提交
        form.on('submit(getAssetsSearch)', function (data) {
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('getRepairAssetsLists', {
                url: getAssetsListsUrl
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
        var position = '';
        if (Math.floor($("#LAY-Repair-Repair-getAssetsLists .layui-form-item").width() / $("#LAY-Repair-Repair-getAssetsLists .layui-inline").width()) == 3) {
            position = '';
        } else {
            position = 1;
        }
        //选择设备
        $("#getAssetsListsAssetsName").bsSuggest(returnAssets());

        //选择分类
        $("#getAssetsListsAssetsCat").bsSuggest(returnCategory('', position));

        //选择设备编号
        $("#getAssetsListsAssetsNum").bsSuggest(returnAssnum());

    });
    exports('repair/repair/getAssetsLists', {});
});





