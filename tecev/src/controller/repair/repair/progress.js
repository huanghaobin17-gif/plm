var gloabOptions = {};
var progress = $("input[name='progress']").val();
layui.define(function(exports){
    layui.use(['form', 'suggest', 'laydate', 'table', 'tablePlug'], function () {
        var form = layui.form, suggest = layui.suggest, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;
        layer.config(layerParmas());
        //初始化时间
        lay('.formatDate').each(function(){
            laydate.render(dateConfig(this));
        });

        layer.config(layerParmas());

        form.render();
        //第一个实例
        table.render({
            elem: '#progressTable'
            , limits: [10, 20, 50, 100]
            , loading: true
            ,title: '维修进程列表'
            , url: progress //数据接口
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
            , cols: [[ //表头
                {
                    field: 'repid',
                    title: '序号',
                    width: 60,
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'repnum', title: '维修单号', width: 160, align: 'center'}
                , {
                    field: 'assets',
                    title: '设备名称',
                    width: 140,
                    align: 'center',
                    templet: function (d) {
                        return '<a lay-event="showAssets" style="color: #00a6c8;" href="javascript:void(0)">' + d.assets + '</a>';
                    }
                }
                , {
                    field: 'repaid',
                    title: '维修状态<div class="progress_menu_status">（<span class="statusColor-green">绿色</span>表示已完成、<span class="statusColor-red">红色</span>表示进行中、<span class="statusColor-grey">灰色</span>表示未进行、<span class="titlecolor">蓝色</span>表示可操作）</div>',
                    align: 'center',
                    templet: function (rows) {
                        var html = '<table class="layui-table insideTable" lay-size="sm">';
                        html += '<tr>';
                        html += '<td style="width:70px;"></td>';
                        html += '<td class="statusColor-green">维修申请</td>';
                        if (rows.status == 1) {
                            html += '<td class="statusColor-red">' + rows.href + '</td>';
                            html += '<td class="statusColor-grey">' + rows.href2 + '</td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">检修</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修审核</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修中</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">科室验收</button></td>';
                        } else if (rows.status == 2) {
                            html += '<td class="statusColor-green">派工</td>';
                            html += '<td class="statusColor-red">' + rows.href + '</td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">检修</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修审核</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修中</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">科室验收</button></td>';
                        } else if (rows.status == 3) {
                            html += '<td class="statusColor-green">派工</td>';
                            html += '<td class="statusColor-green">接单</td>';
                            html += '<td class="statusColor-red">' + rows.href + '</td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修审核</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修中</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">科室验收</button></td>';
                        } else if (rows.status == 4) {
                            html += '<td class="statusColor-green">派工</td>';
                            html += '<td class="statusColor-green">接单</td>';
                            html += '<td class="statusColor-green">检修</td>';

                            if (typeof rows.current_approver === 'string') {
                                const currentApprover = rows.current_approver
                                    .split(',')
                                    .map(item => item.trim())
                                    .filter(item => item && item !== '牛年')
                                    .join('，');

                                html += /* html */ `
                                    <td class="statusColor-red">
                                        ${ rows.href }
                                        <i class="layui-icon layui-icon-tips" lay-tips="当前审批人：${ currentApprover }" style="vertical-align: middle; color: #666;"></i>
                                    </td>
                                `;
                            } else {
                                html += '<td class="statusColor-red">' + rows.href + '</td>';
                            }

                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">维修中</button></td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">科室验收</button></td>';
                        } else if (rows.status == 5) {
                            html += '<td class="statusColor-green">派工</td>';
                            html += '<td class="statusColor-green">接单</td>';
                            html += '<td class="statusColor-green">检修</td>';
                            html += '<td class="statusColor-green">维修审核</td>';
                            html += '<td class="statusColor-red">' + rows.href + '</td>';
                            html += '<td class="statusColor-grey"><button class="layui-btn layui-btn-xs layui-btn-disabled">科室验收</button></td>';
                        } else if (rows.status == 6) {
                            html += '<td class="statusColor-green">派工</td>';
                            html += '<td class="statusColor-green">接单</td>';
                            html += '<td class="statusColor-green">检修</td>';
                            html += '<td class="statusColor-green">维修审核</td>';
                            html += '<td class="statusColor-green">维修中</td>';
                            html += '<td class="statusColor-red">' + rows.href + '</td>';
                        } else if (rows.status == 7) {
                            html += '<td class="statusColor-green">派工</td>';
                            html += '<td class="statusColor-green">接单</td>';
                            html += '<td class="statusColor-green">检修</td>';
                            html += '<td class="statusColor-green">维修审核</td>';
                            html += '<td class="statusColor-green">维修中</td>';
                            html += '<td class="statusColor-green">科室验收</td>';
                        }
                        html += '</tr>';
                        html += '<tr>';
                        html += '<td style="font-weight: bold;">操作人</td>';
                        html += '<td>' + rows.applicant + '</td>';
                        html += '<td>' + rows.assign + '</td>';
                        html += '<td>' + rows.response + '</td>';
                        html += '<td>' + rows.overhaulUser + '</td>';
                        html += '<td>' + rows.examine_user + '</td>';
                        html += '<td>' + rows.engineer + '</td>';
                        html += '<td>' + rows.checkperson + '</td>';
                        html += '</tr>';
                        html += '<tr class="special">';
                        html += '<td style="font-weight: bold;">操作时间</td>';
                        html += '<td>' + rows.applicant_time + '</td>';
                        html += '<td>' + rows.assign_time + '</td>';
                        html += '<td>' + rows.response_date + '</td>';
                        html += '<td>' + rows.overhauldate + '</td>';
                        html += '<td>' + rows.examine_time + '</td>';
                        html += '<td>' + rows.engineer_time + '</td>';
                        html += '<td>' + rows.checkdate + '</td>';
                        html += '</tr>';
                        html += '</table>';
                        return html;
                    }
                }
            ]]
        });
        //监听工具条
        table.on('tool(progressTable)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showAssets':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [admin_name+'/Lookup/showAssets?assid='+rows.assid]
                    });
                    break;
                case 'progressData':
                    var flag = 1;
                    top.layer.open({
                        type: 2,
                        title: $(this).html(),
                        area: ['1000px', '100%'],
                        // area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if (flag) {
                                table.reload('progressTable', {
                                    url: progress
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        //监听排序
        table.on('sort(progressTable)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('progressTable', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        suggest.search();
        //监听提交
        form.on('submit(progressSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            var startDate = gloabOptions.progressStartDate;
            var endDate = gloabOptions.progressEndDate;
            if (startDate && endDate) {
                if (endDate < startDate) {
                    layer.msg('报修时间设置不合理', {icon: 2});
                    return false;
                }
            }
            //刷新表格时，默认回到第一页
            table.reload('progressTable', {
                url: progress
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        $("#progressAssName").change(function () {
            if ($("#progressAssName").val() == '') {
                $("input[name='assNum']").val('');
                $("input[name='assOrignum']").val('');
            }
        });
        $("#progressAssNum").change(function () {
            if ($("#progressAssNum").val() == '') {
                $("input[name='assName']").val('');
                $("input[name='assOrignum']").val('');
            }
        });

        //选择设备
        $("#progressAssName").bsSuggest(returnAssets()).on('onDataRequestSuccess', function (e, result) {
        }).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='assNum']").val(data.assnum);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='assNum']").val('');
        });

        //选择设备编号
        $("#progressAssNum").bsSuggest(returnAssnum()).on('onDataRequestSuccess', function (e, result) {
        }).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='assName']").val(data.assets);
            $("input[name='assOrignum']").val(data.assorignum);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='assName']").val('');
            $("input[name='assOrignum']").val('');
        });
    });
    exports('repair/repair/progress', {});
});
