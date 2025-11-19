/**
 * Created by tcdahe on 2018/4/13.
 */
position = '';
if (Math.floor($("#LAY-Qualities-Quality-addQuality .layui-form-item").width()/$("#LAY-Assets-Lookup-getAssetsList .layui-inline").width()) == 3){
    position = '';
}else {
    position = 1;
}
layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'suggest', 'tablePlug','formSelects'], function () {
        var layer = layui.layer, form = layui.form, element = layui.element,formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;
        //先更新页面部分需要提前渲染的控件
        form.render();
        suggest.search();
        //渲染所有多选下拉
        formSelects.render('department', selectParams(1));
        formSelects.btns('department', selectParams(2), selectParams(3));
        //自定义验证规则
        form.verify({
            planName: function(value, item){
                value = $.trim(value);
                if (value){
                    if(/(^\_)|(\__)|(\_+$)/.test(value)){
                        return '所填项首尾不能出现下划线\'_\'';
                    }
                    if(/^\d+\d+\d$/.test(value)){
                        return '所填项不能全为数字';
                    }
                }else{
                    return '请输入质控计划名称！';
                }
            }
        });
        //定义一个全局空对象
        var gloabOptions = {};

        var QAssetsLists = [
            {
                type: 'checkbox',
                fixed: 'left'
            },
            {
                field: 'assid',
                title: '序号',
                width: 50,
                fixed: 'left',
                align: 'center',
                type: 'space',
                templet: function (d) {
                    return d.LAY_INDEX;
                }
            },
            {
                field: 'assnum',
                fixed: 'left',
                title: '设备编号',
                width: 130,
                align: 'center'
            },
            {
                field: 'assets',
                fixed: 'left',
                title: '设备名称',
                width: 150,
                align: 'center'
            },
            {
                field: 'is_qualityAssets',
                title: '质控设备',
                width: 80,
                align: 'center',
                templet: function (d) {
                    return d.is_qualityAssets == 1 ? '<span style="color:#FF5722">是</span>' : '否';
                }
            },
            {
                field: 'model',
                title: '规格 / 型号',
                width: 120,
                align: 'center'
            },
            {
                field: 'department',
                title: '使用科室',
                width: 150,
                align: 'center'
            },
            {
                field: 'category',
                title: '设备分类',
                width: 150,
                align: 'center'
            },
            {
                field: 'helpcat',
                title: '辅助分类',
                width: 100,
                align: 'center'
            },
            {
                field: 'opendate',
                title: '启用日期',
                width: 125,
                align: 'center'
            },
            {
                field: 'test_user',
                title: '上次检测人',
                width: 100,
                align: 'center'
            },
            {
                field: 'test_date',
                title: '上次质控日期',
                sort:true,
                width: 125,
                align: 'center'
            },
            {
                field: 'test_result',
                title: '上次检测结果',
                width: 110,
                align: 'center'
            },
            {
                field: 'operation',
                fixed: 'right',
                title: '操作',
                width: 130,
                align: 'center'
            }
        ];

        table.render({
            elem: '#QAssetsLists'
            ,size:'sm'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: addQuality //数据接口
            , where: {
                sort: 'assid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
            }
            //,page: true //开启分页
            , cols: [ //表头
                QAssetsLists
            ]
        });

        var InAssetsList = [
            {
                type: 'checkbox',
                fixed: 'left'
            },
            {
                field: 'assid',
                title: '序号',
                width: 50,
                fixed: 'left',
                align: 'center',
                type: 'space',
                templet: function (d) {
                    return d.LAY_INDEX;
                }
            },
            {
                field: 'assnum',
                fixed: 'left',
                title: '设备编号',
                width: 130,
                align: 'center'
            },
            {
                field: 'assets',
                fixed: 'left',
                title: '设备名称',
                width: 150,
                align: 'center'
            },
            {
                field:'executors',
                title:'计划检测人',
                fixed: 'left',
                width: 100,
                align:'center'
            },
            {
                field: 'is_qualityAssets',
                title: '质控设备',
                width: 80,
                align: 'center',
                templet: function (d) {
                    return (d.is_qualityAssets == 1) ? '<span style="color:#FF5722">是</span>' : '否';
                }
            },
            {
                field: 'model',
                title: '规格 / 型号',
                width: 120,
                align: 'center'
            },
            {
                field: 'department',
                title: '使用科室',
                width: 150,
                align: 'center'
            },
            {
                field: 'category',
                title: '设备分类',
                width: 150,
                align: 'center'
            },
            {
                field: 'helpcat',
                title: '辅助分类',
                width: 100,
                align: 'center'
            },
            {
                field: 'opendate',
                title: '启用日期',
                width: 125,
                align: 'center'
            },
            {
                field: 'test_user',
                title: '上次检测人',
                width: 100,
                align: 'center'
            },
            {
                field: 'test_date',
                title: '上次质控日期',
                sort:true,
                width: 135,
                align: 'center'
            },
            {
                field: 'test_result',
                title: '上次检测结果',
                width: 110,
                align: 'center'
            },
            {
                field: 'operation',
                fixed: 'right',
                title: '操作',
                width: 100,
                align: 'center'
            }
        ];
        var assids = '';
        table.render({
            elem: '#InAssetsList'
            ,size:'sm'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: addQuality //数据接口
            , where: {
                sort: 'assid'
                , order: 'desc'
                ,type : 'getJoinAssets'
                ,assids : assids
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
            }
            //,page: true //开启分页
            , cols: [ //表头
                InAssetsList
            ]
        });

        var quality = 1;
        //质控设备
        form.on('checkbox(quality)', function(data){
            if(data.elem.checked){
                quality = 1;
            }else{
                quality = 0;
            }
            form.render('checkbox');
        });
        //搜索按钮
        form.on('submit(qualitySearch)', function(data){
            gloabOptions = data.field;
            gloabOptions.quality = quality;
            var table = layui.table;
            table.reload('QAssetsLists', {
                url: addQuality
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        var existsHos = new Array();
        //操作栏按钮
        table.on('tool(QAssetsData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if (layEvent === 'join'){
                //纳入
                if(existsHos.length == 0){
                    existsHos.push(rows.hospital_id);
                }else{
                    if($.inArray(rows.hospital_id,existsHos) < 0){
                        layer.msg('请选择同一个医院的设备！',{icon : 2,time:2000});
                        return false;
                    }
                }
                assids += rows.assid+',';
                table.reload('InAssetsList', {
                    initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                    ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                        assids: assids
                    }
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                gloabOptions.assids = assids;
                table.reload('QAssetsLists', {
                    url: addQuality
                    ,where: gloabOptions
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                //获取对应医院的执行工程师
                var hosid = existsHos[0];
                var cathtml = getExecutors(hosid);
                $('select[name="executors"]').html('');
                $('select[name="executors"]').html(cathtml);
                form.render(); 
            }else if (layEvent === 'showAssets'){
                //显示主设备详情
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】设备详情信息',
                    area: ['1050px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?assid='+rows.assid]
                });
            }
        });

        //操作栏按钮
        table.on('tool(InAssetsData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            if (layEvent === 'removeAssets'){
                //移除
                assids = assids.replace(rows.assid+',', "");
                if(!assids){
                    existsHos = [];
                }
                table.reload('InAssetsList', {
                    initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                    ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                        assids: assids
                    }
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                gloabOptions.assids = assids;
                table.reload('QAssetsLists', {
                    url: addQuality
                    ,where: gloabOptions
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
            }else if (layEvent === 'showAssets'){
                //显示主设备详情
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】设备详情信息',
                    area: ['75%', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [admin_name+'/Lookup/showAssets.html'+'?assid='+rows.assid]
                });
            }
        });
        //列排序
        table.on('sort(assetsLists)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('assetsLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        $('#batchJoin').on('click',function () {
            var checkStatus = table.checkStatus('QAssetsLists');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要纳入的设备！',{icon : 2,time:1000});
                return false;
            }
            for(j = 0,len=data.length; j < len; j++) {
                if(existsHos.length == 0){
                    existsHos.push(data[j]['hospital_id']);
                }else{
                    if($.inArray(data[j]['hospital_id'],existsHos) < 0){
                        layer.msg('请选择同一个医院的设备！',{icon : 2,time:2000});
                        return false;
                    }
                }
            }
            for(j = 0,len=data.length; j < len; j++) {
                assids += data[j]['assid']+',';
            }
            table.reload('InAssetsList', {
                where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    assids: assids
                }
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            gloabOptions.assids = assids;
            table.reload('QAssetsLists', {
                url: addQuality
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            //获取对应医院的执行工程师
            var hosid = existsHos[0];
            var cathtml = getExecutors(hosid);
            $('select[name="executors"]').html('');
            $('select[name="executors"]').html(cathtml);
            form.render();
        });

        //分配执行人
        $('#share').on('click',function () {
            var executors = $('select[name="executors"] option:selected').val();
            if(!executors){
                layer.msg('请选择检测人！',{icon : 2,time:1000});
                return false;
            }
            var checkStatus = table.checkStatus('InAssetsList');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要分配的设备！',{icon : 2,time:1000});
                return false;
            }
            var target =  $('#LAY-Qualities-Quality-addQuality .layui-table-fixed-l:last').find('.layui-table-body').find('tr');
            $.each(data,function (index,item) {
                var selassid = item.assid;
                $.each(target,function (index1,item1) {
                    var td = $(this).find('td');
                    $.each(td,function () {
                        var cid = $(this).attr('data-content');
                        if(cid == selassid){
                            inputtarget = $(this).parent().find('td:first').find("input[type='checkbox']");
                            if(inputtarget.is(':checked') && inputtarget.attr('disabled') != 'disabled'){
                                $(this).parent().find('td:last').find('div').html(executors);
                                inputtarget.attr('disabled',true);
                                form.render();
                            }
                        }
                    });
                });
            });
            return false;
        });
        //生成质控计划
        $('#save').on('click',function () {
            var target =  $('#LAY-Qualities-Quality-addQuality .layui-table-fixed-l:last').find('.layui-table-body').find('tr');
            if(target.length == 0){
                layer.msg('请先纳入设备！',{icon : 2,time:1000});
                return false;
            }
            var flag = true;
            var username = [];
            $.each(target,function (index1,item1) {
                var executor = $(this).find('td:last').find('div').html();
                if(!executor){
                    flag = false;
                }else{
                    if($.inArray(executor,username) == -1){
                        username.push(executor);
                    }
                }
            });
            if(!flag){
                layer.msg('请对每个设备分配检测人！',{icon : 2,time:1000});
                return false;
            }
            var params = {};
            params.type = 'add';
            params.hospital_id = existsHos[0];
            params.planName = $.trim($('input[name="planName"]').val());
            params.planRemark = $.trim($('input[name="planRemark"]').val());
            $.each(username,function (index,item) {
                var assid = '';
                var target2 =  $('#LAY-Qualities-Quality-addQuality .layui-table-fixed-l:last').find('.layui-table-body').find('tr');
                $.each(target2,function () {
                    var ex = $(this).find('td:last').find('div').html();
                    if(item == ex){
                        assid += $(this).find('td').eq(1).attr('data-content')+',';
                    }
                });
                params[item] = assid;
            });
            if(!params.planName){
                layer.msg('请输入计划名称！',{icon : 2,time:1000});
                return false;
            }
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name + '/Quality/addQuality.html',
                data: params,
                dataType: "json",
                beforeSend:beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg,{icon : 1,time:1000},function () {
                            parent.location.reload();
                        });
                    }else{
                        layer.msg(data.msg,{icon : 2,time:2500});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2});
                },
                complete:complete
            });
            return false;
        });

        //设备名称搜索建议
        $("#getAssetsAddPlan").bsSuggest(
            returnAssets('assets_info','assets')
        );
//分类搜索建议
        $("#getAssetsCategory-addplan").bsSuggest(
            returnCategory('',position)
        );

//科室搜索建议
        $("#getDepartmentLists-addplan").bsSuggest(
            returnDepartment()
        );
    });
    exports('qualities/quality/addQuality', {});
});


function getExecutors(hosid) {
    var html = '<option value=""></option>';
    $.ajax({
        timeout: 5000,
        type: "POST",
        url: admin_name+'/Public/getQualitesExecutor.html?hospital_id='+hosid,
        dataType: "json",
        async:false,
        success: function (data) {
            if (data.value.length > 0) {
                $.each(data.value,function (i,item) {
                    html += '<option value="'+item.username+'">'+item.username+'</option>';
                });
            }else{
                layer.msg('未设置质控计划检测人，请先设置检测人再创建计划！',{icon : 2},1000);
            }
        }
    });
    return html;
}
