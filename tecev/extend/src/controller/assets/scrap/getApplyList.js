layui.define(function(exports){
    //判断搜索建议的位置
    position = '';
    var getApplyListObj = $("#LAY-Assets-Scrap-getApplyList");
    if (Math.floor(getApplyListObj.find(".layui-form-item").width()/getApplyListObj.find(".layui-inline").width()) == 3){
        position = 1;
    }else {
        position = '';
    }
    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug','formSelects'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, suggest = layui.suggest,formSelects = layui.formSelects, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        //渲染所有多选下拉
        formSelects.render('scrap_apply_department', selectParams(1));
        formSelects.btns('scrap_apply_department', selectParams(2), selectParams(3));

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#ApplyLists'
            //,height: '600'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            ,title: '报废申请列表'
            , height: 'full-100' //高度最大化减去差值
            ,url: getApplyList //数据接口
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
            ,page: {
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    field: 'assid',
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
                    width: 160,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    width: 160,
                    align: 'center'
                }
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 140, align: 'center'}
                , {field: 'unit',hide: get_now_cookie(userid+cookie_url+'/unit')=='false'?true:false, title: '单位', width: 80, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 160, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '设备分类', width: 180, align: 'center'}
                , {field: 'storage_date',hide: get_now_cookie(userid+cookie_url+'/storage_date')=='false'?true:false, title: '入库日期', width: 110, sort: true, align: 'center'}
                , {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', width: 110, sort: true, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '原值（元）', width: 110, sort: true, align: 'center'}
                , {field: 'expected_life',hide: get_now_cookie(userid+cookie_url+'/expected_life')=='false'?true:false, title: '使用年限(预计)', width: 130, sort: true, align: 'center'}
                , {field: 'old',hide: get_now_cookie(userid+cookie_url+'/old')=='false'?true:false, title: '折旧状态', width: 100, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    minWidth: 210,
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    align: 'left'
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
        table.on('tool(ApplyLists)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            if(layEvent === 'apply'){
                //报废申请
                top.layer.open({
                    id: 'applys',
                    type: 2,
                    title: '【' + rows.assets + '】报废申请表单 ▼',
                    area: ['700px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?assid='+rows.assid],
                    end: function () {
                        if(flag){
                            table.reload('ApplyLists', {
                                url: getApplyList
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
            }else if(layEvent === 'over'){
                //结束进程
                var params = {};
                    params.type = 'end';
                    params.scrid = $(this).attr('data-id');
                layer.confirm('本次申请报废审批不通过，确认结束该进程吗？', {icon: 3, title: '结束进程'}, function () {
                        $.ajax({
                            timeout: 5000,
                            dataType: "json",
                            type:"POST",
                            url:url,
                            data:params,
                            async:false,
                            beforeSend:function(){
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success:function(data){
                                if(data.status == 1){
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('ApplyLists', {
                                            url: getApplyList
                                            ,where: gloabOptions
                                            ,page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                }else{
                                    layer.msg(data.msg,{icon : 2,time:3000});
                                }
                            },
                            //调用出错执行的函数
                            error: function(){
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 2});
                            },
                            complete:function () {
                                layer.closeAll('loading');
                            }
                        });
                    });
            }else if(layEvent === 'add'){
                //报废申请
                top.layer.open({
                    id: 'applys',
                    type: 2,
                    title: '【' + rows.assets + '】报废申请表单 ▼',
                    area: ['1000px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url],
                    end: function () {
                        if(flag){
                            table.reload('ApplyLists', {
                                url: getApplyList
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
            }else if(layEvent === 'showScrap'){
                //显示报废明细
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】报废明细',
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['1000px', '100%'],
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?action=showScrap&assid='+rows.assid]
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
        table.on('sort(ApplyLists)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('ApplyLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(ApplyListSearch)', function(data){
            gloabOptions = data.field;
            if (gloabOptions.buy_priceMin) {
                if (gloabOptions.buy_priceMax == ''){
                    layer.msg('请补充设备原值区间最大值', {icon: 2});
                    return false;
                }
            }
            if (gloabOptions.buy_priceMax) {
                if (gloabOptions.buy_priceMin == ''){
                    layer.msg('请补充设备原值区间最小值', {icon: 2});
                    return false;
                }
            }
            var table = layui.table;
            table.reload('ApplyLists', {
                url: getApplyList
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
        $("#getApplyListAssets").bsSuggest(
            returnAssets('assets','assets')
        );

        //分类搜索建议

        $("#getApplyListCategory").bsSuggest(
            returnCategory('',position)
        );

        //设备编号搜索建议

        $("#getApplyListAssetsNum").bsSuggest(
            returnAssets('assets','assnum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="getApplyListAssets"]').val(data.assets);
        });

    });
    exports('assets/scrap/getApplyList', {});
});



