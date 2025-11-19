layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#assetsSearchLists'
            //,height: '600'
            ,limits:[5,10,20,50]
            ,loading:true
            ,limit: 10
            ,url: admin_name+'/Lookup/getAssetsList.html' //数据接口
            ,where: {
                sort: 'assid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                ,type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            }
            //,page: true //开启分页
            ,cols: [[ //表头
                {type: 'checkbox', fixed: 'left'},
                {
                    field: 'assid',
                    title: '序号',
                    width: 50,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'assnum',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备编号',
                    width: 180,
                    align: 'center'
                },
                {
                    field: 'assets',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备名称',
                    width: 200,
                    align: 'center'
                },
                {field: 'model', title: '规格 / 型号', width: 220, align: 'center'},
                {field: 'category', title: '设备分类', width: 300, align: 'center'},
                {field: 'department', title: '使用科室', minWidth: 180, align: 'center'}
            ]]
        });

        //搜索按钮
        form.on('submit(sbtn)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('assetsSearchLists', {
                url: admin_name+'/Lookup/getAssetsList.html'
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //搜索按钮
        form.on('submit(printAssetsTemp)', function(data){
            var checkStatus = table.checkStatus('assetsSearchLists');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要打印模板的设备！',{icon : 2,time:1000});
                return false;
            }
            var assid = '';
            for(j = 0,len=data.length; j < len; j++) {
                assid += data[j]['assid']+',';
            }
            var params = {};
            params.type = 'batchPrint';
            params.assid = assid;
            params.temp = $('input[name="temp"]').val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Quality/presetQualityItem.html',
                data: params,
                dataType: "html",
                async:false,
                beforeSend:beforeSend,
                success: function (data) {
                    $('#main-content').html(data);
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            $('#printTem').show();
            $('#printTem').printArea();
            $('#printTem').hide();
            return false;
        });

        //设备名称搜索建议
        $("#getsearchAssets").bsSuggest(
            returnAssets('assets_info','assets')
        );

        //科室搜索建议
        $("#getAssetsListDepartment").bsSuggest(
            returnDepartment()
        );

        //分类搜索建议
        $("#getsearchCate").bsSuggest(
            returnCategory('',1)
        );

        //$("#getAssetsListCategory").click(function(e){
        //    console.log(e);
        //})

        //设备编号搜索建议
        $("#getsearchAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='getAssetsListAssets']").val(data.assets);
            $("input[name='getAssetsListAssorignum']").val(data.assorignum);
        })
    });
    exports('controller/qualities/quality/searchAssets', {});
});




