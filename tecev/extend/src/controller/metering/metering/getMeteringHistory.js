layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'laydate', 'suggest', 'tablePlug','formSelects'], function () {
        var layer = layui.layer
            , form = layui.form
            , table = layui.table
            , laydate = layui.laydate
            , suggest = layui.suggest
            , formSelects = layui.formSelects
            , tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();
        //渲染所有多选下拉
        formSelects.render('mer_res_department', selectParams(1));
        formSelects.btns('mer_res_department', selectParams(2), selectParams(3));
        suggest.search();
        layer.config(layerParmas());
        laydate.render({
            elem: '#startDate',
            festival: true,
            min: '1'
        });
        laydate.render({
            elem: '#endDate',
            festival: true,
            min: '1'
        });

        //定义一个全局空对象
        var gloabOptions = {};

        table.render({
            elem: '#getMeteringHistory'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '计量记录查询列表'
            , url: getMeteringHistory //数据接口
            , where: {
                is_metering: 1,
                type: 'getMeteringHistory',
                sort: 'mpid',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'mpid' //排序字段，对应 cols 设定的各字段名
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
                    field: 'mpid', title: '序号', width: 65, fixed: 'left', align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field:'plan_num',hide: get_now_cookie(userid+cookie_url+'/plan_num')=='false'?true:false,fixed:'left',title:'计划编号',width:140, align: 'center'},//同一个质控计划名称的合并行，如何合并问俊生

                {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false,  title: '设备名称', width: 160, align: 'center'},
                {field: 'assnum',hide: get_now_cookie(userid+cookie_url+'/assnum')=='false'?true:false,  title: '设备编号', width: 160, align: 'center'},
                {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格 / 型号', width: 160, align: 'center'},
                {field: 'productid',hide: get_now_cookie(userid+cookie_url+'/productid')=='false'?true:false, title: '产品序列号', width: 160, align: 'center'},
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '所属科室', width: 160, align: 'center'},
                {field: 'cycle',hide: get_now_cookie(userid+cookie_url+'/cycle')=='false'?true:false, title: '计量周期（月）', width: 130, align: 'center'},
                {field: 'times',hide: get_now_cookie(userid+cookie_url+'/times')=='false'?true:false, title: '计量次数', width: 100, align: 'center',templet: function (d) {
                        return (d.times > 0) ? '<span style="color: #FF5722;">'+d.times+'</span>' : d.times;
                    }},
                {field: 'mcategory',hide: get_now_cookie(userid+cookie_url+'/mcategory')=='false'?true:false, title: '计量分类', width: 160, align: 'center'},
                {field: 'mp_status',hide: get_now_cookie(userid+cookie_url+'/mp_status')=='false'?true:false, title: '计划状态', width: 90,fixed: 'right', align: 'center',templet: function (d) {
                    return (d.mp_status == 1) ? '<span style="color: #5FB878;">启用</span>' : (d.mp_status == 0 ? '<span style="color: #FF5722;">停用</span>' : '已删除');
                }},
                {field: 'operation', fixed: 'right', title: '操作', minWidth: 80, align: 'center'}
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


        table.on('tool(getMeteringHisData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = admin_name + '/Metering/getMeteringHistory';
            var flag = 1;
            switch (layEvent) {
                case 'showMeteringHistory':
                    top.layer.open({
                        type: 2,
                        anim: 2,
                        title: '【' + rows.assets + '】计量检测历史记录',
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        area: ['1080px', '100%'],
                        closeBtn: 1,
                        content: [url + '?mpid=' + rows.mpid + '&type=showMeteringHistory'],
                    });
                    break;
            }
        });

        form.on('submit(getMeteringHisSearch)', function (data) {
            gloabOptions = data.field;
            gloabOptions.type = 'getMeteringHistory';
            table.reload('getMeteringHistory', {
                url: getMeteringHistory
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //选择设备
        $("#getMeteringResultAssetsName").bsSuggest(
            returnAssets()
        );

    });
    exports('metering/metering/getMeteringHistory', {});
});
