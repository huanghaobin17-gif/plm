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
            elem: '#getDepartAssetsList'
            , limits: [20, 50, 100]
            , loading: true
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            ,title: '外调申请列表'
            , url: getDepartAssetsListUrl //数据接口
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
                    width: 160,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center'
                },
                {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    width: 160,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center'
                },
                {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '设备分类', width: 160, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 160, align: 'center'},
                {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 120, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 150, align: 'center'},
                {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', width: 105, align: 'center'},
                {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值(元)', width: 120, align: 'center'},
                {field: 'guarantee_status',hide: get_now_cookie(userid+cookie_url+'/guarantee_status')=='false'?true:false, title: '维保状态', width: 100, align: 'center'},
                // {field: 'insuredsum', title: '维保次数', width: 90, align: 'center'},
                {
                    field: 'statusName',
                    hide: get_now_cookie(userid + cookie_url + '/statusName') == 'false' ? true : false,
                    title: '设备状态',
                    width: 120,
                    align: 'center',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right'
                },
                {
                    field: 'operation',
                    hide: get_now_cookie(userid + cookie_url + '/operation') == 'false' ? true : false,
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
        //操作栏
        table.on('tool(getDepartAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'applyAssetOutSide':
                    var flag = 1;
                    top.layer.open({
                        id: 'applyAssetOutSides',
                        type: 2,
                        title: '<i class="layui-icon layui-icon-zzexchange"></i>【' + rows.assets + '】 外调申请',
                        area: ['760px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('getDepartAssetsList', {
                                    url: getDepartAssetsListUrl
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
                params.action = 'end';
                params.outid = $(this).attr('data-id');
                layer.confirm('本次申请外调审批不通过，确认结束该进程吗？', {icon: 3, title: '结束进程'}, function () {
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
                                        table.reload('getDepartAssetsList', {
                                            url: getDepartAssetsListUrl
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
                    title: '【' + rows.assets + '】外调重审表单 ▼',
                    area: ['75%', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url],
                    end: function () {
                        if(flag){
                            table.reload('getDepartAssetsList', {
                                url: getDepartAssetsListUrl
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


        //搜索
        form.on('submit(getDepartAssetsListSearch)', function (data) {
            var field = data.field;
            gloabOptions.assetsName = field.assetsName;
            gloabOptions.assnum = field.assnum;
            gloabOptions.category = field.category;
            gloabOptions.department = field.department;
            gloabOptions.status = field.status;
            gloabOptions.assetsModel = field.assetsModel;
            gloabOptions.hospital_id = field.hospital_id;
            table.reload('getDepartAssetsList', {
                url: getDepartAssetsListUrl
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
        var getDepartAssetsListObj = $("#LAY-Assets-Borrow-getDepartAssetsList");
        if (Math.floor(getDepartAssetsListObj.find('.layui-form-item').width() / getDepartAssetsListObj.find('.layui-inline').width()) == 3) {
            position = '';
        } else {
            position = 1;
        }

        //设备名称 搜索建议
        $("#getDepartAssetsListAssetsName").bsSuggest(
            returnAssets('job')
        );
        //科室搜索建议
        $("#getAssetsListDepartment").bsSuggest(
            returnDepartment()
        );
        //设备编号搜索建议
        $("#getAssetsListAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='getAssetsListAssets']").val(data.assets);
            $("input[name='getAssetsListAssorignum']").val(data.assorignum);
        });
        //分类搜索建议
        $("#getAssetsListCategory").bsSuggest(
            returnCategory('',position)
        );
        //规格型号 搜索建议
        $("#getDepartAssetsListModel").bsSuggest(
            returnAssetsModel()
        );
    });
    exports('assets/outside/getDepartAssetsList', {});
});