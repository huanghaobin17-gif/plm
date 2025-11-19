layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
    var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

    //渲染多选下拉
    formSelects.render();
    //初始化搜索建议插件
    suggest.search();

    layer.config(layerParmas());

    //先更新页面部分需要提前渲染的控件
    form.render();

    //录入时间元素渲染
    laydate.render({
        elem: '#adddate' //指定元素
    });


    //定义一个全局空对象
    var gloabOptions = {};
    table.render({
        elem: '#getInstallDebugList'
        //,height: '600'
        , limits: [10, 20, 50, 100]
        , loading: true
        , limit: 10
        , url: '' //数据接口
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
        }
        //,page: true //开启分页

        , cols: [[ //表头
            {
                field: 'assid',
                title: '序号',
                width: 50,
                style: 'background-color: #f9f9f9;',
                fixed: 'left',
                align: 'center',
                type: 'space',
                templet: function (d) {
                    return d.LAY_INDEX;
                }
            },
            {field: '', title: '设备名称', width: 300, align: 'center'},
            {field: '', title: '规格 / 型号', width: 180, align: 'center'},
            {field: '', title: '领用科室', width: 100, align: 'center'},
            {field: '', title: '开始日期', width: 120, align: 'center'},
            {field: '', title: '结束日期', width: 120, align: 'center'},
            {field: '', title: '上报科室', width: 150, align: 'center'},
            {field: '', title: '上报日期', width: 120, align: 'center'},
            {field: '', title: '上报设备数量', width: 120, align: 'center'},
            {field: 'operation', style: 'background-color: #f9f9f9;', title: '操作', width: 120, align: 'center'}
            /*除了有新增计划的权限用户，其他科室只看科室自己的，当是可以看多个科室的权限用户时，如计划创建人、审批人，那么结束日期左边的都用colspan = 科室数量，即并行
             * 操作：
             *   编辑：
             *   查看：
             *   审批：
             * */
        ]]
    });

    //新增计划
    $('#installDebug').on('click',function() {
        var flag = 1;
        var url = $(this).attr('data-url');
        top.layer.open({
            type: 2,
            title: $(this).html(),
            scrollbar:false,
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            area: ['40%', '100%'],
            closeBtn: 1,
            content: [url],
            end: function () {
                if(flag){
                    table.reload('assetsLists', {
                        url: getAssetsList
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
        return false;
    });



});