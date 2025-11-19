layui.define(function (exports) {
    function getStartDate() {
        return $('input[name="startDate"]').val();
    }

    function getEndDate() {
        return $('input[name="endDate"]').val();
    }

    layui.use(['layer', 'form', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var laydate = layui.laydate, table = layui.table, form = layui.form, suggest = layui.suggest, tablePlug = layui.tablePlug;

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
//
        $("#singleBenefitListReset").click(function () {
            layui.index.render();
        });
        layer.config(layerParmas());
        suggest.search();
        form.render();
        //执行一个laydate实例
        laydate.render({
            elem: '#singleBenefitListStartDate',
            festival: true,
            //value: new Date()
            value: preMonth
            , type: 'month'
        });
        laydate.render({
            elem: '#singleBenefitListEndDate',
            festival: true,
            value: preMonth
            , type: 'month'
        });
        var gloabOptions = {};
        //第一个实例
        table.render({
            elem: '#singleBenefitList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , title: '单机效益分析列表'
            , url: admin_name+'/Benefit/singleBenefitList.html' //数据接口
            , where: {
                startDate: getStartDate()
                , endDate: getEndDate()
                , sort: 'assid'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            },
            toolbar: 'true',
            defaultToolbar: ['filter', 'exports']
            , cols: [[ //表头
                {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'startDate',
                    fixed: 'left',
                    title: '开始月份',
                    width: 120,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/startDate') == 'false' ? true : false
                }
                , {
                    field: 'endDate',
                    fixed: 'left',
                    title: '截止月份',
                    width: 120,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/endDate') == 'false' ? true : false
                }
                , {
                    field: 'monthNum',
                    title: '统计月数',
                    width: 110,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/monthNum') == 'false' ? true : false
                }
                , {
                    field: 'assnum',
                    title: '设备编号',
                    minWidth: 180,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false
                }
                , {
                    field: 'assets',
                    title: '设备名称',
                    minWidth: 200,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false
                }
                , {
                    field: 'department',
                    title: '所属科室',
                    width: 180,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/department') == 'false' ? true : false
                }
                , {
                    field: 'income',
                    title: '总收入',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/income') == 'false' ? true : false
                }
                , {
                    field: 'all_cost',
                    title: '总支出',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/all_cost') == 'false' ? true : false
                }
                , {
                    field: 'balance',
                    title: '总利润',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/balance') == 'false' ? true : false
                }
                , {
                    field: 'work_number',
                    title: '总诊疗次数',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/work_number') == 'false' ? true : false
                }
                , {field: 'operation', title: '操作', fixed: 'right', width: 70, align: 'center'}
            ]]
        });
        //监听工具条
        table.on('tool(singleBenefitData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'see':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备效益分析',
                        area: ['1280px', '100%'],
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 2,
                        offset: 'r',
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid + '&sdate=' + rows.startDate + '&edate=' + rows.endDate]
                    });
                    break;
            }
        });
        form.on('submit(singleBenefitListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('singleBenefitList', {
                url: admin_name+'/Benefit/singleBenefitList.html'
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //设备名称搜索建议
        $("#singleBenefitListAssets").bsSuggest(
            returnAssets()
        );
        //建议性搜索科室
        $("#singleBenefitListDep").bsSuggest(
            returnDepartment()
        );
    });
    exports('benefit/benefit/singleBenefitList', {});
});



