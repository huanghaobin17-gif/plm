layui.define(function (exports) {
    window.operateEvents = {
        //点击移除
    };
//返回上步按钮
    $("#back").click(function () {
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
    });
    //var index = parent.layer.getFrameIndex(window.name);
    var patrol_name = parent.$("input[name='patrol_name']").val();
    var remark = parent.$("textarea[name='remark']").val();
    var level = parseInt(parent.$("input[name='level']").val());
    var assnum = parent.$("input[name='assnum']").val();
    var hospital_id = parent.$("input[name='hospital_id']").val();
    var nextAction = parent.$("input[name='nextAction']").val();

    var pirx = '';
    switch(level){
        case 1:
            pirx = '(DC)';
            break;
        case 2:
            pirx = '(RC)';
            break;
        case 3:
            pirx = '(PM)';
            break;
    }
    $("input[name='patrol_name']").val(patrol_name+pirx);
    $("input[name='remark']").val(remark);
    $("input[name='assnum']").val(assnum);
    $("input[name='level']").val(level);
    $("input[name='hospital_id']").val(hospital_id);;


    layui.use(['form', 'layer', 'table', 'tablePlug', 'laypage','element','laydate','slider'], function () {
        var form = layui.form, layer = layui.layer, table = layui.table, tablePlug = layui.tablePlug,element = layui.element,laydate = layui.laydate,
            laypage = layui.laypage;
        layer.config(layerParmas());
        form.render();
        // //初始化时间
        lay('.formatDate').each(function(){
            laydate.render(dateConfig(this));
        });
        table.render({
            elem: '#patrol_assets',
            id: 'patrol_assets_cache',
            size: 'sm',
            data:assets_data
            , cols: [[ //表头
                {style: 'background-color: #f9f9f9;',rowspan: 2, type: 'checkbox'}
                , {
                    field: 'assid',
                    title: '序号',
                    width: 40,
                    rowspan: 2,
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'assnum', title: '设备编码', width: 130,rowspan: 2, align: 'center'}
                , {field: 'assets', title: '设备名称', width: 130,rowspan: 2, align: 'center'}
                , {field: 'model', title: '规格型号', width: 110,rowspan: 2, align: 'center'}
                , {field: 'department_name', title: '使用科室', width: 140,rowspan: 2, align: 'center'}
                , {field: 'status', title: '当前状态', width: 80,rowspan: 2, align: 'center'}
                , {field: 'guarantee_status', title: '维保状态', width: 80,rowspan: 2, align: 'center'}
                , {field: 'pre_maintain_date', title: '上次保养日期', width: 110,rowspan: 2, align: 'center'}
                , {field: 'patrol_pm_cycle', title: '保养周期(天)', width: 100,rowspan: 2, align: 'center'}
                , {title: '模板名称', width: 510, colspan: 3}
            ],
                [
                    {
                        field: 'tpName_0',
                        align: 'left',
                        title: '全选 <input type="radio" name="t1_all" value="0" lay-filter="all">',
                        width: 170,
                        templet: function (d) {
                            if(d.mb[0]){
                                if(d.mb[0]['default']){
                                    return '<label><input type="radio" name="' + d.assnum + '" value="' + d.mb[0]['tpid'] + '" data-id="' + d.assnum + '" checked /></label> <a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate"  data-id="' + d.mb[0]['tpid'] + '">' + d.mb[0]['tpName'] + '</a>';
                                }else{
                                    return '<label><input type="radio" name="' + d.assnum + '" value="' + d.mb[0]['tpid'] + '" data-id="' + d.assnum + '" /></label> <a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate"  data-id="' + d.mb[0]['tpid'] + '">' + d.mb[0]['tpName'] + '</a>';
                                }
                            }else{
                                return d.tpName;
                            }
                        }
                    },
                    {
                        field: 'tpName_1',
                        align: 'left',
                        title: '全选 <input type="radio" name="t1_all" value="1" lay-filter="all">',
                        width: 170,
                        templet: function (d) {
                            if(d.mb[1]){
                                if(d.mb[1]['default']){
                                    return '<label><input type="radio" name="' + d.assnum + '" value="' + d.mb[1]['tpid'] + '" data-id="' + d.assnum + '" checked /></label> <a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate"  data-id="' + d.mb[1]['tpid'] + '">' + d.mb[1]['tpName'] + '</a>';
                                }else{
                                    return '<label><input type="radio" name="' + d.assnum + '" value="' + d.mb[1]['tpid'] + '" data-id="' + d.assnum + '" /></label> <a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate" data-id="' + d.mb[1]['tpid'] + '">' + d.mb[1]['tpName'] + '</a>';
                                }
                            }else{
                                return '';
                            }
                        }
                    },
                    {
                        field: 'tpName_2',
                        align: 'left',
                        title: '全选 <input type="radio" name="t1_all" value="2" lay-filter="all">',
                        width: 170,
                        templet: function (d) {
                            if(d.mb[2]){
                                if(d.mb[2]['default']){
                                    return '<label><input type="radio" name="' + d.assnum + '" value="' + d.mb[2]['tpid'] + '" data-id="' + d.assnum + '" checked /></label> <a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate"  data-id="' + d.mb[2]['tpid'] + '">' + d.mb[2]['tpName'] + '</a>';
                                }else{
                                    return '<label><input type="radio" name="' + d.assnum + '" value="' + d.mb[2]['tpid'] + '" data-id="' + d.assnum + '" /></label> <a class="thisTemplate" style="color:#01AAED;cursor:pointer;" lay-event="thisTemplate"  data-id="' + d.mb[2]['tpid'] + '">' + d.mb[2]['tpName'] + '</a>';
                                }
                            }else{
                                return '';
                            }
                        }
                    }
                ]
            ]
            //,skin: 'line' //表格风格
            , even: true
            , done: function (res, curr, count) {
                table.cache.patrol_assets_cache.curr = curr;//标记
                $.each(res.data, function (i, v) {
                    if (v.tempname != undefined) {
                        $("input[name=" + v.tempname + "][value=" + v.tpid + "]").next().click();
                    }
                });
                pages = res.page;
                counts = res.records;
                laypage.render({
                    elem: 'demo1'
                    , count: counts
                    , curr: pages
                    , layout: ['count', 'prev', 'page', 'next', 'refresh', 'skip']
                    , jump: function (obj, first) {
                        var currs = false;
                        if (table.cache.patrol_assets_cache_1) {
                            $.each(table.cache.patrol_assets_cache, function (i, val) {
                                $.each(table.cache.patrol_assets_cache_1, function (o_i, o_val) {
                                    if (val.assid == o_val.assid) {
                                        table.cache.patrol_assets_cache_1[o_i].level_executor_1 = val.level_executor_1;
                                        currs = true;
                                    }
                                });
                            });
                            if (!currs) {
                                table.cache.patrol_assets_cache_1 = table.cache.patrol_assets_cache_1.concat(table.cache.patrol_assets_cache);
                            }
                        } else {
                            table.cache.patrol_assets_cache_1 = new Array;
                            table.cache.patrol_assets_cache_1 = table.cache.patrol_assets_cache;
                        }

                    }
                });
            },
            page: true, //是否显示分页
            limit: 100,
            limits: [100, 200,500],
        });

        //监听全选操作
        form.on('radio(all)', function (data) {
            var mb_index = parseInt(data.value);
            var options = new Array();
            options.data = table.cache.patrol_assets_cache;
            $.each(options.data, function (i, val) {
                options.data[i].tempname = '';
                options.data[i].tpid = 0;//标记
                $.each(options.data[i]['mb'],function (mbi,mbv){
                    if(mbi === mb_index){
                        options.data[i].tempname = mbv.tpName;
                        options.data[i].tpid = parseInt(mbv.tpid);//标记
                        options.data[i]['mb'][mbi]['default'] = true;
                    }else{
                        options.data[i]['mb'][mbi]['default'] = false;
                    }
                });
            });
            table.reload('patrol_assets_cache', options);
        });

        //监听搜索按钮
        form.on('submit(next)', function (data) {
            return false;
        });
        form.on('submit(allot)', function (data) {
            //options.data=data;定位
            var level = $(".layui-this").attr('data-level');
            if (level == 1) {
                var options = new Array();
                options.data = table.cache.table1;
                if (layui.table.checkStatus('table1').data == "") {
                    layer.msg('请选中分配的设备', {icon: 2});
                    return false;
                }
                $.each(layui.table.checkStatus('table1').data, function (i, val) {
                    $.each(options.data, function (o_i, o_v) {
                        if (o_v.assid == val.assid) {
                            options.data[o_i].level_executor_1 = data.field.executor;
                        }
                    });
                    // if (val.assid==) {}

                });
                table.reload('table1', {
                    options,
                    page: {
                        curr: table.cache.table1.curr //重新从第 1 页开始
                    }
                });
                $.each(options.data, function (i, v) {
                    if (v.tempname != undefined) {
                        $("input[name=" + v.tempname + "][value=" + v.tpid + "]").next().click();
                    }
                });
            } else if (level == 2) {
                var options = new Array();
                options.data = table.cache.table2;
                if (layui.table.checkStatus('table2').data == "") {
                    layer.msg('请选中分配的设备', {icon: 2});
                    return false;
                }
                $.each(layui.table.checkStatus('table2').data, function (i, val) {
                    $.each(options.data, function (o_i, o_v) {
                        if (o_v.assid == val.assid) {
                            options.data[o_i].level_executor_2 = data.field.executor;
                        }
                    });
                    // if (val.assid==) {}

                });
                table.reload('table2', {
                    options,
                    page: {
                        curr: table.cache.table2.curr //重新从第 1 页开始
                    }
                });
                $.each(options.data, function (i, v) {
                    if (v.tempname != undefined) {
                        $("input[name=" + v.tempname + "][value=" + v.tpid + "]").next().click();
                    }
                });
            } else if (level == 3) {
                var options = new Array();
                options.data = table.cache.table3;
                if (layui.table.checkStatus('table3').data == "") {
                    layer.msg('请选中分配的设备', {icon: 2});
                    return false;
                }
                $.each(layui.table.checkStatus('table3').data, function (i, val) {
                    $.each(options.data, function (o_i, o_v) {
                        if (o_v.assid == val.assid) {
                            options.data[o_i].level_executor_3 = data.field.executor;
                        }
                    });
                    // if (val.assid==) {}

                });
                //table.reload('table3', options);
                table.reload('table3', {
                    options,
                    page: {
                        curr: table.cache.table3.curr //重新从第 1 页开始
                    }
                });
                $.each(options.data, function (i, v) {

                    if (v.tempname != undefined) {
                        $("input[name=" + v.tempname + "][value=" + v.tpid + "]").next().click();
                    }
                });
            }
            return false;
        });
        form.on('submit(saveCurLevel)', function (data) {
            var params = data.field;
            params.type = 'save_plan'
            var assnum = params.assnum;
            assnum = assnum.slice(0,assnum.lastIndexOf(','));
            var assnum_arr = assnum.split(',');
            var tp_flag = true;
            $.each(assnum_arr, function (i, val) {
                if(!params[val]){
                    tp_flag = false;
                }
            });
            if(!tp_flag){
                layer.msg("请为所有设备选择一个保养模板", {icon: 2}, 1000);
                return false;
            }
            if(!params.startDate || !params.endDate){
                layer.msg("请设置执行日期", {icon: 2}, 1000);
                return false;
            }
            if (!(/(^[1-9]\d*$)/.test(params.cycle_setting))) {
                layer.msg('请输入正整数的周期设置', {icon: 2});
                return false;
            }
            $.ajax({
                timeout: 5000,
                type: "POST",
                data: params,
                url: nextAction,
                dataType: "json",
                success: function (data) {
                    if (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1,time:2000}, function (){
                                parent.layer.closeAll('iframe');
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                    } else {
                        layer.msg('数据异常', {icon: 2});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败！", {icon: 2});
                }
            });
            return false;
        });
        //监听周期按钮
        form.on('switch(switchTest)', function(data){
            var params = {};
            if (data.elem.checked==true) {
                $('#cycle').show();
                $('#cycle_setting').show();
            }else{
                $('#cycle').hide();
                $('#cycle_setting').hide();
            }
            return false;
        });
        form.on('submit(addAssets)', function (data) {
            return false;
        });
        form.on("radio", function (data) {
            if(data.value >= 0){
                var new_tpid = parseInt(data.value);
                $.each(table.cache.patrol_assets_cache, function (i, val) {
                    if (data.elem.dataset.id == val.assnum) {
                        val.tempname = data.elem.name;
                        val.tpid = new_tpid;//标记
                    }
                });
            }else{
                switch (data.value){
                    case 'day':
                        $(".unit").html('天');
                        break;
                    case 'week':
                        $(".unit").html('周');
                        break;
                    case 'month':
                        $(".unit").html('月');
                        break;
                }
            }
            return false;
        });
        //操作栏按钮
        table.on('tool(patrol_assets)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            switch (layEvent){
                case 'settingTemplate'://设置模板
                    var url = $(this).attr('data-url') + '?assid=' + $(this).attr('data-id') + '&assnum=' + $(this).attr('data-assnum');
                    top.layer.open({
                        id: 'settingTemplate',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['100%', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            location.reload();
                        }
                    });
                    break;
                case 'thisTemplate':
                    var tpid = $(this).prev().children("input").attr('value');
                    top.layer.open({
                        type: 2,
                        title: '【' + $(this).html() + '】模板预览',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [admin_name + '/PatrolSetting/template?id=' + tpid + '&type=showTemplate']
                    });
                    break;
            }
        });
    });

    $("input[name='cycle_setting']").bind('input propertychange', function() {
        var num = $("input[name='cycle_setting']").val();
        $(".num").html(num);
    })
    //是否默认选中
    function stateFormatter(value, row, index) {
        //在后台设置后
        if (row.check == true)
            return {
                disabled: true,//设置是否可用
                checked: true//设置选中
            };
        return value;
    }

    //已纳入计划设备列表参数
    function RCQueryParams(params) {
        return {
            order: params.order,//排序
            sort: params.sort,//排序
            arr_assnum: arr_assnum
        };
    }

    //已纳入计划设备列表参数
    function XCQueryParams(params) {
        return {
            order: params.order,//排序
            sort: params.sort,//排序
            arr_assnum: arr_assnum
        };
    }

    //已纳入计划设备列表参数
    function PMQueryParams(params) {
        return {
            order: params.order,//排序
            sort: params.sort,//排序
            arr_assnum: arr_assnum
        };
    }
    function removeAssets(e) {
        //执行移除操作，如有计划进行过保存操作，删除数据库记录
        var url = admin_name + '/Patrol/addPatrol.html';
        layer.confirm('移除设备操作会影响到已经保存过的计划，确定移除吗？', {icon: 3, title: '操作提示'}, function (index) {
            var params = {};
            params['assnum'] = $(e).attr('data-id');
            params['packid'] = $("input[name='packid']").val();
            params['action'] = 'removeAssets';

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
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            //location.reload();
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 2});
                },
                complete: function () {
                    layer.closeAll('loading');
                }
            });
            layer.close(index);
        });
    }


    var index = '';
    var assets_data = [];
    $(document).ready(function () {
        var params = {};
        params.assnum = assnum;
        params.type = 'getAssets';
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: nextAction,
            data: params,
            dataType: "json",
            async:false,
            success: function (res) {
                if(res.code == 200){
                    assets_data = res.rows;
                }
            },
            error: function () {
                layer.msg("网络访问错误", {icon: 2}, 1000);
            }
        });

        $(".layui-tab-title li").click(function () {
            var level = $(this).attr('data-level');
            var patrid = $("input[name='patrid']").val();
            if (patrid != "") {
                return false;
            }
            var desc = '';
            var table = layui.table;
            var options = new Array();
            if (level == 1) {
                options.data = table.cache.table1;
                table.reload('table1', {
                    options,
                });
                desc = $("input[name='rcdesc']").val();
            }
            if (level == 2) {
                options.data = table.cache.table2;
                table.reload('table2', {
                    options,
                });
                desc = $("input[name='xcdesc']").val();
            }
            if (level == 3) {
                options.data = table.cache.table3;
                table.reload('table3', {
                    options,
                })
                desc = $("input[name='pmdesc']").val();
            }
            $("textarea[name='remark']").val(desc);
        });
        $(".show").click(function () {
            var url = admin_name + '/PatrolSetting/template?id=' + $(this).attr('data-id') + '&type=showTemplate';
            top.layer.open({
                type: 2,
                title: $(this).html(),
                scrollbar: false,
                area: ['100%', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                closeBtn: 1,
                content: url
            });
        });
        $(".editTemplate").click(function () {
            var url = admin_name + '/Patrol/next?id=' + $(this).attr('data-id');
            top.layer.open({
                id: 'editTemplate',
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['100%', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    location.reload();
                }
            });
        });
        $(".release").click(function () {
            var url = $(this).attr('data-url');
            var cycid = $(this).attr('data-id');
            top.layer.open({
                id: 'release',
                type: 2,
                title: $(this).html(),
                anim: 2, //动画风格
                offset: 'r',//弹窗位置固定在右边
                scrollbar: false,
                area: ['100%', '100%'],
                content: url + '?cycid=' + cycid
            });
        });
        $(".Release").click(function () {
            var url = $(this).attr('data-url');
            var patrid = $(this).attr('data-id');
            var period = $(this).attr('data-period');
            top.layer.open({
                id: 'Release',
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['100%', '100%'],
                content: url + '?patrid=' + patrid + '&period=' + period
            });
        });
        $(".showDetail").click(function () {
            var url = $(this).attr('data-url');
            var cyclenum = $(this).attr('data-cyclenum');
            top.layer.open({
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['100%', '100%'],
                content: url + '?cyclenum=' + cyclenum
            });
        });
        $("#allot").click(function () {
            var level = $(".layui-this").attr('data-level');
            if (level == 1) {
                var tar = $('.rctemplate').find("a");
                var flag = 0;
                tar.each(function () {
                    if ($(this).attr("class") == 'settingTemplate') {
                        flag = 1;
                    }
                    if ($(this).attr("class") == 'notSettingTemplate') {
                        flag = 3;
                    }
                    if ($(this).attr("class") == 'editTemplate') {
                        flag = 2;
                    }
                });
                if (flag == 1) {
                    layer.msg("请先对未绑定模板的设备设置模板", {icon: 2}, 1000);
                    return false;
                }
                if (flag == 3) {
                    layer.msg("请联系管理员为设备设置模板", {icon: 2}, 1000);
                    return false;
                }
                if (flag == 2) {
                    layer.msg("请先设置当前级别模板明细", {icon: 2}, 1000);
                    return false;
                }
            }
            if (level == 2) {
                var tar = $('.xctemplate').find("a");
                var flag = 0;
                tar.each(function () {
                    if ($(this).attr("class") == 'settingTemplate') {
                        flag = 1;
                    }
                    if ($(this).attr("class") == 'notSettingTemplate') {
                        flag = 3;
                    }
                    if ($(this).attr("class") == 'editTemplate') {
                        flag = 2;
                    }
                });
                if (flag == 1) {
                    layer.msg("请先对未绑定模板的设备设置模板", {icon: 2}, 1000);
                    return false;
                }
                if (flag == 3) {
                    layer.msg("请联系管理员为设备设置模板", {icon: 2}, 1000);
                    return false;
                }
                if (flag == 2) {
                    layer.msg("请先设置当前级别模板明细", {icon: 2}, 1000);
                    return false;
                }
            }
            if (level == 3) {
                var tar = $('.pmtemplate').find("a");
                var flag = 0;
                tar.each(function () {
                    if ($(this).attr("class") == 'settingTemplate') {
                        flag = 1;
                    }
                    if ($(this).attr("class") == 'notSettingTemplate') {
                        flag = 3;
                    }
                    if ($(this).attr("class") == 'editTemplate') {
                        flag = 2;
                    }
                });
                if (flag == 1) {
                    layer.msg("请先对未绑定模板的设备设置模板", {icon: 2}, 1000);
                    return false;
                }
                if (flag == 3) {
                    layer.msg("请联系管理员为设备设置模板", {icon: 2}, 1000);
                    return false;
                }
                if (flag == 2) {
                    layer.msg("请先设置当前级别模板明细", {icon: 2}, 1000);
                    return false;
                }
            }
            var executor = $("select[name='executor']").val();
            if (!executor) {
                layer.msg("请选择执行人", {icon: 2}, 1000);
                return false;
            }
            var target = '';
            if (level == 3) {
                target = $("#pmdata").find("input[type='checkbox']");
                target.each(function () {
                    if ($(this).is(':checked') && $(this).attr('disabled') != 'disabled') {
                        $(this).parent().parent().find(".executor").html(executor);
                        $(this).attr('disabled', true);
                    }
                });
            } else if (level == 2) {
                target = $("#xcdata").find("input[type='checkbox']");
                target.each(function () {
                    if ($(this).is(':checked') && $(this).attr('disabled') != 'disabled') {
                        $(this).parent().parent().find(".executor").html(executor);
                        $(this).attr('disabled', true);
                    }
                });
            } else if (level == 1) {
                target = $("#rcdata").find("input[type='checkbox']");
                target.each(function () {
                    if ($(this).is(':checked') && $(this).attr('disabled') != 'disabled') {
                        $(this).parent().parent().find(".executor").html(executor);
                        $(this).attr('disabled', true);
                    }
                });
            }
        });
        $("#saveCurLevel11").click(function () {
            var level = $(".layui-this").attr('data-level');
            var url = $("input[name='action']").val();
            var packid = $("input[name='packid']").val();
            var patrid = $("input[name='patrid']").val();
            var hospital_id = $("input[name='hospital_id']").val();
            var remark = $("textarea[name='remark']").val();
            var planNum = '';
            var planName = '';
            var executedate = '';
            var unit = '';
            var target = '';
            var data = {};
            data.level = level;
            data.packid = packid;
            data.patrid = patrid;
            data.hospital_id = hospital_id;
            data.remark = remark;
            if (level == 3) {
                //标记
                planNum = $("input[name='PM_patrolnum']").val();
                planName = $("input[name='PM_patrolname']").val();
                expect_complete_date = $("input[name='PM_expect_complete_date']").val();
                executedate = $("input[name='PM_executedate']").val();
                if (!planName) {
                    layer.msg("计划名称不能为空", {icon: 2}, 1000);
                    return false;
                }
                if (!executedate) {
                    layer.msg("请设置计划开始执行日期", {icon: 2}, 1000);
                    return false;
                }
                if (!expect_complete_date) {
                    layer.msg("请设置计划预计完成日期", {icon: 2}, 1000);
                    return false;
                }
                if (executedate > expect_complete_date) {
                    layer.msg("完成日期不能小于执行日期", {icon: 2}, 1000);
                    return false;
                }
                target = PM_data;
                targets = layui.table.cache.table3;
                var flag = true;
                $.each(target, function (i, val) {
                    $.each(targets, function (o_i, o_val) {
                        if (val.assid == o_val.assid) {
                            target[i].level_executor_3 = o_val.level_executor_3;
                            target[i].tpid = o_val.tpid;
                        }
                    });
                    if (val.level_executor_3 == "") {
                        flag = false;
                    }
                });
                if (!flag) {
                    layer.msg("请为每条设备分配执行人", {icon: 2}, 1000);
                    return false;
                }
                var executor = {};
                var tmpArr = new Array;
                var tmpExecutor = '';
                $.each(target, function (i, val) {
                    tmpExecutor = val.level_executor_3;
                    if ($.inArray(tmpExecutor, tmpArr) < 0) {
                        tmpArr.push(tmpExecutor);
                        var tmpassnum = '';
                        $.each(target, function (t_i, t_val) {
                            if (t_val.level_executor_3 == tmpExecutor) {
                                tmpassnum += t_val.assnum + ',';
                            }
                        });
                        tmpassnum = tmpassnum.substring(0, tmpassnum.length - 1);
                        executor[tmpExecutor] = tmpassnum;
                    }
                });

                var assnum_name = new Array;
                var assnum_tpid = new Array;
                var no_tpid = true;
                $.each(targets, function (i, val) {
                    var n = val.assnum;
                    assnum_name.push(n);
                    if (val.tpid == undefined || val.tpid == 0) {
                        no_tpid = false;
                    }
                    assnum_tpid.push(parseInt(val.tpid));
                });
                if (!no_tpid) {
                    layer.msg("请为每台设备选择一个保养模板！", {icon: 2}, 1000);
                    return false;
                }
                data.planNum = planNum;
                data.planName = planName;
                data.executedate = executedate;
                data.expect_complete_date = expect_complete_date;
                data.executor = JSON.stringify(executor);
                data.assnum_name = JSON.stringify(assnum_name);
                data.assnum_tpid = JSON.stringify(assnum_tpid);
            } else if (level == 2) {
                planNum = $("input[name='RC_patrolnum']").val();
                planName = $("input[name='RC_patrolname']").val();
                expect_complete_date = $("input[name='RC_expect_complete_date']").val();
                executedate = $("input[name='RC_executedate']").val();
                if (!planName) {
                    layer.msg("计划名称不能为空", {icon: 2}, 1000);
                    return false;
                }
                if (!executedate) {
                    layer.msg("请设置计划开始执行时间", {icon: 2}, 1000);
                    return false;
                }
                if (!expect_complete_date) {
                    layer.msg("请设置计划预计完成日期", {icon: 2}, 1000);
                    return false;
                }
                target = RC_data;
                targets = layui.table.cache.table2;
                var flag = true;
                $.each(target, function (i, val) {
                    $.each(targets, function (o_i, o_val) {
                        if (val.assid == o_val.assid) {
                            target[i].level_executor_2 = o_val.level_executor_2;
                            target[i].tpid = o_val.tpid;
                        }
                    });
                    if (val.level_executor_2 == "") {
                        flag = false;
                    }
                });
                if (!flag) {
                    layer.msg("请为每条设备分配执行人", {icon: 2}, 1000);
                    return false;
                }
                var executor = {};
                var tmpArr = new Array;
                var tmpExecutor = '';
                $.each(target, function (i, val) {
                    tmpExecutor = val.level_executor_2;
                    if ($.inArray(tmpExecutor, tmpArr) < 0) {
                        tmpArr.push(tmpExecutor);
                        var tmpassnum = '';
                        $.each(target, function (t_i, t_val) {
                            if (t_val.level_executor_2 == tmpExecutor) {
                                tmpassnum += t_val.assnum + ',';
                            }
                        });
                        tmpassnum = tmpassnum.substring(0, tmpassnum.length - 1);
                        executor[tmpExecutor] = tmpassnum;
                    }
                });
                var assnum_name = new Array;
                var assnum_tpid = new Array;
                var no_tpid = true;
                $.each(targets, function (i, val) {
                    var n = val.assnum;
                    assnum_name.push(n);
                    if (val.tpid == undefined || val.tpid == 0) {
                        no_tpid = false;
                    }
                    assnum_tpid.push(parseInt(val.tpid));
                });
                if (!no_tpid) {
                    layer.msg("请为每台设备选择一个保养模板！", {icon: 2}, 1000);
                    return false;
                }
                data.planNum = planNum;
                data.planName = planName;
                data.executedate = executedate;
                data.expect_complete_date = expect_complete_date;
                data.executor = JSON.stringify(executor);
                data.assnum_name = JSON.stringify(assnum_name);
                data.assnum_tpid = JSON.stringify(assnum_tpid);
            } else if (level == 1) {
                planName = $("input[name='DC_patrolname']").val();
                expect_complete_date = $("input[name='DC_expect_complete_date']").val();
                executedate = $("input[name='DC_executedate']").val();
                var dlen = $('.level1_nodetail').length;
                if (dlen > 0) {
                    layer.msg("请先设置当前级别模板明细", {icon: 2}, 1000);
                    return false;
                }
                if (!planName) {
                    layer.msg("计划名称不能为空", {icon: 2}, 1000);
                    return false;
                }
                if (!executedate) {
                    layer.msg("请设置计划开始执行日期", {icon: 2}, 1000);
                    return false;
                }
                if (!expect_complete_date) {
                    layer.msg("请设置计划预计完成日期", {icon: 2}, 1000);
                    return false;
                }
                target = DC_data;
                targets = layui.table.cache.table1;
                var flag = true;
                $.each(target, function (i, val) {
                    $.each(targets, function (o_i, o_val) {
                        if (val.assid == o_val.assid) {
                            target[i].level_executor_1 = o_val.level_executor_1;
                            target[i].tpid = o_val.tpid;
                        }
                    });
                    if (val.level_executor_1 == "") {
                        flag = false;
                    }
                });
                if (!flag) {
                    layer.msg("请为每条设备分配执行人", {icon: 2}, 1000);
                    return false;
                }
                var executor = {};
                var tmpArr = new Array;
                var tmpExecutor = '';
                $.each(target, function (i, val) {
                    tmpExecutor = val.level_executor_1;
                    if ($.inArray(tmpExecutor, tmpArr) < 0) {
                        tmpArr.push(tmpExecutor);
                        var tmpassnum = '';
                        $.each(target, function (t_i, t_val) {
                            if (t_val.level_executor_1 == tmpExecutor) {
                                tmpassnum += t_val.assnum + ',';
                            }
                        });
                        tmpassnum = tmpassnum.substring(0, tmpassnum.length - 1);
                        executor[tmpExecutor] = tmpassnum;
                    }
                });
                var assnum_name = new Array;
                var assnum_tpid = new Array;
                var no_tpid = true;
                $.each(targets, function (i, val) {
                    var n = val.assnum;
                    assnum_name.push(n);
                    if (val.tpid == undefined || val.tpid == 0) {
                        no_tpid = false;
                    }
                    assnum_tpid.push(parseInt(val.tpid));
                });
                if (!no_tpid) {
                    layer.msg("请为每台设备选择一个保养模板！", {icon: 2}, 1000);
                    return false;
                }
                data.planNum = planNum;
                data.planName = planName;
                data.executedate = executedate;
                data.expect_complete_date = expect_complete_date;
                data.executor = JSON.stringify(executor);
                data.assnum_name = JSON.stringify(assnum_name);
                data.assnum_tpid = JSON.stringify(assnum_tpid);
            }
            $.ajax({
                timeout: 5000,
                type: "POST",
                data: data,
                url: url,
                dataType: "json",
                success: function (data) {
                    if (data) {
                        if (data.status == 1) {
                            if (level == 1) {
                                $("input[name='rcisSave']").val('1');
                            } else if (level == 2) {
                                $("input[name='xcisSave']").val('1');
                            } else if (level == 3) {
                                $("input[name='pmisSave']").val('1');
                            }
                            layer.msg(data.msg, {icon: 1}, 1000);
                        } else {
                            layer.msg(data.msg, {icon: 2}, 1000);
                        }
                    } else {
                        layer.msg('数据异常', {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败！", {icon: 2}, 1000);
                }
            });
            var patrid = $("input[name='patrid']").val();
            if (patrid != "") {
                parent.layer.closeAll('iframe');
            }
        });
        $("#allnext").click(function () {
            parent.layer.closeAll('iframe');
        });
        $("#addAssets").click(function () {
            var packid = $("input[name='packid']").val();
            var url = admin_name + '/Patrol/addAssets?packid=' + packid;
            top.layer.open({
                id: 'addAssets',
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['100%', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    location.reload();
                }
            });
        });
    });

    layui.use(['layer', 'form'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        form.on('checkbox(allChoose)', function (data) {
            if (data.elem.checked) {
                $(this).parent().parent().find("input[name='level2']").prop('checked', true);
            } else {
                $(this).parent().parent().find("input[name='level2']").prop('checked', false);
            }
            form.render('checkbox');
        });
        form.verify({
            name: function (value, item) {
                if (value) {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '模板名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '模板名称不能全为数字';
                    }
                } else {
                    return '模板名称不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(scan)', function (data) {
            var level2 = "";
            $("input[name='level2']:checked").each(function () {
                if (level2 == '') {
                    level2 = $(this).val();
                } else {
                    level2 += "," + $(this).val();
                }
            });
            var level1 = "";
            $("input[name='level1']:checked").each(function () {
                if (level1 == '') {
                    level1 = $(this).val();
                } else {
                    level1 += "," + $(this).val();
                }
            });
            var level3 = "";
            $("input[name='level3order']").each(function () {
                if (level3 == '') {
                    level3 = $(this).val();
                } else {
                    level3 += "," + $(this).val();
                }
            });
            params = data.field;
            params.level3 = level3;
            params.level1 = level1;
            params.level2 = level2;
            //下一步
            var layerId = parent.layer.getFrameIndex(window.name);
            top.layer.open({
                id: 'scan',
                type: 2,
                title: $(this).html(),
                scrollbar: false,
                area: ['100%', '100%'],
                closeBtn: 1,
                move: false,
                content: [admin_name + '/Patrol/confirmAdd?name=' + data.field['name'] + '&level1=' + data.field['level1'] + '&level2=' + data.field['level2'] + '&level3=' + data.field['level3'] + '&update=' + data.field['update'] + '&layerId=' + layerId + '&tpid=' + data.field['tpid']],
                end: function () {
                    location.reload();
                }
            });
            var index = parent.layer.getFrameIndex(window.name);
            //parent.layer.close(index);
            return false;
        })
    });

    layui.use('element', function () {
        var element = layui.element;
    });


    //移除一行
    function delRow(k) {
        $(k).parent().parent().remove();
    }

    function ontop(a) {
        //置顶
        var tr = $(a).parents("tr");
        tr.fadeOut().fadeIn();
        $(a).parents("table").prepend(tr);
    }

    layui.use(['layer', 'form'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        //监听提交
        form.on('submit(add)', function (data) {
            params = data.field;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'confirmAdd',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data) {
                        if (data.status == 1) {
                            CloseWindow(data.msg);
                        } else {
                            layer.msg(data.msg, {icon: 2}, 1000);
                        }
                    } else {
                        layer.msg("数据异常！", {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                }
            });

            return false;
        })
    });

    layui.use('element', function () {
        var element = layui.element;
    });

    //返回上一步
    $("#prev").click(function () {
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
    });


    //关闭页面
    function CloseWindow(msg) {

        parent.layer.msg(msg, {
            icon: 1,
            time: 2000
        }, function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            var layerId = $('input[name="layerId"]').val();
            parent.layer.close(index); //再执行关闭
            parent.layer.close(layerId);
        });
    }

    exports('controller/patrol/patrol/allocationPlan', {});
});