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
            elem: '#subsidiaryAllotList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '借调申请列表'
            , url: subsidiaryAllotListUrl //数据接口
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
            toolbar: false,
            defaultToolbar: false
            , cols: [[ //表头
                {
                    field: 'assid', title: '序号', width: 65, fixed: 'left', align: 'center', type: 'space', rowspan: 2,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false, title: '设备编号', width: 150, align: 'center'},
                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 150, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 150, align: 'center'},
                {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 120, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 180, align: 'center'},
                {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值', width: 120, align: 'center'},
                {field: 'statusName',hide: get_now_cookie(userid+cookie_url+'/statusName')=='false'?true:false, title: '设备状态', width: 120, align: 'center'},
                {field: 'operation', title: '操作', fixed: 'right', width: 120, align: 'center'}
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

        //操作栏
        table.on('tool(subsidiaryAllotData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'subsidiaryAllot':
                    var flag = 1;
                    top.layer.open({
                        id: 'subsidiaryAllot',
                        type: 2,
                        title: '【' + rows.assets + '】 分配申请',
                        area: ['750px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('subsidiaryAllotList', {
                                    url: subsidiaryAllotListUrl
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

        //搜索
        form.on('submit(subsidiaryAllotListSearch)', function (data) {
            var field = data.field;
            gloabOptions.departid = field.departid;
            gloabOptions.assetsName = field.assetsName;
            table.reload('subsidiaryAllotList', {
                url: subsidiaryAllotListUrl
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });




        //设备名称 搜索建议
        $("#subsidiaryAllotListAssetsName").bsSuggest(
            {
                url: admin_name+"/Public/getSubsidiaryAllotAssets.html",
                effectiveFields: ["assnum", "assets", "pinyin", "assorignum"],
                searchFields: ["assets"],
                effectiveFieldsAlias: {assnum: "设备编号", assets: "设备名称", pinyin: "拼音", assorignum: "设备原编号"},
                ignorecase: false,
                showHeader: true,
                listStyle: {
                    "max-height": "330px", "max-width": "500px",
                    "overflow": "auto", "width": "500px", "text-align": "center"
                },
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                idField: 'assets',
                keyField: 'assets',
                clearable: false
            }
        );
    });
    exports('assets/subsidiary/subsidiaryAllotList', {});
});