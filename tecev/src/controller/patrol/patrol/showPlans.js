layui.define(function (exports) {
    layui.use(['layer', 'form', 'table', 'laydate', 'suggest', 'laydate', 'tablePlug','element'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, laydate = layui.laydate, tablePlug = layui.tablePlug,element = layui.element;

        form.render();
        layer.config(layerParmas());
        var gloabOptions = {};
        if (show_detail_table) {
            table.render({
                elem: '#showPlansLists'
                , limits: [5, 10, 20, 50, 100]
                , data: assets,
                toolbar: '#showPlansToolbar',
                defaultToolbar: []
                , page: {
                    theme: '#428bca', //当前页码背景色
                    groups: 10 //只显示 1 个连续页码
                    , prev: '上一页'
                    , next: '下一页'
                }
                , cols: [[ //表头
                    {
                        field: 'assid', title: '序号', width: 60, fixed: 'left', align: 'center', type: 'space', templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    }
                    , {field: 'assnum', fixed: 'left', title: '设备编号', width: 160, align: 'center'}
                    , {field: 'assorignum', title: '设备原编号', width: 160, align: 'center'}
                    , {field: 'assets', title: '设备名称', width: 160, align: 'center'}
                    , {field: 'model', title: '规格型号', width: 120, align: 'center'}
                    , {field: 'department', title: '使用科室', width: 140, align: 'center'}
                    , {field: 'pre_date', title: pre_date_name, width: 130, align: 'center'}
                    , {field: 'details_num', title: detail_name, width: 110, align: 'center'}
                    , {field: 'executor', fixed: 'right', title: '执行人', width: 110, align: 'center'}
                    , {field: 'template_name', fixed: 'right', title: template_name, width: 140, align: 'center'}
                    , {field: 'operation', fixed: 'right', title: operation_name, width: 100, align: 'center'}
                ]], done: function (res) {
                    const {data} = res;
                    let count = data.filter(v => {
                        return v.hasOwnProperty('need_sign') && v.need_sign === true
                    })
                    if (count.length>0) {
                        $("#yijian").addClass('layui-btn-disabled');
                        $("#yijian").attr('disabled', 'disabled');
                    }
                }
            });
        } else {
            table.render({
                elem: '#showPlansLists'
                , limits: [5, 10, 20, 50, 100]
                , data: assets
                , page: {
                    theme: '#428bca', //当前页码背景色
                    groups: 10 //只显示 1 个连续页码
                    , prev: '上一页'
                    , next: '下一页'
                }
                , cols: [[ //表头
                    {
                        field: 'assid', title: '序号', width: 60, fixed: 'left', align: 'center', type: 'space', templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    }
                    , {field: 'assnum', fixed: 'left', title: '设备编号', width: 160, align: 'center'}
                    , {field: 'assorignum', title: '设备原编号', width: 160, align: 'center'}
                    , {field: 'assets', title: '设备名称', width: 160, align: 'center'}
                    , {field: 'model', title: '规格型号', width: 120, align: 'center'}
                    , {field: 'department', title: '使用科室', width: 140, align: 'center'}
                    , {field: 'pre_date', title: pre_date_name, width: 130, align: 'center'}
                    , {field: 'details_num', title: detail_name, width: 110, align: 'center'}
                    , {field: 'template_name', fixed: 'right', title: template_name, width: 140, align: 'center'}
                ]]
            });
        }

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

        //确定审核
        $('#approval').bind('click', function () {
            var pid = $("input[name='pid']").val();
            var is_adopt = $("input[name='is_adopt']:checked").val();
            var tips = '';
            if (is_adopt == 1) {
                tips = '确认通过审核？';
            } else {
                tips = '确认驳回申请？';
            }
            //审核意见
            var remark = $("textarea[name='remark']").val();
            var url = admin_name + '/Patrol/approve';
            var param = {};
            param['pid'] = pid;
            param['is_adopt'] = is_adopt;
            param['remark'] = remark;
            layer.confirm(tips, {icon: 3, title: '审核计划'}, function (index) {
                $.ajax({
                    timeout: 5000,
                    dataType: "json",
                    type: "POST",
                    url: url,
                    data: param,
                    async: false,
                    beforeSend: function () {
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        if (data.status == 1) {
                            CloseWin(data.msg);
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 3000});
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
            });
        });
        if(get_plan_cycle){
            table.render({
                elem: '#plan_list'
                ,limit: 10
                , limits: [10, 20, 50, 100]
                , data: plans
                ,toolbar: '#plansDataToolbar'
                , page: {
                    theme: '#428bca', //当前页码背景色
                    groups: 10 //只显示 1 个连续页码
                    , prev: '上一页'
                    , next: '下一页'
                }
                , cols: [[ //表头
                    {
                        field: 'patrid',
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
                    , {field: 'patrol_num',title: '计划编号',width: 160,align: 'center'}
                    , {field: 'current_total',title: '当前期次 / 总期次',width: 140,align: 'center'}
                    , {field: 'patrol_date', title: '计划执行日期', width: 200, align: 'center'}
                    , {field: 'numstatus',title: '执行 / 实际 / 计划设备台次', width: 200, align: 'center'}
                    , {field: 'implement_sum',title: '已执行设备数', width: 120, align: 'center'}
                    , {field: 'abnormal_sum',title: '异常设备数', width: 120, align: 'center'}
                    , {
                        field: 'operation',
                        style: 'background-color: #f9f9f9;',
                        title: '操作',
                        fixed: 'right',
                        minWidth: 100,
                        align: 'center'
                    }
                ]]
            });
        }
        //监听工具条
        table.on('tool(plansData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var thisUrl = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'showDetail':
                    top.layer.open({
                        id: 'showDetail',
                        type: 2,
                        title: rows.patrol_name+' -- 第 '+rows.period+' 期',
                        area: ['1120px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: thisUrl,
                        end: function () {
                            if (flag) {
                                table.reload('plan_list');
                            }
                        },
                        cancel: function () {
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        table.on('toolbar(plansData)', function (obj) {
            var thisUrl = $(this).attr('data-url');
            switch (obj.event) {
                case 'createNewPlan'://生成下一周期计划
                    var param = {};
                    param['patrid'] = $(this).attr('data-id');
                    param['action'] = 'create_next_plan';
                    $.ajax({
                        timeout: 5000,
                        dataType: "json",
                        type: "POST",
                        url: thisUrl,
                        data: param,
                        async: false,
                        beforeSend: function () {
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success: function (res) {
                            if (res.status == 1) {
                                layer.msg(res.msg, {icon: 1}, function (){
                                    location.reload();
                                });
                            } else {
                                layer.msg(res.msg, {icon: 2, time: 3000});
                            }
                        }
                    });
                    break;
            }
        });
    });
    exports('controller/patrol/patrol/showPlans', {});
});



