layui.define(function (exports) {
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'laydate', 'form', 'upload', 'tablePlug'], function () {
        var table = layui.table, suggest = layui.suggest, laydate = layui.laydate, form = layui.form,
            upload = layui.upload, tablePlug = layui.tablePlug;
        form.render();
        //监听提交
        form.on('submit(searchInterface)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            console.log(gloabOptions)
            table.reload('interfaceLists', {
                url: getInterfaceList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });


        table.render({
            elem: '#interfaceLists'
            , limits: [100, 500, 1000, 5000]
            , loading: true
            , limit: 100
            , height: 'full-150'
            , title: '接口日志'
            , url: getInterfaceList //数据接口
            , where: {
                sort: 'id'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'id' //排序字段，对应 cols 设定的各字段名
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
            defaultToolbar: []
            , cols: [[ //表头
                {
                    field: 'id',
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
                , {field: 'system', title: '系统名称', width: 120, align: 'center'}
                , {field: 'interface', title: '接口名称', width: 260, align: 'center'}
                , {field: 'status', title: '状态码', width: 100, align: 'center'}
                , {field: 'response', title: '返回数据', minWidth: 200, align: 'center'}
                , {field: 'create_at', title: '创建时间', width: 180, align: 'center'}
            ]]
        });


    });
    exports('basesetting/interface/getInterfaceList', {});
});




