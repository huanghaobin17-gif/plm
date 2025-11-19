layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug','formSelects'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, suggest = layui.suggest,formSelects = layui.formSelects, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();
        //渲染所有多选下拉
        formSelects.render('scrap_exam_department', selectParams(1));
        formSelects.btns('scrap_exam_department', selectParams(2), selectParams(3));

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#ExamineList'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            ,title: '报废审核列表'
            , height: 'full-100' //高度最大化减去差值
            ,url: getExamineList //数据接口
            ,where: {
                sort: 'scrid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'scrid' //排序字段，对应 cols 设定的各字段名
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
            ,page: {
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    field: 'scrid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }, {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    title: '设备编号',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }
                , {field: 'scrapnum',hide: get_now_cookie(userid+cookie_url+'/scrapnum')=='false'?true:false, title: '报废单号', width: 180, align: 'center'}
                , {field: 'scrapdate',hide: get_now_cookie(userid+cookie_url+'/scrapdate')=='false'?true:false, title: '申请日期', width: 110, align: 'center'}
                , {field: 'apply_user',hide: get_now_cookie(userid+cookie_url+'/apply_user')=='false'?true:false, title: '申请人', width: 120, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 120, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '设备分类', width: 180, align: 'center'}
                , {field: 'expected_life',hide: get_now_cookie(userid+cookie_url+'/expected_life')=='false'?true:false, title: '使用年限', width: 100, sort: true, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '原值(元)', width: 130, sort: true, align: 'center'}
                , {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', width: 110, sort: true, align: 'center'}
                , {field: 'scrap_reason',hide: get_now_cookie(userid+cookie_url+'/scrap_reason')=='false'?true:false, title: '报废原因', width: 300, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    minWidth: 140,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
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
        //操作栏按钮
        table.on('tool(ExamineList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            if(layEvent === 'approval'){
                //报废审批
                top.layer.open({
                    id: 'approvals',
                    type: 2,
                    title: '【' + rows.assets + '】报废审批表单 ▼',
                    area: ['750px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?assid='+rows.assid+'&scrid='+rows.scrid],
                    end: function () {
                        if(flag){
                            table.reload('ExamineList', {
                                url: getExamineList
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
            }else if(layEvent === 'showAssets'){
                //显示设备详情
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】设备详情',
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['1050px', '100%'],
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?assid='+rows.assid]
                });
            }
        });

        //列排序
        table.on('sort(ExamineList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('ExamineList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(ExamineListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('ExamineList', {
                url: getExamineList
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
        $("#getExamineListAssets").bsSuggest(
            returnAssets('scrap','assets')
        );

        //分类搜索建议

        $("#getExamineListCategory").bsSuggest(
            returnCategory('',1)
        );

        //设备编号搜索建议

        $("#getExamineListAssetsNum").bsSuggest(
            returnAssets('scrap','assnum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="getExamineListAssets"]').val(data.assets);
        });

    });
    exports('assets/scrap/getExamineList', {});
});



