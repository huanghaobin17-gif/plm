layui.define(function(exports){
    layui.use(['carousel', 'echarts', 'form', 'table', 'laydate', 'suggest', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , suggest = layui.suggest
            , table = layui.table, tablePlug = layui.tablePlug;
        suggest.search();
        laydate.render({
            elem: '#repairAnalysisS'
        });
        laydate.render({
            elem: '#repairAnalysisE'
        });
        form.render();
        var tdiv = $('.div-chart-show-pic');
        var ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.div-chart-show-pic-chart').attr('id');
            if(tmpid){
                ids.push(tmpid);
            }
        });
        $.each(ids,function (index,item) {
            var idname = echarts.init(document.getElementById(item));
            idname.clear();
            idname.showLoading();
        });
        table.render({
            elem: '#departFeeLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '维修费用分析列表'
            ,url: repairAnalysis //数据接口
            ,where: {
                action:'getLists',
                type:'department'
            } //如果无需传递额外参数，可不加该参数
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,totalRow: true

            ,defaultToolbar: ['exports']
            ,page: false //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    unresize: true,
                    totalRowText: '合计',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'department', fixed: 'left', title: '科室名称', width: 200, align: 'center'},
                {field: 'repair_num', align: 'center', width: 120, totalRow: true,totalNums:0, sort: true, title: '维修次数'},
                {field: 'part_num', align: 'center', width: 120, sort: true, totalRow: true,totalNums:0, title: '配件数量'},
                {field: 'part_total_price', align: 'center', sort: true, width: 120, totalRow: true,totalNums:0, title: '配件费用'}
                , {field: 'repair_fee', width: 140, title: '实际维修费用', sort: true, totalRow: true,totalNums:0, align: 'center'},
                {field: 'num_ratio', width: 120, title: '次数占比', totalRow: true, align: 'center'},
                {field: 'fee_ratio', title: '费用占比', totalRow: true, minWidth: 140, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                var departFee = echarts.init(document.getElementById('departFee'));
                departFee.clear();
                departFee.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    departFee.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b}: {c} ({d}%)"
                        },
                        legend: {
                            type: 'scroll',
                            orient: 'vertical',
                            top:'2%',
                            x: 'right',
                            data:res['charData']['legend_data']
                        },
                        series: [
                            {
                                name:'科室维修费用',
                                type:'pie',
                                radius: ['50%', '70%'],
                                center:['40%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '20',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:res['charData']['series_data']
                            }
                        ]
                    })
                }else{
                    departFee.setOption({
                        color:"#E6E6E6",
                        title : {
                            subtext: '暂无相关数据',
                            x:'center'
                        },
                        series: [
                            {
                                type:'pie',
                                radius: ['65%', '80%'],
                                center:['50%', '55%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '30',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:[
                                    {value:0}

                                ]
                            }
                        ]
                    });
                }
            }
        });

        //监听排序
        table.on('sort(departFeeData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('departFeeLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        table.render({
            elem: '#faultTypeLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: repairAnalysis //数据接口
            ,where: {
                action:'getLists'
                ,type:'fault_type'
            } //如果无需传递额外参数，可不加该参数
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,totalRow: true

            ,defaultToolbar: ['exports']
            ,page: false //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    totalRowText: '合计',
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'fault_type_name', fixed: 'left', title: '故障类型名称', width: 200, align: 'center'},
                {field: 'repair_num', align: 'center', width: 120, totalRow: true,totalNums:0, title: '维修次数'}
                , {field: 'repair_fee', width: 140, title: '实际维修费用', totalRow: true,totalNums:0, align: 'center'},
                {field: 'num_ratio', width: 120, title: '次数占比', totalRow: true, align: 'center'},
                {field: 'fee_ratio', title: '费用占比', totalRow: true, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                var faultType = echarts.init(document.getElementById('faultType'));
                faultType.clear();
                faultType.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    faultType.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b}: {c} ({d}%)"
                        },
                        legend: {
                            type: 'scroll',
                            orient: 'vertical',
                            top:'2%',
                            x: 'right',
                            data:res['charData']['legend_data']
                        },
                        series: [
                            {
                                name:'故障类型维修费用',
                                type:'pie',
                                radius: ['50%', '70%'],
                                center:['40%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '20',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:res['charData']['series_data']
                            }
                        ]
                    })
                }else{
                    faultType.setOption({
                        color:"#E6E6E6",
                        title : {
                            subtext: '暂无相关数据',
                            x:'center'
                        },
                        series: [
                            {
                                type:'pie',
                                radius: ['65%', '80%'],
                                center:['50%', '55%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '30',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:[
                                    {value:0}

                                ]
                            }
                        ]
                    });
                }
            }
        });
        table.render({
            elem: '#cateFeeLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: repairAnalysis //数据接口
            ,where: {
                action:'getLists'
                ,type:'category'
            } //如果无需传递额外参数，可不加该参数
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,totalRow: true

            ,defaultToolbar: ['exports']
            ,page: false //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    totalRowText: '合计',
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'category',
                    fixed: 'left',
                    title: '分类名称',
                    width: 200,
                    align:'center'
                },
                {
                    field: 'repair_num',
                    align:'center',
                    width: 120,
                    sort: true,
                    totalRow: true,
                    totalNums:0,
                    title: '维修次数'
                },
                {
                    field: 'part_num',
                    align:'center',
                    width: 120,
                    sort: true,
                    totalRow: true,
                    totalNums:0,
                    title: '配件数量'
                },
                {
                    field: 'part_total_price',
                    align:'center',
                    sort: true,
                    width: 120,
                    totalRow: true,
                    totalNums:0,
                    title: '配件费用'
                }
                ,{
                    field: 'repair_fee',
                    width: 140,
                    title: '实际维修费用',
                    sort: true,
                    totalRow: true,
                    totalNums:0,
                    align:'center'
                },
                {
                    field: 'num_ratio',
                    width: 120,
                    totalRow: true,
                    title: '次数占比',
                    align:'center'
                },
                {
                    field: 'fee_ratio',
                    title: '费用占比',
                    minWidth: 140,
                    totalRow: true,
                    align:'center'
                }
            ]]
            , done: function (res, curr, count) {
                var cateFee = echarts.init(document.getElementById('cateFee'));
                cateFee.clear();
                cateFee.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    cateFee.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b}: {c} ({d}%)"
                        },
                        legend: {
                            type: 'scroll',
                            orient: 'vertical',
                            top:'2%',
                            x: 'right',
                            width:'160px',
                            data:res['charData']['legend_data']
                        },
                        series: [
                            {
                                name:'设备分类维修费用',
                                type:'pie',
                                radius: ['50%', '70%'],
                                center:['40%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '20',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:res['charData']['series_data']
                            }
                        ]
                    })
                }else{
                    cateFee.setOption({
                        color:"#E6E6E6",
                        title : {
                            subtext: '暂无相关数据',
                            x:'center'
                        },
                        series: [
                            {
                                type:'pie',
                                radius: ['65%', '80%'],
                                center:['50%', '55%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '30',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:[
                                    {value:0}

                                ]
                            }
                        ]
                    });
                }
            }
        });
        //监听排序
        table.on('sort(cateFeeData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('cateFeeLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //监听搜索
        form.on('submit(repairAnalysisSearch)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            if(gloabOptions.startDate && gloabOptions.endDate){
                if(gloabOptions.startDate > gloabOptions.endDate){
                    layer.msg("请输入合理的日期",{icon : 2,time:1000});
                    return false;
                }
            }
            table.reload('departFeeLists', {
                url: repairAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            table.reload('faultTypeLists', {
                url: repairAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            table.reload('cateFeeLists', {
                url: repairAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

    });
    exports('statistics/statisRepair/repairAnalysis', {});
});
