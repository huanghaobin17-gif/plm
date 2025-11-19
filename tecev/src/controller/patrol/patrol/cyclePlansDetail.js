layui.define(function (exports) {
    layui.use(['layer', 'form', 'table','element','laydate'], function () {
        var table = layui.table, form = layui.form,element = layui.element,laydate = layui.laydate;
        form.render();

        laydate.render({
            elem: '#executeData' //指定元素
            , type: 'datetime',
            calendar: true
            , trigger: 'click'
            ,value: now
            ,min: min
            ,max: max
        });

        table.render({
            elem: '#plansDetailLists'
            ,limit: 10
            , limits: [10, 20, 50, 100]
            ,size: 'sm'
            , data: assets
            ,toolbar: '#plansDetailToolbar'
            , page: {
                theme: '#428bca', //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'}
                ,{
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'assets',title: '设备名称',width: 180,align: 'center'}
                , {field: 'assnum',title: '设备编号',width: 140,align: 'center'}
                , {field: 'status_name',title: '设备状态',width: 120,align: 'center'}
                //, {field: 'assorignum', title: '设备原编号', width: 140, align: 'center'}
                , {field: 'model',title: '规格型号', width: 140, align: 'center'}
                , {field: 'department',title: '所属科室', width: 160, align: 'center'}
                , {field: 'temp_name',title: '巡查/保养模板名称', width: 160, align: 'center'}
                , {field: 'points_num',title: '巡查/保养明细(项)', width: 140, align: 'center'}
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作/结果',
                    fixed: 'right',
                    minWidth: 100,
                    align: 'center'
                }
            ]]
        });

        //监听工具条
        table.on('tool(plansDetailData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var thisUrl = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'doTask':
                    top.layer.open({
                        id: 'maintains',
                        type: 2,
                        title: rows.assets + '-' + rows.patrol_level_name + '-明细表',
                        shade: 0,
                        anim: 2,
                        offset: 'r',//弹窗位置固定在右边
                        scrollbar: false,
                        area: ['1000px', '100%'],
                        content: thisUrl + '?action=setSituation&assnum=' + rows.assnum + '&cycid=' + rows.cycid,
                        end: function (layero, index) {
                            if (flag) {
                                location.reload();//刷新
                            }
                        },
                        cancel: function () {
                            flag = 0;
                        }
                    });
                    break;
                case 'examine':
                    top.layer.open({
                        id: 'examines',
                        type: 2,
                        title: '巡查任务实施设备清单【' + rows.patrolname + '】',
                        area: ['1080px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [thisUrl]
                    });
                    break;
                case 'sign':
                    layer.msg('请到微信端进行签到后再进行保养')
                    break;
            }
        });
        table.on('toolbar(plansDetailData)', function (obj) {
            switch (obj.event) {
                case 'yijian'://一键保养
                    var checkStatus = table.checkStatus('plansDetailLists');
                    //获取选中行数量，可作为是否有选中行的条件
                    var length = checkStatus.data.length;
                    if (length == 0) {
                        top.layer.msg('请选择要一键保养的设备', {icon: 2});
                        return false;
                    }
                    let assnum = [];
                    var flag = true;
                    var cycid = 0;
                    checkStatus.data.forEach(v => {
                        if(v.is_complete == 1){
                            flag = false;
                        }
                        if(v.is_complete != 1){
                            cycid = v.cycid;
                            assnum.push(v.assnum);
                        }
                    });
                    if(!flag){
                        top.layer.msg('请选择未保养的设备', {icon: 2});
                        return false;
                    }
                    if(assnum.length == 0){
                        top.layer.msg('该计划的设备均已完成保养', {icon: 2});
                        return false;
                    }
                    let that = this;
                    if(is_overdue == 1){
                        layer.open({
                            type: 1,
                            title: '请选择完成时间',
                            area: ['420px'],
                            offset: ['40px', '250px'],
                            content: $('#execute_time'),
                            btn: ['确定','取消'],
                            yes: function(index, layero){
                                layer.close(layer.index);
                                var complete_time = $('input[name="complete_time"]').val();
                                layer.confirm('选择一键保养的设备，默认所有保养项目的保养结果均为合格！确定要继续一键保养？', {icon: 3, title: '确定一键保养'}, function (index) {
                                    var params = {};
                                    params.action = 'batch_maintain';
                                    params.cycid = cycid;
                                    params.complete_time = complete_time;
                                    params.assnum = JSON.stringify(assnum);
                                    $.ajax({
                                        type: "POST",
                                        url: $(that).attr('data-url'),
                                        data: params,
                                        dataType: "json",
                                        async: true,
                                        beforeSend: function (){
                                            layer.load(1, {
                                                shade: [0.1,'#fff'] //0.1透明度的白色背景
                                            });
                                        },
                                        success: function (res) {
                                            if(res.status == 1){
                                                layer.msg(res.msg, {icon: 1}, function (){
                                                    location.reload();
                                                });
                                            }else{
                                                layer.msg(res.msg, {icon: 2});
                                            }
                                        },
                                        error: function () {
                                            layer.msg("网络访问失败", {icon: 2}, 1000);
                                        },
                                        complete:function () {
                                            layer.closeAll('loading');
                                        }
                                    });
                                    layer.close(index);
                                });
                            },
                            btn2: function(index, layero){
                                console.log('取消');
                            }
                        });
                    }else{
                        layer.confirm('选择一键保养的设备，默认所有保养项目的保养结果均为合格！确定要继续一键保养？', {icon: 3, title: '确定一键保养'}, function (index) {
                            var params = {};
                            params.action = 'batch_maintain';
                            params.cycid = cycid;
                            params.assnum = JSON.stringify(assnum);
                            $.ajax({
                                type: "POST",
                                url: $(that).attr('data-url'),
                                data: params,
                                dataType: "json",
                                async: true,
                                beforeSend: function (){
                                    layer.load(1, {
                                        shade: [0.1,'#fff'] //0.1透明度的白色背景
                                    });
                                },
                                success: function (res) {
                                    if(res.status == 1){
                                        layer.msg(res.msg, {icon: 1}, function (){
                                            location.reload();
                                        });
                                    }else{
                                        layer.msg(res.msg, {icon: 2});
                                    }
                                },
                                error: function () {
                                    layer.msg("网络访问失败", {icon: 2}, 1000);
                                },
                                complete:function () {
                                    layer.closeAll('loading');
                                }
                            });
                            layer.close(index);
                        });
                    }



                    // let that = this;
                    // let filter = assets.filter(v => {
                    //     return v.operation.replace(/<.*?>/g, '') !== '合格' && !v.operation.includes('layui-btn-disabled');
                    // })
                    // if (filter.length === 0) {
                    //     layer.msg("该计划已没有您可操作的设备了", {icon: 0}, 1000);
                    //     return
                    // }
                    // console.log(filter);
                    // return false;
                    // filter.forEach(v => {
                    //     $.ajax({
                    //         url: $(that).attr('data-url'),
                    //         data: {
                    //             assnum: v.assnum,
                    //             action: 'getTaskList',
                    //             patrol_level: v.patrol_level,
                    //             cycid: v.cycid
                    //         },
                    //         async: true,
                    //         dataType: "json",
                    //         success: function (data) {
                    //             console.log(data);
                    //             return false;
                    //             $.ajax({
                    //                 type: "POST",
                    //                 url: $(that).attr('data-url'),
                    //                 data: get_yijian_params(data),
                    //                 dataType: "json",
                    //                 async: true,
                    //                 success: function () {
                    //                 },
                    //                 error: function () {
                    //                     layer.msg("网络访问失败", {icon: 2}, 1000);
                    //                 },
                    //             });
                    //         },
                    //         error: function () {
                    //             layer.msg("网络访问失败", {icon: 2}, 1000);
                    //         },
                    //     });
                    // })
                    // layer.msg("一键保养成功", {icon: 1, time: 3500},()=>{
                    //     location.reload()
                    // });
                    break;
            }
        });
        //点击模板名称弹窗
        $('.show_template').on('click', function () {
            top.layer.open({
                type: 2,
                title: '【' + $(this).html() + '】模板明细项',
                area: ['800px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                closeBtn: 1,
                content: [showPlans + '?id=' + $(this).attr('data-id') + '&action=showTemplate']
            });
        });
    });
    exports('controller/patrol/patrol/cyclePlansDetail', {});
});



