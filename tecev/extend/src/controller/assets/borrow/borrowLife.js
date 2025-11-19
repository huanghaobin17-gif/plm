layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'laydate', 'tablePlug'], function () {
        var layer = layui.layer
            , form = layui.form
            , table = layui.table
            , tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();
        layer.config(layerParmas());


        table.render({
            elem: '#borrowLife'
            ,size: 'lg'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '借调进程列表'
            , url: borrowLifeUrl //数据接口
            , where: {
                sort: 'borid',
                order: 'desc'
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
            , cols:[ [ //表头
                {field: 'borid', title: '序号', width: 65, align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'borrow_num', title: '流水号', width: 160, align: 'center'},
                {field: 'department', title: '所属科室', width: 150, align: 'center'},
                {field: 'assnum', title: '设备编号', width: 150,  align: 'center'},
                {field: 'assets', title: '设备名称', width: 150,  align: 'center'},
                {field: 'operation', title: '进程', align: 'center', width: 730}
            ]]
        });


    });
    exports('assets/borrow/borrowLife', {});
});