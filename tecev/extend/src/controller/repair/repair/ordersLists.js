var gloabOptions = {};
layui.define(function(exports){
    layui.use(['form', 'suggest', 'laydate', 'table', 'tablePlug'], function () {
        var form = layui.form, suggest = layui.suggest, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;

        //初始化时间
        lay('.formatDate').each(function(){
            laydate.render(dateConfig(this));
        });

        layer.config(layerParmas());

        form.render();
        //第一个实例
        table.render({
            elem: '#getOrdersLists'
            , limits: [20, 50, 100]
            , limit: 20
            , loading: true
            ,title: '接单响应列表'
            , height: 'full-100' //高度最大化减去差值
            , url: ordersListsUrl //数据接口
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
            , page: {
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
                , {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编码', width: 160, align: 'center'}
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格型号', width: 120, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 140, align: 'center'}
                , {field: 'archives_num',hide: get_now_cookie(userid+cookie_url+'/archives_num')=='false'?true:false, title: '档案编号', width: 140, align: 'center'}
                , {field: 'applicant',hide: get_now_cookie(userid+cookie_url+'/applicant')=='false'?true:false, title: '报修人', width: 90, align: 'center'}
                , {field: 'applicant_time',hide: get_now_cookie(userid+cookie_url+'/applicant_time')=='false'?true:false, title: '报修日期', sort: true, width: 110, align: 'center'}
                , {field: 'responder',hide: get_now_cookie(userid+cookie_url+'/responder')=='false'?true:false, title: '派单人员', width: 100, align: 'center'}
                , {field: 'response_date',hide: get_now_cookie(userid+cookie_url+'/response_date')=='false'?true:false, title: '接单时间', width: 170, align: 'center'}
                , {field: 'expect_arrive',hide: get_now_cookie(userid+cookie_url+'/expect_arrive')=='false'?true:false, title: '预计到场时间', width: 170, align: 'center'}
                , {field: 'response',hide: get_now_cookie(userid+cookie_url+'/response')=='false'?true:false, title: '接单工程师', width: 100, align: 'center'}
                , {field: 'breakdown',hide: get_now_cookie(userid+cookie_url+'/breakdown')=='false'?true:false, title: '故障描述', width: 250, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 165,
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
        table.on('tool(orderData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showAssets':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid]
                    });
                    break;
                case 'accept':
                    var flag = 1;
                    top.layer.open({
                        id: 'accepts',
                        type: 2,
                        title: '【' + rows.assets + '】维修单 - '+ $(this).html(),
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?repid=' + rows.repid+'&assid='+rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('getOrdersLists', {
                                    url: ordersListsUrl
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
                        area: ['1000px', '100%'],
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'modify':
                   $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: ordersListsUrl,
                            data: {departid:rows.departid,repid:rows.repid,action:'getengineer'},
                            dataType: "json",
                            success: function (data) {
                                $('select[name="edit_engineer"]').empty();
                                var html = '';
                                $.each(data.data, function (k,v) {
                                    if (v.username==data.response) {
                                        html+='<option value="'+v.username+'" selected="selected">'+v.username+'</option>';
                                    }else{
                                        html+='<option value="'+v.username+'">'+v.username+'</option>';
                                    }
                                });
                                $('select[name="edit_engineer"]').append(html);
                                form.render();
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                   layer.open({
                         type: 1,
                         content: $('#update_user'),
                         title:'将维修单' + rows.repnum + '转让',
                         area: ['400px', '250px'],
                         success: function (layero, index) {
                            form.on('submit(edit_engineer)', function (data) {
                                var params = data.field;
                                params.repid = rows.repid;
                                params.action = 'edit_engineer';
                                $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: ordersListsUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                layer.closeAll();
                                table.reload('getOrdersLists');
                                if (data.status==1) {
                                layer.msg(data.msg, {icon: 1, time: 3000});
                                }else{
                                layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                                
                                
                            }
                            })
                         })
                           // layer.close(layer.index);
                    }});
                    break;
            }
        });

        //监听排序
        table.on('sort(orderData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('getOrdersLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        suggest.search();
        //监听提交
        form.on('submit(orderSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            table.reload('getOrdersLists', {
                url: ordersListsUrl
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //选择设备
        $("#ordersListsAssetsName").bsSuggest(
            returnAssets()
        );

        // 选择分类
        $("#ordersListsAssetsCat").bsSuggest(
            returnCategory('',1)
        );
    });
    exports('repair/repair/ordersLists', {});
});




