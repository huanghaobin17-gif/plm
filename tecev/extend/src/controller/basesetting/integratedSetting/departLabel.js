layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'form', 'treeGrid', 'admin', 'formSelects'], function () {
        layer.config(layerParmas());
        var treeGrid = layui.treeGrid, admin = layui.admin, formSelects = layui.formSelects, form = layui.form, table = layui.table;
        //渲染所有多选下拉
        formSelects.render('departLabelList', selectParams(1));
        formSelects.btns('departLabelList', selectParams(2), selectParams(3));
        ptable = treeGrid.render({
            elem: '#departLabelList'
            , url: departLabelLists
            , cellMinWidth: 10
            , idField: 'departid'//必須字段
            , treeId: 'departid'//树形id字段名称
            , treeUpId: 'parentid'//树形父id字段名称
            , treeShowName: 'department'//以树形式显示的字段
            , height: 'full-200' //高度最大化减去差值
            //, height: tableHeight
            // , limits: [20, 50, 100, 200, 500]
            // , limit: 20
            , isFilter: false
            // , isPage: true
            , iconOpen: true//是否显示图标【默认显示】
            , isOpenDefault: false//节点默认是展开还是折叠【默认展开】
            , loading: true
            , method: 'POST'
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            },
            toolbar: '#LAY-BaseSetting-IntegratedSetting-departLabelToolbar',
            defaultToolbar: ['filter', 'exports']
            , cols: [[
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
                {field: 'department', minWidth: 160, title: '科室名称', align: 'left'}
                , {field: 'departnum', width: 140, title: '科室编码', align: 'center'}
                , {field: 'label_1', title: '标签1', width: 120, align: 'center'}
                , {field: 'label_2', title: '标签2', width: 120, align: 'center'}
                , {field: 'label_3', title: '标签3', width: 120, align: 'center'}
                , {field: 'label_4', title: '标签4', width: 120, align: 'center'}
                , {field: 'depart_operation', width: 120, title: '操作', fixed: 'right', align: 'center'}
            ]]
        });

        //搜索按钮
        form.on('submit(departLabelListSearch)', function (data) {
            gloabOptions = data.field;
            treeGrid.reload('departLabelList', {
                url: departLabelLists
                , height: 'full-100' //高度最大化减去差值
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , where: gloabOptions
            });
            return false;
        });

        //监听工具条
        treeGrid.on('tool(departLabelData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var departid = rows.departid;
            switch (layEvent) {
                case 'print':
                    print_scan(departid);
                    break;
            }
        });

        //批量打印
        $("#batchPrint").on('click', function () {
            var checkStatus = treeGrid.checkStatus('departLabelList')
            var length = checkStatus.data.length;
            if (length == 0) {
                top.layer.msg('请选择要批量打印的科室', {icon: 2});
                return false;
            }
            var departid = '';
            for (var i = 0; i < length; i++) {
                var tmpId = checkStatus.data[i]['departid'];
                departid += tmpId + ',';
            }
            departid = departid.substring(0, departid.length - 1);
            print_scan(departid);
        });
        function print_scan(departid){
            console.log(departid);
            //获取字段数据
            var params = {};
            params.action = 'batchPrint';
            params.departid = departid;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: departLabelLists,
                data: params,
                dataType: "html",
                async:false,
                beforeSend:beforeSend,
                success: function (data) {
                    $('#print_depart').html('');
                    $('#print_depart').append(data);
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            $('#print_depart').show();
            $('#print_depart').printArea();
            $('#print_depart').hide();
        }
    });
    exports('basesetting/integratedSetting/departLabel', {});
});
