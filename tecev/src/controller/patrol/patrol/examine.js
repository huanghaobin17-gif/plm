layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug','element'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, layer = layui.layer, tablePlug = layui.tablePlug,element = layui.element;
        var url = $('input[name="url"]').val();
        var action = $('input[name="action"]').val();
        var listAction = $('input[name="listAction"]').val();
        //初始化搜索建议插件
        suggest.search();
        form.render();
        layer.config(layerParmas());

        table.render({
            elem: '#examLists'
            ,id: 'exam_lists_cache'
            ,data:assets
            ,page: true //是否显示分页
            ,limit: 10
            ,limits: [5, 10, 20, 50, 100]
            , even: true
            , cols: [[ //表头
                {field: 'assid',title: '序号',width: 55,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                ,{field: 'assnum',fixed: 'left',title: '设备编号',width: 160,align: 'center'}
                ,{field: 'assorignum',title: '设备原编号',width: 160,align: 'center'}
                ,{field: 'assets',title: '设备名称',width: 160,align: 'center'}
                ,{field: 'model',title: '规格型号',width: 120,align: 'center'}
                ,{field: 'department',title: '使用科室',width: 140,align: 'center'}
                ,{field: 'report_num',title: '报告编号',width: 150,align: 'center'}
                ,{field: 'finish_time',title: '完成时间',width: 160,align: 'center'}
                ,{field: 'abnormal_details_num',title: '异常项 / 明细项',width: 130,align: 'center'}
                ,{field: 'execute_user',title: '执行人',width: 110,align: 'center'}
                ,{field: 'template_name',fixed: 'right',title: template_name,width: 140,align: 'center'}
                ,{field: 'result',fixed: 'right',title: result_name,width: 90,align: 'center'}
                ,{field: 'operation',fixed: 'right',title: '操作',width: 80,align: 'center'}
            ]]
        });
        //监听工具条
        table.on('tool(examData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'examine':
                    top.layer.open({
                        id: 'examine_3',
                        type: 2,
                        title: rows.assets + '-验收单',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['1080px', '100%'],
                        content: [url+'?action=examineOne&execid='+rows.execid],
                        end: function () {
                            if(flag == 1){
                                location.reload();
                            }
                        },
                        cancel: function () {
                            flag = 0;
                        }
                    });
                    break;
            }
        });
        //监听排序
        table.on('sort(examineData)', function (obj) {
            table.reload('examine', {
                //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                initSort: obj
                , where: {
                    sort: obj.field //排序字段
                    , action:listAction
                    , order: obj.type //排序方式
                    , exallid: returnExallid()
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res, curr, count) {
                }
            });
        });
        form.on('submit(examineSearch)', function (data) {
            var table = layui.table;
            var gloabOptions = data.field;
            gloabOptions.action=listAction;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('周期时段设置不合理', {icon: 2});
                    return false;
                }
            }
            //刷新表格时，默认回到第一页
            table.reload('examine', {
                url: url
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        //确认全部验收
        $('#examine_all').on('click',function () {
            var url = $("input[name='url']").val();
            var patrid = $("input[name='pid']").val();
            var cycid = $("input[name='cycid']").val();
            var remark=$("textarea[name='remark']").val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: {patrid: patrid, remark: remark,cycid:cycid},
                dataType: "json",
                success: function (data) {
                    layer.msg(data.msg, {icon: 1,time:2000}, function () {
                        location.reload();
                    });
                },
                error: function () {
                    layer.msg("网络访问错误", {icon: 2}, 1000);
                }
            });
            return false;
            });
        //点击模板名称弹窗
        $('.show_template').on('click',function () {
            top.layer.open({
                type: 2,
                title:'【'+$(this).html()+'】模板明细项',
                area: ['800px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [admin_name+'/Patrol/showPlans?id='+$(this).attr('data-id')+'&action=showTemplate']
            });
        });

        //搜索按钮触发事件
        //提交并完成该保养任务
        form.on('submit(doExamine)', function () {
            var url = $('input[name="url"]').val();
            var params={remark:$("textarea[name='remark']").val(),exallid: returnExallid()};
            submit($, params, url);
            return false;
        });

        $("#examineAssets").bsSuggest({
            url: admin_name+'/Public/getExamineAsset?exallid=' + returnExallid(),
            effectiveFields: ["assnum", "assets"],
            searchFields: [ "assets"],
            effectiveFieldsAlias: {assnum: "设备编码",assets: "设备名称"},
            listStyle: {
                "max-height": "375px", "max-width": "400px",
                "overflow": "auto", "width": "480px", "text-align": "center"
            },
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            keyField: "assets",
            clearable: false
        });
    });
    function returnExallid() {
        return $("input[name='exallid']").val();
    }
    exports('controller/patrol/patrol/examine', {});
});