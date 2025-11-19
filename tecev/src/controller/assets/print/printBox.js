layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'form', 'tablePlug','formSelects'], function () {

        layer.config(layerParmas());

        var table = layui.table, formSelects = layui.formSelects,suggest = layui.suggest, form = layui.form, tablePlug = layui.tablePlug;

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        //初始化搜索建议插件
        suggest.search();
        //渲染所有多选下拉
        formSelects.render('boxListDepartment', selectParams(1));
        formSelects.btns('boxListDepartment', selectParams(2), selectParams(3));
        //第一个实例
        table.render({
            elem: '#printBoxList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 5
            , title: '档案盒标签打印'
            , url: printBox //数据接口
            , where: {
                sort: 'box_id'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'box_id' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Assets-Print-printBoxToolbar',
            defaultToolbar: ['exports']
            , cols: [[ //表头
                {type: 'checkbox', fixed: 'left', style: 'background-color: #f9f9f9;'},
                {
                    field: 'box_id',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'box_num',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '档案盒编号',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/category') == 'false' ? true : false
                }
            ]]
        });

        form.render();
        //监听搜索
        form.on('submit(searchPrintBox)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('printBoxList', {
                url: printBox
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        table.on('toolbar(printBoxData)', function(obj){
            var event =  obj.event,
                url = printBox;
            switch(event){
                case 'printBox'://批量打印标签
                    var checkStatus = table.checkStatus('printBoxList');
                    var data = checkStatus.data;
                    if(data.length == 0){
                        layer.msg('请选择要打印标签的数据！',{icon : 2,time:2000});
                        return false;
                    }
                    var box_id = '';
                    for(j = 0,len=data.length; j < len; j++) {
                        box_id += data[j]['box_id']+',';
                    }
                    //获取字段数据
                    var params = {};
                    params.action = 'batchPrint';
                    params.box_id = box_id;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "html",
                        async:false,
                        beforeSend:beforeSend,
                        success: function (data) {
                            $('#print_box').html('');
                            $('#print_box').append(data);
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2},1000);
                        },
                        complete:complete
                    });
                    $('#print_box').show();
                    $('#print_box').printArea();
                    $('#print_box').hide();
                    break;
            }
        });

        //档案编号
        $("#printbox_num").bsSuggest(
            returnBoxNum()
        );
    });
    exports('assets/print/printBox', {});
});

