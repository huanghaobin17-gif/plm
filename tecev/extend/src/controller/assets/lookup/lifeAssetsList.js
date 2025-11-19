layui.define(function(exports){
    //判断搜索建议的位置
    position = '';
    lookupLifeAssetsListObj = $("#LAY-Assets-Lookup-lifeAssetsList");
    if (Math.floor(lookupLifeAssetsListObj.find(".layui-form-item").width()/lookupLifeAssetsListObj.find(".layui-inline").width()) == 3){
        position = '';
    }else {
        position = 1;
    }
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'upload', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, upload = layui.upload, tablePlug = layui.tablePlug;

        //渲染所有多选下拉
        formSelects.render('lifeAssetsListDepartment', selectParams(1));
        formSelects.btns('lifeAssetsListDepartment', selectParams(2), selectParams(3));

        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //录入时间元素渲染
        laydate.render(dateConfig('#lifeAssetsListAdddate'));

        //定义一个全局空对象
        var gloabOptions = {};
        var tablens = table.render({
            elem: '#lifeAssetsList'
            //,height: '600'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            ,title: '生命支持类设备列表'
            ,url: lifeAssetsList //数据接口
            , height: 'full-100' //高度最大化减去差值
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
            , cols: [[ //表头
                {
                    field: 'assid',
                    fixed: 'left',
                    title: '序号',
                    width: 65,
                    align: 'center',
                    style: 'background-color: #f9f9f9;',
                    templet: function(d){
                        return d.LAY_INDEX
                    }
                }
                , {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    fixed: 'left',
                    title: '设备编号',
                    width: 150,
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                }
                , {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    fixed: 'left',
                    title: '设备名称',
                    width: 150,
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                }
                , {field: 'assorignum',hide: get_now_cookie(userid+cookie_url+'/assorignum')=='false'?true:false, title: '设备原编码', width: 140, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '设备分类', width: 150, align: 'center'}
                , {field: 'assets_level_name',hide: get_now_cookie(userid+cookie_url+'/assets_level_name')=='false'?true:false, title: '管理类别', width: 150, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 150, align: 'center'}
                , {field: 'departperson',hide: get_now_cookie(userid+cookie_url+'/departperson')=='false'?true:false, title: '科室负责人', width: 100, align: 'center'}
                , {field: 'address',hide: get_now_cookie(userid+cookie_url+'/address')=='false'?true:false, title: '存放地点', width: 120, align: 'center'}
                , {field: 'managedepart',hide: get_now_cookie(userid+cookie_url+'/managedepart')=='false'?true:false, title: '管理科室', width: 120, align: 'center'}
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格型号', width: 140, align: 'center'}
                , {field: 'brand',hide: get_now_cookie(userid+cookie_url+'/brand')=='false'?true:false, title: '品牌', width: 140, align: 'center'}
                , {
                    field: 'status_name',
                    title: '设备当前状态',
                    hide: get_now_cookie(userid+cookie_url+'/status_name')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if(d.status != 2){
                            switch (d.status){
                                case 0:
                                    return d.status_name;
                                    break;
                                case 1:
                                    return '<span style="color: #FFB800;">'+d.status_name+'</span>';
                                    break;
                                case 5:
                                    return '<span style="color: #FFB800;">'+d.status_name+'</span>';
                                    break;
                                case 6:
                                    return '<span style="color: #FFB800;">'+d.status_name+'</span>';
                                    break;
                            }
                        }else{
                            return '<span style="color: #FF5722;">'+d.status_name+'</span>';
                        }
                    }
                }
                , {field: 'serialnum',hide: get_now_cookie(userid+cookie_url+'/serialnum')=='false'?true:false, title: '设备序列号', width: 140, align: 'center'}
                , {field: 'assetsrespon',hide: get_now_cookie(userid+cookie_url+'/assetsrespon')=='false'?true:false, title: '设备负责人', width: 100, align: 'center'}
                , {field: 'factorynum',hide: get_now_cookie(userid+cookie_url+'/factorynum')=='false'?true:false, title: '出厂编号', width: 140, align: 'center'}
                , {field: 'factorydate',hide: get_now_cookie(userid+cookie_url+'/factorydate')=='false'?true:false, title: '出厂日期', width: 140, sort: true, align: 'center'}
                , {field: 'opendate',hide: get_now_cookie(userid+cookie_url+'/opendate')=='false'?true:false, title: '启用日期', sort: true, width: 140, align: 'center'}
                , {field: 'storage_date',hide: get_now_cookie(userid+cookie_url+'/storage_date')=='false'?true:false, title: '入库日期', width: 140, sort: true, align: 'center'}
                , {field: 'helpcatid',hide: get_now_cookie(userid+cookie_url+'/helpcatid')=='false'?true:false, title: '辅助分类', width: 120, align: 'center'}
                , {field: 'financeid',hide: get_now_cookie(userid+cookie_url+'/financeid')=='false'?true:false, title: '财务分类', width: 120, align: 'center'}
                , {field: 'capitalfrom',hide: get_now_cookie(userid+cookie_url+'/capitalfrom')=='false'?true:false, title: '资金来源', width: 120, align: 'center'}
                , {field: 'assfromid',hide: get_now_cookie(userid+cookie_url+'/assfromid')=='false'?true:false, title: '设备来源', width: 120, align: 'center'}
                , {field: 'invoicenum',hide: get_now_cookie(userid+cookie_url+'/invoicenum')=='false'?true:false, title: '发票编号', width: 140, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '设备原值', sort: true, width: 120, align: 'center'}
                , {field: 'expected_life',hide: get_now_cookie(userid+cookie_url+'/expected_life')=='false'?true:false, title: '预计使用年限', sort: true, width: 120, align: 'center'}
                , {field: 'residual_value',hide: get_now_cookie(userid+cookie_url+'/residual_value')=='false'?true:false, title: '残净值率', sort: true, width: 120, align: 'center'}
                , {
                    field: 'is_firstaid',
                    title: '急救设备',
                    hide: get_now_cookie(userid+cookie_url+'/is_firstaid')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if (d.is_firstaid === '是'){
                            return '<span style="color: #FF5722;">'+d.is_firstaid+'</span>';
                        }else {
                            return d.is_firstaid;
                        }
                    }
                }, {
                    field: 'is_special',
                    title: '特种设备',
                    hide: get_now_cookie(userid+cookie_url+'/is_special')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if (d.is_special === '是'){
                            return '<span style="color: #FF5722;">'+d.is_special+'</span>';
                        }else {
                            return d.is_special;
                        }
                    }
                },{
                    field: 'is_metering',
                    title: '计量设备',
                    hide: get_now_cookie(userid+cookie_url+'/is_metering')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if (d.is_metering === '是'){
                            return '<span style="color: #FF5722;">'+d.is_metering+'</span>';
                        }else {
                            return d.is_metering;
                        }
                    }
                },{
                    field: 'is_qualityAssets',
                    title: '质控设备',
                    hide: get_now_cookie(userid+cookie_url+'/is_qualityAssets')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if (d.is_qualityAssets === '是'){
                            return '<span style="color: #FF5722;">'+d.is_qualityAssets+'</span>';
                        }else {
                            return d.is_qualityAssets;
                        }
                    }
                },{
                    field: 'is_benefit',
                    title: '效益分析设备',
                    hide: get_now_cookie(userid+cookie_url+'/is_benefit')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if (d.is_benefit === '是'){
                            return '<span style="color: #FF5722;">'+d.is_benefit+'</span>';
                        }else {
                            return d.is_benefit;
                        }
                    }
                },{
                    field: 'is_lifesupport',
                    title: '生命支持类设备',
                    hide: get_now_cookie(userid+cookie_url+'/is_lifesupport')=='false'?true:false,
                    width: 120,
                    align: 'center',
                    templet: function(d){
                        if (d.is_lifesupport === '是'){
                            return '<span style="color: #FF5722;">'+d.is_lifesupport+'</span>';
                        }else {
                            return d.is_lifesupport;
                        }
                    }
                }
                , {field: 'guarantee_date',hide: get_now_cookie(userid+cookie_url+'/guarantee_date')=='false'?true:false, title: '保修截止日期', width: 120, sort: true, align: 'center'}
                , {field: 'depreciation_method',hide: get_now_cookie(userid+cookie_url+'/depreciation_method')=='false'?true:false, title: '折旧方式', width: 120, align: 'center'}
                , {field: 'depreciable_lives', hide: get_now_cookie(userid+cookie_url+'/depreciable_lives')=='false'?true:false,title: '折旧年限', width: 120, align: 'center'}
                , {field: 'factory',hide: get_now_cookie(userid+cookie_url+'/factory')=='false'?true:false, title: '生产厂商', width: 240, align: 'center'}
                , {field: 'factory_user',hide: get_now_cookie(userid+cookie_url+'/factory_user')=='false'?true:false, title: '生产厂商联系人', width: 150, align: 'center'}
                , {field: 'factory_tel',hide: get_now_cookie(userid+cookie_url+'/factory_tel')=='false'?true:false, title: '生产厂商联系电话', width: 150, align: 'center'}
                , {field: 'supplier', hide: get_now_cookie(userid+cookie_url+'/supplier')=='false'?true:false,title: '供应商', width: 240, align: 'center'}
                , {field: 'supp_user',hide: get_now_cookie(userid+cookie_url+'/supp_user')=='false'?true:false, title: '供应商联系人', width: 120, align: 'center'}
                , {field: 'supp_tel',hide: get_now_cookie(userid+cookie_url+'/supp_tel')=='false'?true:false, title: '供应商联系电话', width: 120, align: 'center'}
                , {field: 'repair',hide: get_now_cookie(userid+cookie_url+'/repair')=='false'?true:false, title: '维修公司', width: 240, align: 'center'}
                , {field: 'repa_user',hide: get_now_cookie(userid+cookie_url+'/repa_user')=='false'?true:false, title: '维修公司联系人', width: 150, align: 'center'}
                , {field: 'repa_tel',hide: get_now_cookie(userid+cookie_url+'/repa_tel')=='false'?true:false, title: '维修联系电话', width: 150, align: 'center'}
                , {
                    field: 'operation',
                    hide: get_now_cookie(userid + cookie_url + '/operation') == 'false' ? true : false,
                    title: '操作',
                    minWidth: 220,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'center'
                }
            ]],
            done: function (res, curr, count) {
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
                var tableElem = this.elem;
                // table render出来实际显示的table组件
                var tableViewElem = tableElem.next();
                var url = tableViewElem.find('.uploadPics').attr('data-url');
                var tr = $(tableViewElem).find('.layui-table-fixed-r').find('.layui-table-body').find('tr');
                $.each(tr,function(k,v){
                    var assid = $(v).find('.uploadPics').attr('data-assid');
                    // 渲染当前页面的所有的上传设备图片按钮
                    upload.render({
                        elem: $(v).find('.uploadPics')
                        ,url: url
                        ,data: {
                            assid:assid,
                            action:'uploadPic'
                        }
                        ,multiple: true
                        ,allDone: function(obj){
                            //当文件全部被提交后，才触发
                            if (obj.total == obj.successful){
                                layer.msg('上传设备图片成功',{icon : 1},1000);
                                $.ajax({
                                    type: "POST",
                                    url: url,
                                    data: {count:obj.successful,action:'uploadPic',assid:assid},
                                    dataType: "json"
                                });
                                setTimeout(function(){
                                    table.reload('assetsLists', {
                                        url: getAssetsList
                                        ,where: gloabOptions
                                        ,page: {
                                            curr: 1 //重新从第 1 页开始
                                        }
                                    });
                                },2000)
                            }
                        }
                    });
                });
            }
        });
        //操作栏按钮
        table.on('tool(lifeAssetsList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var flag = 1;
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'showAssets'://显示主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】设备详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?assid='+rows.assid]
                    });
                    break;
                case 'editAssets'://编辑主设备详情
                    top.layer.open({
                        id: 'editAssetss',
                        type: 2,
                        title: '编辑设备【'+rows.assets+'】',
                        area: ['1145px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?assid='+rows.assid],
                        end: function () {
                            if(flag){
                                table.reload('lifeAssetsList', {
                                    url: lifeAssetsList
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
                    break;
                case 'deleteAssets'://删除主设备详情
                                    //先判断是否可以删除
                    var params = {};
                    var title = $(this).html() + '【' + rows.assets + '】';
                    params['assid'] = rows.assid;
                    params['type'] = 'is_del';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend: function () {
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success: function (data) {
                            layer.closeAll('loading');
                            if (data.status == 1) {
                                layer.confirm('设备删除后无法恢复，确定删除吗？',{btn: ['确定', '取消'],title:title}, function(index){
                                    var params = {};
                                    params['assid'] = rows.assid;
                                    params['type']='del';
                                    $.ajax({
                                        type: "POST",
                                        url: url,
                                        data: params,
                                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                                        beforeSend: function () {
                                            layer.load(1);
                                        },
                                        //成功返回之后调用的函数
                                        success: function (data) {
                                            layer.closeAll('loading');
                                            if (data.status == 1) {
                                                layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                                    table.reload('assetsLists', {
                                                        url: getAssetsList
                                                        , where: gloabOptions
                                                        , page: {
                                                            curr: 1 //重新从第 1 页开始
                                                        }
                                                    });
                                                });
                                            } else {
                                                layer.msg(data.msg, {icon: 2});
                                            }
                                        },
                                        //调用出错执行的函数
                                        error: function () {
                                            //请求出错处理
                                            layer.msg('服务器繁忙', {icon: 5});
                                        }
                                    });
                                    layer.close(index);

                                });
                            } else {
                                layer.msg(data.msg, {icon: 2});
                            }
                        },
                        //调用出错执行的函数
                        error: function () {
                            //请求出错处理
                            layer.msg('服务器繁忙', {icon: 5});
                        }
                    });
                    break;
                case 'showAssetLabel'://设备便签页，大河写，只为弹窗，开发时需要修改敬请按照开发需要修改。
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】设备标签页',
                        area: ['450px', '380px'],
                        shade: [0.8, '#393D49'],
                        shadeClose:true,
                        anim:5,
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?action=showAssetLabel&assid='+rows.assid]
                    });
                    break;
                case 'increment'://附属设备
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】设备详情信息',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['75%', '100%'],
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?assid='+rows.assid+'&change=5']
                    });
                    break;
                case 'mainAssets'://所属设备
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】所属设备详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')+'?assid='+rows.main_assid]
                    });
                    break;
            }
        });
        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                console.log(userid + cookie_url + '/' + key + '=' + status);
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
            //
        });
        //列排序
        table.on('sort(lifeAssetsList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('lifeAssetsList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(lifeAssetsListsSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('lifeAssetsList', {
                url: lifeAssetsList
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

        //添加设备按钮
        $('#addAssets').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'addAssetss',
                type: 2,
                title: $(this).html(),
                scrollbar:false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['81%', '100%'],
                closeBtn: 1,
                content: [url],
                end: function () {
                    if(flag){
                        table.reload('lifeAssetsList', {
                            url: lifeAssetsList
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


        //批量入库
        $('#batchAddAssets').on('click',function() {
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'batchAddAssetss',
                type: 2,
                title: $(this).html(),
                scrollbar:false,
                area: ['100%', '100%'],
                closeBtn: 1,
                content: [url],
                end: function () {
                    table.reload('lifeAssetsList', {
                        url: lifeAssetsList
                        ,where: gloabOptions
                        ,page: {
                            curr: 1 //重新从第 1 页开始
                        }
                    });
                }
            });
            return false;
        });
        //批量维护设备
        $('#batchEditAssets').on('click',function() {
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'batchEditAssetss',
                type: 2,
                title: $(this).html(),
                scrollbar:false,
                area: ['100%', '100%'],
                closeBtn: 1,
                content: [url],
                end: function () {
                    table.reload('lifeAssetsList', {
                        url: lifeAssetsList
                        ,where: gloabOptions
                        ,page: {
                            curr: 1 //重新从第 1 页开始
                        }
                    });
                }
            });
            return false;
        });

        //选择字段显示
        formSelects.on('lifeAssetsListSelectFields', function(){
            setTimeout(function(){
                var selectFields = formSelects.value('lifeAssetsListSelectFields', 'val');
                var params = {};
                params.showFields = selectFields;
                params.type = 'changeFields';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: lifeAssetsList,
                    data: params,
                    dataType: "json",
                    beforeSend:beforeSend,
                    success: function (data) {
                        if (data.status == 1) {
                            header = data.header;
                            tablens.reload( {
                                url: lifeAssetsList
                                ,where: gloabOptions
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                                ,cols: [ //表头
                                    header
                                ]
                            });
                        }else{
                            layer.msg(data.msg,{icon : 2,time:1000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败",{icon : 2,time:1000});
                    },
                    complete:complete
                });
            },100);
        });
        //批量导出设备
        $('#exportAssets').on('click',function() {
            var url = $(this).attr('data-url');
            var checkStatus = table.checkStatus('lifeAssetsList');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要导出的设备！',{icon : 2,time:1000});
                return false;
            }
            var assid = '';
            var params = {};
            for(j = 0,len=data.length; j < len; j++) {
                assid += data[j]['assid']+',';
            }
            params.assid = assid;
            var fields = '';
            $.each(header,function (index,e) {
                if(e.field && e.field != 'operation' && e.field != 'assid'){
                    fields += e.field+',';
                }
            });
            params.fields = fields;
            postDownLoadFile({
                url:url,
                data:params,
                method:'POST'
            });
        });

        //设备名称搜索建议
        $("#lifeAssetsListAssets").bsSuggest(
            returnAssets()
        );

        //分类搜索建议
        $("#lifeAssetsListCategory").bsSuggest(
            returnCategory('',position)
        );


        //设备编号搜索建议
        $("#lifeAssetsListAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='lifeAssetsListAssets']").val(data.assets);
            $("input[name='lifeAssetsListAssorignum']").val(data.assorignum);
        });

        //设备原编号搜索建议
        $("#lifeAssetsListAssorignum").bsSuggest(
            returnAssets('assets','assorignum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="getListAssetsNum"]').val(data.assnum);
            $('input[name="getListAssets"]').val(data.assets);
        });
    });
    exports('assets/lookup/lifeAssetsList', {});
});



