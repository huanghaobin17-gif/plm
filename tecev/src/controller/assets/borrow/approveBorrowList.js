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
            elem: '#approveBorrowList'
            , limits: [20, 50, 100]
            , loading: true
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            ,title: '借调审批列表'
            , url: approveBorrowListUrl //数据接口
            , where: {
                sort: 'borid'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'borid' //排序字段，对应 cols 设定的各字段名
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
                    field: 'borid',
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

                {field: 'borrow_num',hide: get_now_cookie(userid+cookie_url+'/borrow_num')=='false'?true:false, title: '流水号', width: 160, align: 'center'},
                {field: 'apply_department',hide: get_now_cookie(userid+cookie_url+'/apply_department')=='false'?true:false, title: '申请科室', width: 160, align: 'center'},
                {field: 'apply_user',hide: get_now_cookie(userid+cookie_url+'/apply_user')=='false'?true:false, title: '申请人', width: 100, align: 'center'},
                {field: 'apply_time',hide: get_now_cookie(userid+cookie_url+'/apply_time')=='false'?true:false, title: '申请日期', width: 120, align: 'center'},
                {field: 'borrow_reason',hide: get_now_cookie(userid+cookie_url+'/borrow_reason')=='false'?true:false, title: '借用原因', width: 200, align: 'center'},
                {field: 'estimate_back',hide: get_now_cookie(userid+cookie_url+'/estimate_back')=='false'?true:false, title: '归还时间', width: 145, align: 'center'},
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 180, align: 'center'},
                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 180, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 160, align: 'center'},//列表的同个科室rowspan合并为一个
                {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 120, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 130, align: 'center'},
                {field: 'statusName',hide: get_now_cookie(userid+cookie_url+'/statusName')=='false'?true:false, title: '设备状态', width: 100, align: 'center'},
                {field: 'deparment_approve',hide: get_now_cookie(userid+cookie_url+'/deparment_approve')=='false'?true:false, title: '借出科室审批', width: 120, align: 'center'},
                {field: 'assets_approve',hide: get_now_cookie(userid+cookie_url+'/assets_approve')=='false'?true:false, title: '设备科审核', width: 120, align: 'center'},
                {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    minWidth: 120,
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
        table.on('tool(approveBorrowData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'approveBorrow':
                    var flag = 1;
                    top.layer.open({
                        id: 'approveBorrows',
                        type: 2,
                        title: '【' + rows.assets + '】 借调审批',
                        area: ['750px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid + '&borid=' + rows.borid],
                        end: function () {
                            if (flag) {
                                table.reload('approveBorrowList', {
                                    url: approveBorrowListUrl
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
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】借调审批详情',
                        area: ['750px', '100%'],
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
        form.on('submit(approveBorrowListSearch)', function (data) {
            var field = data.field;
            gloabOptions.departid = field.departid;
            gloabOptions.assetsName = field.assetsName;
            gloabOptions.assetsModel = field.assetsModel;
            gloabOptions.hospital_id = field.hospital_id;
            table.reload('approveBorrowList', {
                url: approveBorrowListUrl
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
        if (Math.floor($("#LAY-Assets-Borrow-approveBorrowList .layui-form-item").width() / $("#LAY-Assets-Borrow-approveBorrowList .layui-inline").width()) == 3) {
            position = '';
        } else {
            position = 1;
        }

        //设备名称 搜索建议
        $("#approveBorrowListAssetsName").bsSuggest(
            returnAssets()
        );

        //规格型号 搜索建议
        $("#approveBorrowListModel").bsSuggest(
            returnAssetsModel(position)
        );

    });
    exports('assets/borrow/approveBorrowList', {});
});