layui.define(function(exports){
    //判断搜索建议的位置
    position = '';
    var getScrapListObj = $("#LAY-Assets-Scrap-getScrapList");
    if (Math.floor(getScrapListObj.find(".layui-form-item").width()/getScrapListObj.find(".layui-inline").width()) == 3){
        position = 1;
    }else {
        position = '';
    }
    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug','formSelects'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, suggest = layui.suggest,formSelects = layui.formSelects, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();
        //渲染所有多选下拉
        formSelects.render('scrap_list_department', selectParams(1));
        formSelects.btns('scrap_list_department', selectParams(2), selectParams(3));

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#ScrapLists'
            //,height: '600'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            ,title: '报废查询列表'
            , height: 'full-100' //高度最大化减去差值
            ,url: getScrapList //数据接口
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
                }, {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }, {
                    field: 'approve_status',
                    hide: get_now_cookie(userid+cookie_url+'/approve_status')=='false'?true:false,
                    title: '报废状态',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    width: 100,
                    align:'center',
                    templet: function(d){
                        return d.approve_status == 1 ? '<span style="color: red;">已报废</span>' : (d.approve_status == 2 ? '<span style="color: green;">不通过</span>' : (d.approve_status == -1) ? '<span style="color: red;">已报废</span>' : '<span style="color:#FFB800;">待审核</span>');
                    }
                }, {field: 'scrapnum',hide: get_now_cookie(userid+cookie_url+'/scrapnum')=='false'?true:false, title: '报废单号', width: 180, align: 'center'}
                , {field: 'scrapdate',hide: get_now_cookie(userid+cookie_url+'/scrapdate')=='false'?true:false, title: '申请日期', width: 110, sort: true, align: 'center'}
                , {field: 'apply_user',hide: get_now_cookie(userid+cookie_url+'/apply_user')=='false'?true:false, title: '申请人', width: 110, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 160, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '使用分类', width: 180, align: 'center'}
                , {field: 'expected_life',hide: get_now_cookie(userid+cookie_url+'/expected_life')=='false'?true:false, title: '使用年限', width: 100, sort: true, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '原值(元)', width: 130, sort: true, align: 'center'}
                , {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', width: 110, sort: true, align: 'center'}
                , {field: 'scrap_reason',hide: get_now_cookie(userid+cookie_url+'/scrap_reason')=='false'?true:false, title: '报废原因', width: 300, align: 'center'}
                , {field: 'clear_cross_user',hide: get_now_cookie(userid+cookie_url+'/clear_cross_user')=='false'?true:false, title: '处置经手人', width: 100, align: 'center'}
                , {field: 'cleardate',hide: get_now_cookie(userid+cookie_url+'/cleardate')=='false'?true:false, title: '处置时间', width: 110, align: 'center'}
                , {field: 'clear_company',hide: get_now_cookie(userid+cookie_url+'/clear_company')=='false'?true:false, title: '处置公司', width: 150, align: 'center'}
                , {field: 'clear_contacter',hide: get_now_cookie(userid+cookie_url+'/clear_contacter')=='false'?true:false, title: '联系人', width: 100, align: 'center'}
                , {field: 'clear_telephone',hide: get_now_cookie(userid+cookie_url+'/clear_telephone')=='false'?true:false, title: '联系电话', width: 150, align: 'center'}
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 180,
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


        //列排序
        table.on('sort(ScrapLists)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('ScrapLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(ScrapListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('ScrapLists', {
                url: getScrapList
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

        //操作栏按钮
        table.on('tool(ScrapLists)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if(layEvent === 'showAssets'){
                //显示主设备详情
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
            }else if(layEvent === 'showScrap'){
                //显示报废明细
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】报废明细',
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['700px', '100%'],
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?action=showScrap&assid='+rows.assid]
                });
            }
        });

        //设备名称搜索建议
        $("#getScrapListAssets").bsSuggest(
            returnAssets('scrap','assets')
        );

        //分类搜索建议

        $("#getScrapListCategory").bsSuggest(
            returnCategory('',position)
        );

        //设备编号搜索建议

        $("#getScrapListAssetsNum").bsSuggest(
            returnAssets('scrap','assnum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="getScrapListAssets"]').val(data.assets);
        });
    });
    exports('assets/scrap/getScrapList', {});
});

