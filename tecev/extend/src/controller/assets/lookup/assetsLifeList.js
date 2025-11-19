layui.define(function(exports){
    //判断搜索建议的位置
    position = '';
    if (Math.floor($("#LAY-Assets-Lookup-assetsLifeList .layui-form-item").width()/$("#LAY-Assets-Lookup-assetsLifeList .layui-inline").width()) == 3){
        position = '';
    }else {
        position = 1;
    }
    layui.use(['layer', 'form', 'laydate', 'table', 'suggest', 'formSelects', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, formSelects = layui.formSelects, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        //渲染所有多选下拉
        formSelects.render('assetsLifeListDepartment', selectParams(1));
        formSelects.btns('assetsLifeListDepartment', selectParams(2), selectParams(3));

        layer.config(layerParmas());
        //录入时间元素渲染
        laydate.render(dateConfig('#assetsLifeListAddDate'));
        //先更新页面部分需要提前渲染的控件
        form.render();
        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#assetsLifeList'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            ,title: '设备生命历程'
            ,url: assetsLifeList //数据接口
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
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            ,cols: [ //表头
                header
            ], done: function (res, curr) {
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
        //操作栏按钮
        table.on('tool(assetsLifeList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'showLife'://显示主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】设备生命历程信息',
                        area: ['1000px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?action=showLife&assid='+rows.assid]
                    });
                    break;
            }
        });


        //列排序
        table.on('sort(assetsLifeList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('assetsLifeList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });
        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
            var key=data.elem.name;
            var status=data.elem.checked;
            console.log(userid+cookie_url+'/'+key+'='+status);
            document.cookie=userid+cookie_url+'/'+key+'='+status+"; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }
           // 
        });
        //搜索按钮
        form.on('submit(assetsLifeListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('assetsLifeList', {
                url: assetsLifeList
                ,where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                ,page: {
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

        //设备名称搜索建议
        $("#assetsLifeListAssets").bsSuggest(
            returnAssets()
        );

        //分类搜索建议
        $("#assetsLifeListCategory").bsSuggest(
            returnCategory('',position)
        );

        //设备原编号搜索建议
        $("#assetsLifeListAssorignum").bsSuggest(
            returnAssets('assets','assorignum')
        );

        //设备编号搜索建议
        $("#assetsLifeListAssnum").bsSuggest(
            returnAssnum()
        );
    });
    exports('assets/lookup/assetsLifeList', {});
});



