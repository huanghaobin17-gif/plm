layui.define(function(exports){
    //记录被纳入的设备
    var removedata = '';
    var oldAssetsList = $("input[name='oldAssetsList']").val();
    if (oldAssetsList) {removedata = oldAssetsList;}
    function getType() {
        var type = '';
        $("input[name='type']:checked").each(function () {
            type += ',' + $(this).val();
        });
        if (type.substr(0, 1) === ',') {
            type = type.substr(1);
        }
        return type;
    }

    layui.use(['form', 'table', 'suggest', 'formSelects', 'tablePlug'], function () {
        var form = layui.form, table = layui.table, formSelects = layui.formSelects, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //使用科室 多选框初始配置
        formSelects.render('addPatrolDepartment', selectParams(1));
        formSelects.btns('addPatrolDepartment', selectParams(2));
        //初始化搜索建议插件
        suggest.search();

        layui.form.render();
        //初始化设备列表
        table.render({
            elem: '#addAssetslist'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , where: {
                removedata: removedata,
                type:getType(),
                action:'addAssetslist'
            }
            , url: admin_name+'/Patrol/addPatrol.html' //数据接口
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            //,page: true //开启分页
            , cols: [[ //表头
                {type: 'checkbox', fixed: 'left'}
                ,{field: 'assid',title: '序号',width: 60,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                , {field: 'assnum', fixed: 'left', title: '设备编号', width: 140, align: 'center'}
                , {field: 'assorignum', title: '设备原编号', width: 140, align: 'center'}
                , {field: 'assets', title: '设备名称', width: 170, align: 'center'}
                , {field: 'model', title: '规格型号', width: 120, align: 'center'}
                , {field: 'department', title: '使用科室', width: 140, align: 'center'}
                , {field: 'status_name', title: '当前状态', width: 90, align: 'center'}
                , {field: 'pre_patrol_date', title: '上一次巡查日期', width: 130, align: 'center'}
                , {field: 'pre_maintain_date', title: '上一次保养日期', width: 130, align: 'center'}
                , {field: 'patrol_xc_cycle', title: '巡查周期(天)', width: 110, align: 'center'}
                , {field: 'patrol_pm_cycle', title: '保养周期(天)', width: 110, align: 'center'}
                , {field: 'guarantee_status', title: '维保状态', width: 100, align: 'center'}
                , {field: 'opendate', title: '启用日期', width: 110, align: 'center'}
                , {field: 'operation', title: '操作', fixed: 'right', width: 80, align: 'center'}
            ]]
            , done: function (res) {
                $('.assetsListTotal').html('&nbsp;&nbsp;&nbsp;查询到的设备列表(' + (res.total ? res.total : 0)  + '台)');

            }
        });

        var existsHos = [];
        //监听设备列表工具条
        table.on('tool(addAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'add':
                    if(existsHos.length === 0){
                        existsHos.push(rows.hospital_id);
                    }else{
                        if($.inArray(rows.hospital_id,existsHos) < 0){
                            layer.msg('请选择同一个医院的设备！',{icon : 2,time:2000});
                            return false;
                        }
                    }
                    removedata += ',' + rows.assnum;
                    if (removedata.substr(0, 1) === ',') {
                        removedata = removedata.substr(1);
                    }
                    table.reload('addAssetslist', {
                        where: {
                            action:'addAssetslist',
                            removedata: removedata
                        }
                    });
                    table.reload('delAssetslist', {
                        where: {
                            action:'delAssetslist',
                            removedata: removedata
                        }
                    });
                    break;
            }
        });

        //初始化已纳入设备列表
        table.render({
            elem: '#delAssetslist'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , where: {
                removedata: removedata,
                action:'delAssetslist'
            }
            , url: admin_name+'/Patrol/addPatrol.html' //数据接口
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
            //,page: true //开启分页
            , cols: [[ //表头
                {field: 'assid',title: '序号',width: 60,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                , {field: 'assnum', fixed: 'left', title: '设备编号', width: 140, align: 'center'}
                , {field: 'assorignum', title: '设备原编号', width: 140, align: 'center'}
                , {field: 'assets',title: '设备名称', fixed: 'left', width: 170, align: 'center'}
                , {field: 'model', title: '规格型号', width: 120, align: 'center'}
                , {field: 'department', title: '使用科室', width: 140, align: 'center'}
                , {field: 'status_name', title: '当前状态', width: 90, align: 'center'}
                , {field: 'pre_patrol_date', title: '上一次巡查日期', width: 130, align: 'center'}
                , {field: 'pre_maintain_date', title: '上一次保养日期', width: 130, align: 'center'}
                , {field: 'patrol_xc_cycle', title: '巡查周期(天)', width: 110, align: 'center'}
                , {field: 'patrol_pm_cycle', title: '保养周期(天)', width: 110, align: 'center'}
                , {field: 'guarantee_status', title: '维保状态', width: 100, align: 'center'}
                , {field: 'opendate', title: '启用日期', width: 110, align: 'center'}
                , {field: 'operation', title: '操作', fixed: 'right', width: 80, align: 'center'}
            ]]
            , done: function (res) {
                $('.delAssetslistTotal').html('&nbsp;&nbsp;&nbsp;已纳入计划设备列表(' + res.total + '台)');
            }
        });

        //监听已纳入设备列表工具条
        table.on('tool(delAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'del':
                    removedata = removedata.split(',');
                    removedata.splice($.inArray(rows.assnum, removedata), 1);
                    removedata = removedata.join(",");
                    if(!removedata){
                        existsHos = [];
                    }
                    table.reload('addAssetslist', {
                        where: {
                            action:'addAssetslist',
                            removedata: removedata
                        }
                    });
                    table.reload('delAssetslist', {
                        where: {
                            action:'delAssetslist',
                            removedata: removedata
                        }
                    });
                    break;
            }
        });

        //一键纳入功能
        $('.addAll').on('click', function () {
            var checkStatus = table.checkStatus('addAssetslist');
            var data = checkStatus.data;
            if (data.length === 0) {
                layer.msg("请选择要纳入的设备", {icon: 2}, 1000);
                return false;
            }
            for(j = 0,len=data.length; j < len; j++) {
                if(existsHos.length === 0){
                    existsHos.push(data[j]['hospital_id']);
                }else{
                    if($.inArray(data[j]['hospital_id'],existsHos) < 0){
                        layer.msg('请选择同一个医院的设备！',{icon : 2,time:2000});
                        return false;
                    }
                }
            }
            $.each(data, function (k, v) {
                removedata += ',' + v.assnum;
            });
            if (removedata.substr(0, 1) === ',') {
                removedata = removedata.substr(1);
            }
            //刷新
            if (removedata) {
                table.reload('addAssetslist', {where: {removedata: removedata, action:'addAssetslist'}});
                table.reload('delAssetslist', {where: {removedata: removedata, action:'delAssetslist'}});
            }
        });

        //搜索功能
        form.on('submit(addPatrolSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            var assName = $("input[name='assName']").val();
            var startPrice = $("input[name='startPrice']").val();
            var endPrice = $("input[name='endPrice']").val();
            if (startPrice) {
                if (!/^\d+(\.\d+)?$/.test(startPrice + "")) {
                    layer.msg("请输入大于等于0的最小金额", {icon: 2}, 1000);
                    return false;
                }
            }
            if (endPrice) {
                if (!/^[1-9]\d*(\.\d+)?$/.exec(endPrice)) {
                    layer.msg("请输入大于0的最大金额", {icon: 2}, 1000);
                    return false;
                }
            }
            if (startPrice && endPrice) {
                if (parseFloat(endPrice) < parseFloat(startPrice)) {
                    layer.msg("最大金额必须大于最小金额", {icon: 2}, 1000);
                    return false;
                }
            }
            gloabOptions.removedata = removedata;
            gloabOptions. action='addAssetslist';
            gloabOptions.startPrice = startPrice;
            gloabOptions.endPrice = endPrice;
            gloabOptions.type = getType();
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('addAssetslist', {
                url: admin_name+'/Patrol/addPatrol.html'
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        //下一步:生成或修改包操作
        form.on('submit(next)', function (data) {
            var target = $('#delAssetslist').next().find('.layui-table-main').find('tr');
            //var assnum = [];
            var assnum = '';
            //获取设已纳入设备的编号
            target.each(function () {
                //assnum.push($(this).find("td").eq(1).find('div').html());
                assnum += $(this).find("td").eq(1).find('div').html()+',';
            });
            if (!assnum[0]) { 
                layer.msg("请选择要纳入的设备", {icon: 2}, 1000);
                return false;
            }
            if (!$("input[name='patrol_name']").val()) {
                layer.msg("请输入计划名称", {icon: 2}, 1000);
                return false;
            }

            $("input[name='hospital_id']").val(existsHos[0]);
            $("input[name='assnum']").val(assnum);
            var url = $("input[name='nextAction']").val()+'?level='+$("input[name='level']").val();
            layui.use('layer',function (){
                layer.open({
                    id: 'nexts',
                    type: 2,
                    title: '确认计划',
                    shade: 0,
                    anim: 2,
                    offset: 'r',//弹窗位置固定在右边
                    scrollbar: false,
                    area: ['100%', '100%'],
                    maxmin: true,
                    closeBtn: 1,
                    content: url
                });
            });
            // top.layer.open({
            //     id: 'nexts',
            //     type: 2,
            //     title: '确认计划',
            //     shade: 0,
            //     anim: 2,
            //     offset: 'r',//弹窗位置固定在右边
            //     scrollbar: false,
            //     area: ['100%', '100%'],
            //     maxmin: true,
            //     closeBtn: 1,
            //     content: url,
            //     success:function(layero, index){
            //         var body = layer.getChildFrame('body', index);
            //         body.contents().find("#detailId").val('12121');  // #detailId  子页面元素id
            //     }
            // });
            return false;
        });

        //选择设备
        $("#addPatrolAssName").bsSuggest(
            returnAssets()
        ).on('onSetSelectValue', function (e, keyword, data) {

        }).on('onUnsetSelectValue', function () {

        });
        //设备编号搜索建议
        $("#addPatrolAssNum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {

        });

        //选择分类
        $("#addPatrolAssCat").bsSuggest(
            returnCategory()
        );
    });
    exports('controller/patrol/patrol/addPatrol', {});
});