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
            elem: '#outSideApproveList'
            , limits: [20, 50, 100]
            , loading: true
            , limit: 20
            ,title: '外调审批列表'
            , url: outSideApproveListUrl //数据接口
            , where: {
                sort: 'outid',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'outid' //排序字段，对应 cols 设定的各字段名
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'outid',
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
                    width: 140,
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
                {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 120, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 150, align: 'center'},
                {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', width: 120, align: 'center'},
                {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值(元)', width: 120, align: 'center'},
                {field: 'statusName',hide: get_now_cookie(userid+cookie_url+'/statusName')=='false'?true:false, title: '设备状态', width: 120, align: 'center'},
                {field: 'guarantee_status',hide: get_now_cookie(userid+cookie_url+'/guarantee_status')=='false'?true:false, title: '维保状态', width: 120, align: 'center'},
                // {field: 'patrol_status', title: '巡查保养状态', width: 120, align: 'center'},
                // {field: 'metering_status', title: '计量状态', width: 120, align: 'center'},
                {field: 'repairNum',hide: get_now_cookie(userid+cookie_url+'/repairNum')=='false'?true:false, title: '维修次数', width: 100, align: 'center'},
                // {field: 'insuredsum', title: '维保次数', width: 90, align: 'center'},
                {field: 'apply_type_name',hide: get_now_cookie(userid+cookie_url+'/apply_type_name')=='false'?true:false, title: '申请类型', width: 120, align: 'center'},
                {field: 'reason',hide: get_now_cookie(userid+cookie_url+'/reason')=='false'?true:false, title: '外调原因', width: 200, align: 'center'},
                {
                    field: 'accept',
                    hide: get_now_cookie(userid + cookie_url + '/accept') == 'false' ? true : false,
                    title: '外调目的地',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    width: 200,
                    align: 'center'
                },
                {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
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

        //操作栏
        table.on('tool(outSideApproveData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'assetOutsideApprove':
                    var flag = 1;
                    top.layer.open({
                        id: 'assetOutsideApprove',
                        type: 2,
                        title: '【' + rows.assets + '】 审批',
                        area: ['760px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid + '&outid=' + rows.outid],
                        end: function () {
                            if (flag) {
                                table.reload('outSideApproveList', {
                                    url: outSideApproveListUrl
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
                        title: '【' + rows.assets + '】外调审批详情',
                        area: ['760px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'showAssets':
                    //显示设备详情
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1050px', '100%'],
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid]
                    });
                    break;
            }
        });

        //搜索
        form.on('submit(outSideApproveListSearch)', function (data) {
            var field = data.field;
            gloabOptions.departid = field.departid;
            gloabOptions.assetsName = field.assetsName;
            gloabOptions.assetsModel = field.assetsModel;
            gloabOptions.hospital_id = field.hospital_id;
            table.reload('outSideApproveList', {
                url: outSideApproveListUrl
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
        var outSideApproveListObj = $("#LAY-Assets-Outside-outSideApproveList");
        if (Math.floor(outSideApproveListObj.find(".layui-form-item").width() / outSideApproveListObj.find(".layui-inline").width()) == 3) {
            position = '';
        } else {
            position = 1;
        }

        //设备名称 搜索建议
        $("#outSideApproveListAssetsName").bsSuggest(
            returnAssets()
        );

        //规格型号 搜索建议
        $("#outSideApproveListModel").bsSuggest(
            returnAssetsModel()
        );

    });
    exports('assets/outside/outSideApproveList', {});
});