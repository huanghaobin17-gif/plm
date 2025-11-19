layui.define(function (exports) {
    function getStartDate() {
        return $('input[name="departStartDate"]').val();
    }

    function getEndDate() {
        return $('input[name="departEndDate"]').val();
    }

    layui.use(['layer', 'form', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var laydate = layui.laydate, table = layui.table, form = layui.form, suggest = layui.suggest, tablePlug = layui.tablePlug;
//
        //初始化搜索建议插件
        suggest.search();

        $("#departmentBenefitListReset").click(function () {
            layui.index.render();
        });

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        form.render();
        //执行一个laydate实例
        laydate.render({
            elem: '#departmentBenefitListStartDate',
            festival: true,
            value: new Date()
            , type: 'month'
        });
        laydate.render({
            elem: '#departmentBenefitListEndDate',
            festival: true,
            value: new Date()
            , type: 'month'
        });


        //定义一个全局空对象
        var gloabOptions = {};

        //第一个实例
        table.render({
            elem: '#departmentBenefitList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , title: '科室效益分析列表'
            , url: admin_name+'/Benefit/departmentBenefitList.html' //数据接口
            , where: {
                departStartDate: getStartDate()
                , departEndDate: getEndDate()
                , sort: 'departid'
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
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'startDate',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '开始月份',
                    width: 120,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/startDate') == 'false' ? true : false
                }
                , {
                    field: 'endDate',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
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
                    field: 'department',
                    title: '科室名称',
                    minWidth: 180,
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
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 70,
                    align: 'center'
                }
            ]]
        });
        //监听工具条
        table.on('tool(departmentBenefitData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'see':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.department + '】科室效益分析',
                        area: ['1300px', '100%'],
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        isOutAnim: true,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?departid=' + rows.departid]
                    });
                    break;
            }
        });
        form.on('submit(departmentBenefitListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            if (!gloabOptions.departStartDate || !gloabOptions.departEndDate) {
                layer.msg('请选择统计的月份', {icon: 2});
                return false;
            }
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('departmentBenefitList', {
                url: admin_name+'/Benefit/departmentBenefitList.html'
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //建议性搜索科室
        $("#departmentBenefitListDep").bsSuggest(
            returnDepartment()
        );
    });
    exports('benefit/benefit/departmentBenefitList', {});
});



