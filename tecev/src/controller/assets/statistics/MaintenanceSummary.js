layui.define(function(exports){
    var tabterm = 0;
    var tabDepart = 0;
    var tabCost = 0;
    var tabAssets = 0;
    layui.use(['form', 'table', 'element', 'tablePlug'], function () {
        var form = layui.form, $ = layui.jquery, element = layui.element,
            table = layui.table, tablePlug = layui.tablePlug;
        form.render();
        var mySupplier = echarts.init(document.getElementById('MaintenanceSummarySupplierMain'));
        mySupplier.clear();
        //显示一个简单的加载动画
        mySupplier.showLoading();
        table.render({
            elem: '#MaintenanceSummarySupplier'
            , size: 'sm'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , title: '中标供应商列表'
            , url: admin_name+'/AssetsStatis/MaintenanceSummary.html' //数据接口
            , where: {
                tab: 'Supplier'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'company' //排序字段，对应 cols 设定的各字段名
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
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                // {type:'checkbox',fixed: 'left'},
                {
                    field: 'company_id', title: '序号', width: 80, align: 'center', type: 'space', templet: function (d) {
                    return d.LAY_INDEX;
                }
                }
                , {field: 'company', title: '维保公司名称', align: 'center'}
                , {field: 'assidNum', title: '参保设备数量', align: 'center'}
                , {field: 'insuredsumNum', title: '公司来维保记录', align: 'center'}
                , {field: 'departNum', title: '相关科室数量', align: 'center'}
            ]]
            , done: function (res, curr, count) {
                mySupplier.hideLoading();
                if (res.code == 200) {
                    // 填入数据 
                    mySupplier.setOption({
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        grid: {
                            y: '10%'

                        },
                        toolbox: {
                            feature: {
                                // dataView: {show: true, readOnly: false},
                                // magicType: {show: true, type: ['line', 'bar']},
                                // restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        legend: {
                            data: ['参保设备数量', '相关科室数量', '公司来维保次数']
                        },
                        xAxis: [
                            {
                                type: 'category',
                                nameRotate: 9,
                                data: res.Bar.company,
                                axisPointer: {
                                    type: 'shadow'
                                },
                                axisLabel: {
                                    interval: 0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                                    rotate: 50//标签倾斜的角度
                                }
                            }
                        ],
                        yAxis: [
                            {
                                minInterval: 1,
                                type: 'value',
                                name: '数量',
                                interval: 1,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            },
                            {
                                minInterval: 1,
                                type: 'value',
                                name: '参保次数',
                                interval: 1,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            }
                        ],
                        series: [
                            {
                                barMaxWidth:40,
                                name: '参保设备数量',
                                type: 'bar',
                                data: res.Bar.assid
                            },
                            {
                                barMaxWidth:40,
                                name: '相关科室数量',
                                type: 'bar',
                                data: res.Bar.depart
                            },
                            {
                                barMaxWidth:40,
                                name: '公司来维保次数',
                                yAxisIndex: 1,
                                type: 'line',
                                data: res.Bar.insuredsum
                            }
                        ]
                    });
                    // changeCanvas();
                }
            }

        });
        element.on('tab(MaintenanceSummaryTab)', function (data) {
            switch (data.index) {
                case 1:
                    if (tabterm == 0) {
                        //设备故障统计表格
                        var myTerm = echarts.init(document.getElementById('MaintenanceSummaryTermMain'));
                        myTerm.clear();
                        //显示一个简单的加载动画
                        myTerm.showLoading();
                        table.render({
                            elem: '#MaintenanceSummaryTerm'
                            , size: 'sm'
                            //,height: '600'
                            , loading: true
                            , title: '维保期限列表'
                            , url: admin_name+'/AssetsStatis/MaintenanceSummary.html' //数据接口
                            , where: {
                                // sort: 'repid', 
                                // ,order: 'asc'
                                tab: 'Term'
                            } //如果无需传递额外参数，可不加该参数
                            , initSort: {
                                field: 'LongAssidNum' //排序字段，对应 cols 设定的各字段名
                                // ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                            },
                            toolbar: 'true',
                            defaultToolbar: ['filter','exports']
                            , cols: [[ //表头
                                // {type:'checkbox',fixed: 'left'},
                                {
                                    field: 'id',
                                    title: '序号',
                                    width: 80,
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'LongAssidNum', title: '一至三个月到期', align: 'center'}
                                , {field: 'MetaphaseAssidNum', title: '一个月内到15天到期', align: 'center'}
                                , {field: 'SoonAssidNum', title: '15天内到期', align: 'center'}
                                , {
                                    field: 'assidNum', title: '相关设备数量', align: 'center', templet: function (d) {
                                        return d.LongAssidNum + d.MetaphaseAssidNum + d.SoonAssidNum;
                                    }
                                }
                                , {
                                    field: 'departNum', title: '相关科室数量', align: 'center', templet: function (d) {
                                        return d.LongDepartNum + d.MetaphaseDepartNum + d.SoonDepartNum;
                                    }
                                }
                                , {
                                    field: '', title: '相关供应商数量', align: 'center', templet: function (d) {
                                        return d.LongCount + d.MetaphaseCount + d.SoonCount;
                                    }
                                }
                            ]]
                            , done: function (res, curr, count) {
                                myTerm.hideLoading();
                                if (res.code == 200) {
                                    myTerm.setOption({
                                        title: {
                                            text: '参保统计图',
                                            x: 'center'
                                        },
                                        tooltip: {
                                            trigger: 'item',
                                            formatter: "{a} <br/>{b}: {c} ({d}%)"
                                        },
                                        series: [
                                            {
                                                name: '数量',
                                                type: 'pie',
                                                selectedMode: 'single',
                                                radius: [0, '30%'],

                                                label: {
                                                    normal: {
                                                        position: 'inner'
                                                    }
                                                },
                                                labelLine: {
                                                    normal: {
                                                        show: false
                                                    }
                                                },
                                                data: res.Pie.series.data
                                            },
                                            {
                                                name: '数量',
                                                type: 'pie',
                                                radius: ['40%', '55%'],
                                                label: {
                                                    normal: {
                                                        formatter: '{a|{a}}{abg|}\n{hr|}\n  {b|{b}：}{c}  {per|{d}%}  ',
                                                        backgroundColor: '#eee',
                                                        borderColor: '#aaa',
                                                        borderWidth: 1,
                                                        borderRadius: 4,
                                                        // shadowBlur:3,
                                                        // shadowOffsetX: 2,
                                                        // shadowOffsetY: 2,
                                                        // shadowColor: '#999',
                                                        // padding: [0, 7],
                                                        rich: {
                                                            a: {
                                                                color: '#999',
                                                                lineHeight: 22,
                                                                align: 'center'
                                                            },
                                                            // abg: {
                                                            //     backgroundColor: '#333',
                                                            //     width: '100%',
                                                            //     align: 'right',
                                                            //     height: 22,
                                                            //     borderRadius: [4, 4, 0, 0]
                                                            // },
                                                            hr: {
                                                                borderColor: '#aaa',
                                                                width: '100%',
                                                                borderWidth: 0.5,
                                                                height: 0
                                                            },
                                                            b: {
                                                                fontSize: 16,
                                                                lineHeight: 33
                                                            },
                                                            per: {
                                                                color: '#eee',
                                                                backgroundColor: '#334455',
                                                                padding: [2, 4],
                                                                borderRadius: 2
                                                            }
                                                        }
                                                    }
                                                },
                                                data: res.Pie.data
                                            }
                                        ]
                                    });
                                }
                            }
                        });
                        tabterm = 1;
                    }
                    break;
                case 2:
                    if (tabCost == 0) {
                        //设备故障统计表格
                        table.render({
                            elem: '#MaintenanceSummaryCost'
                            , size: 'sm'
                            //,height: '600'
                            , loading: true
                            ,title: '维保费用列表'
                            , url: admin_name+'/AssetsStatis/MaintenanceSummary.html' //数据接口
                            , where: {
                                // sort: 'repid',
                                // ,order: 'asc'
                                tab: 'cost'
                            } //如果无需传递额外参数，可不加该参数
                            , initSort: {
                                field: 'assnum' //排序字段，对应 cols 设定的各字段名
                                // ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
                            }
                            , limits: [10, 20, 50, 100]
                            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                                //,curr: 5 //设定初始在第 5 页
                                //,theme: '#428bca' //当前页码背景色
                                groups: 10 //只显示 1 个连续页码
                                , prev: '上一页'
                                , next: '下一页'
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
                            },
                            toolbar: 'true',
                            defaultToolbar: ['filter','exports']
                            , cols: [[ //表头
                                // {type:'checkbox',fixed: 'left'},
                                {
                                    fixed: 'left',
                                    field: 'assets_id',
                                    title: '序号',
                                    width: 80,
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'assnum', title: '设备编号', align: 'center',fixed: 'left',width: 150}
                                , {field: 'assets', title: '设备名称', align: 'center',fixed: 'left',width: 150}
                                , {field: 'model', title: '规格 / 型号', align: 'center',width:120}
                                , {field: 'cost', title: '参保费用', align: 'center',width:120}
                                , {field: 'buy_price', title: '设备原值', align: 'center',width:120}
                                , {field: 'MP_ratio_AP', title: '保费占原值比', align: 'center',width:120}
                                , {field: 'CP_ratio_AllCP', title: '保费占所有保费总额比', align: 'center',width:180}
                                , {field: 'buy_price_sum', title: '设备所在科室设备总额', align: 'center',width:180}
                                , {field: 'CP_ratio_AllAP', title: '保费占科室设备总额比', align: 'center',width:180}
                            ]]
                        });
                        tabCost = 1;
                    }
                    break;
                case 3:
                    if (tabDepart == 0) {
                        //设备故障统计表格
                        var myDepart = echarts.init(document.getElementById('MaintenanceSummaryDepartMain'));
                        myDepart.clear();
                        //显示一个简单的加载动画
                        myDepart.showLoading();
                        table.render({
                            elem: '#MaintenanceSummaryDepart'
                            , size: 'sm'
                            //,height: '600'
                            , loading: true
                            ,title: '科室统计列表'
                            , url: admin_name+'/AssetsStatis/MaintenanceSummary.html' //数据接口
                            , where: {
                                // sort: 'repid',
                                // ,order: 'asc'
                                tab: 'depart'
                            } //如果无需传递额外参数，可不加该参数
                            , initSort: {
                                field: 'department' //排序字段，对应 cols 设定的各字段名
                                // ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                            },
                            toolbar: 'true',
                            defaultToolbar: ['filter','exports']
                            , cols: [[ //表头
                                // {type:'checkbox',fixed: 'left'},
                                {
                                    field: 'department_id',
                                    title: '序号',
                                    width: 80,
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'department', title: '科室名称', align: 'center'}
                                , {field: 'assidNum', title: '科室设备总数', align: 'center'}
                                , {field: 'joinNum', title: '参保设备数量', align: 'center'}
                                , {field: 'honaiNum', title: '参保期内台数', align: 'center'}
                                , {field: 'joinProportion', title: '参保设备占比', align: 'center'}
                                , {field: 'dePaulProportion', title: '脱保设备占比', align: 'center'}
                            ]]
                            , done: function (res, curr, count) {
                                myDepart.hideLoading();
                                var colors = ['#5793f3', '#d14a61', '#675bba'];
                                if (res.code == 200) {
                                    myDepart.setOption({
                                        title: {
                                            text: '各科室资产参保汇总统计',
                                            subtext: '按科室统计'
                                        },
                                        tooltip: {
                                            trigger: 'axis',
                                            formatter: function (a) {
                                                return ('科室 : ' + a[0]['axisValue']
                                                    + '</br>参保设备数量 : ' + a[0]['data']
                                                    + '<br>保修期内台数 : ' + a[1]['data']
                                                    + '<br>科室设备总数 : ' + a[2]['data']
                                                    + '<br>参保设备占比 : ' + a[3]['data']
                                                    + '<br>脱保设备占比 : ' + a[4]['data']
                                                );
                                            }
                                        },
                                        legend: {
                                            show: true,//是否显示
                                            type: 'plain',//'plain'：普通图例。缺省就是普通图例。'scroll'：可滚动翻页的图例。当图例数量较多时可以使用
                                            left: '35%',//图例组件离容器左侧的距离。
                                            top: '5',//图例组件离容器上侧的距离。
                                            width: '',//图例组件宽度。一般不用设置，默认宽度
                                            x: 'right',
                                            data: ['参保设备数量', '保修期内台数', '科室设备总数']
                                        },
                                        dataZoom: [
                                            {
                                                type: 'slider',
                                                xAxisIndex: 0,//作用在X轴
                                                start: 0,//左边在 0% 的位置。
                                                end: 100// 右边在 100% 的位置。
                                            }
                                        ],
                                        grid: {
                                            top: 80,//grid 组件离容器上侧的距离
                                            left: '5%',//grid 组件离容器上侧的距离
                                            bottom: 100,
                                            tooltip: {
                                                trigger: 'axis',
                                                axisPointer: {
                                                    type: 'shadow',
                                                    label: {
                                                        show: true,
                                                        formatter: function (params) {
                                                            return params.value.replace('\n', '');
                                                        }
                                                    }
                                                }
                                            }
                                        },
                                        toolbox: {
                                            show: true,
                                            feature: {
                                                //dataView : {show: true, readOnly: false},
                                                //magicType : {show: true, type: ['pie', 'bar']},
                                                //restore : {show: true},
                                                saveAsImage: {show: true}
                                            }
                                        },
                                        calculable: true,
                                        xAxis: [
                                            {
                                                type: 'category',
                                                nameRotate: 9,//坐标轴名字旋转角度值。
                                                data: res.depart,
                                                axisLabel: {
                                                    interval: 0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                                                    rotate: 40//标签倾斜的角度
                                                }
                                            }
                                        ],
                                        yAxis: [
                                            {
                                                minInterval: 1,
                                                type: 'value',
                                                name: '设备数量（台）',//坐标轴名称
                                                nameGap: 15,//坐标轴名称与轴线之间的距离。
                                                nameRotate: 0,//坐标轴名字旋转角度值。
                                                position: 'left',
                                                axisLine: {
                                                    lineStyle: {
                                                        color: colors[1]
                                                    }
                                                },
                                                axisLabel: {
                                                    formatter: '{value} 台'
                                                }
                                            },
                                            {
                                                minInterval: 1,
                                                type: 'value',
                                                name: '科室设备总数（台）',
                                                position: 'right',
                                                axisLine: {
                                                    lineStyle: {
                                                        color: colors[0]
                                                    }
                                                },
                                                axisLabel: {
                                                    formatter: '{value} 台'
                                                }
                                            }
                                        ],
                                        series: [
                                            {
                                                name: '参保设备数量',
                                                type: 'bar',
                                                data: res.joinNum
                                            }, {
                                                name: '保修期内台数',
                                                type: 'bar',
                                                data: res.honaiNum
                                            }, {
                                                name: '科室设备总数',
                                                yAxisIndex: 1,
                                                type: 'line',
                                                data: res.assidNum
                                            }, {
                                                name: '参保设备占比',
                                                type: 'line',
                                                data: res.joinProportion
                                            }, {
                                                name: '脱保设备占比',
                                                type: 'line',
                                                data: res.dePaulProportion
                                            }
                                        ]
                                    });
                                }
                            }
                        });
                        tabDepart = 1;
                    }
                    break;
                case 4:
                    if(tabAssets==0){
                        table.render({
                            elem: '#MaintenanceSummaryAssets'
                            , size: 'sm'
                            //,height: '600'
                            , loading: true
                            ,title: '设备统计列表'
                            , url: admin_name+'/AssetsStatis/MaintenanceSummary.html' //数据接口
                            , where: {
                                // sort: 'repid',
                                // ,order: 'asc'
                                tab: 'assets'
                            } //如果无需传递额外参数，可不加该参数
                            , initSort: {
                                field: 'assnum' //排序字段，对应 cols 设定的各字段名
                                // ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
                            }
                            , limits: [10, 20, 50, 100]
                            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                                //,curr: 5 //设定初始在第 5 页
                                //,theme: '#428bca' //当前页码背景色
                                groups: 10 //只显示 1 个连续页码
                                , prev: '上一页'
                                , next: '下一页'
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
                            },
                            toolbar: 'true',
                            defaultToolbar: ['filter','exports']
                            , cols: [[ //表头
                                // {type:'checkbox',fixed: 'left'},
                                {
                                    field: 'assets_id',
                                    title: '序号',
                                    width: 80,
                                    align: 'center',
                                    type: 'space',
                                    templet: function (d) {
                                        return d.LAY_INDEX;
                                    }
                                }
                                , {field: 'assnum', title: '设备编号', align: 'center'}
                                , {field: 'assets', title: '设备名称', align: 'center'}
                                , {field: 'insurSum', title: '历史参保次数', align: 'center'}
                                , {field: 'repSum', title: '当前在保期间维修次数', align: 'center'}
                            ]]
                        });
                        tabAssets=1;
                    }
                    break;
            }

        });
        form.on('submit(MaintenanceSummaryCostSearch)', function (data) {
            var table = layui.table;
            var typeCost=data.field.typeCost;
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('MaintenanceSummaryCost', {
                url: admin_name+'/AssetsStatis/MaintenanceSummary.html' //数据接口
                , where: {
                    typeCost:typeCost,
                    tab: 'cost'
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

    });


// $(function () {
//     showChart();
// });
    function showChart(params) {

    }

//修改显示的查询条件
    function showConditions(res) {
        $('.MaintenanceSummaryReportConditions').html(res);
    }
//改变表格的分类类型
    function showTd(res) {
        $('.MaintenanceSummaryTableTh').html(res);
    }
    exports('assets/statistics/MaintenanceSummary', {});
});

