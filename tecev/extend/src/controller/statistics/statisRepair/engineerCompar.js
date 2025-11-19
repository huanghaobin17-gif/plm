layui.define(function(exports){
    layui.use(['carousel', 'echarts','form','table','laydate','suggest'], function() {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , suggest = layui.suggest
            , table = layui.table, tablePlug = layui.tablePlug;
        suggest.search();
        laydate.render({
            elem: '#engineerComparS'
        });
        laydate.render({
            elem: '#engineerComparE'
        });
        form.render();
        var tdiv = $('.div-chart-show-pic-workjob');
        var ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.div-chart-show-workjob').attr('id');
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
            elem: '#engineerComparLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '工程师工作量对比'
            ,url: engineerCompar //数据接口
            ,where: {
                action:'getLists'
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
                    field: 'engineer',
                    fixed: 'left',
                    title: '工程师名称',
                    width: '14%',
                    align:'center'
                },
                {
                    field: 'repair_num',
                    align:'center',
                    width: '12%',
                    sort: true,
                    totalRow: true,
                    totalNums:0,
                    title: '维修次数'
                },
                {
                    field: 'repair_time',
                    align:'center',
                    width: '12%',
                    sort: true,
                    totalRow: true,
                    title: '维修时长'
                },
                {
                    field: 'avg_time',
                    align:'center',
                    width: '12%',
                    totalRow: true,
                    title: '平均时长'
                }
                ,{
                    field: 'over_status_num',
                    width: '11%',
                    title: '修复次数',
                    totalRow: true,
                    totalNums:0,
                    align:'center'
                }
                ,{
                    field: 'not_over_status_num',
                    width: '11%',
                    title: '未修复次数',
                    totalRow: true,
                    totalNums:0,
                    align:'center'
                },
                {
                    field: 'not_check_num',
                    width: '11%',
                    title: '未验收次数',
                    totalRow: true,
                    totalNums:0,
                    align:'center'
                },
                {
                    field: 'repair_rate',
                    title: '修复占比',
                    totalRow: true,
                    align:'center'
                }
            ]]
            , done: function (res, curr, count) {
                if(!$.isEmptyObject(res.charData)){
                    $.each(res.charData,function (index1,item1) {
                        var idname = echarts.init(document.getElementById(index1));
                        idname.clear();
                        idname.hideLoading();
                        idname.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: "{a} <br/>{b}: {c} ({d}%)"
                            },
                            legend: {
                                type: 'scroll',
                                orient: 'vertical',
                                top:'2%',
                                x: 'right',
                                data:item1['legend_data']
                            },
                            series: [
                                {
                                    name:item1['title'],
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
                                    data:item1['series_data']
                                }
                            ]
                        });
                    });
                }else{
                    $.each(ids,function (index2,item2) {
                        var idname = echarts.init(document.getElementById(item2));
                        idname.clear();
                        idname.hideLoading();
                        idname.setOption({
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
                    });
                }
            }
        });

        //监听排序
        table.on('sort(engineerComparData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('engineerComparLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //监听搜索
        form.on('submit(engineerComparSearch)', function(data){
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
            table.reload('engineerComparLists', {
                url: engineerCompar
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

    });
    exports('statistics/statisRepair/engineerCompar', {});
});
