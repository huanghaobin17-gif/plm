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
            elem: '#pur_year' //指定元素
            ,type: 'year'
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
            elem: '#departAssetsNumsLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '维修费用分析列表'
            ,url: purAnalysis //数据接口
            ,where: {
                action:'getLists',
                type:'assetsNums',
                year:year
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
                {field: 'nums', align: 'center', width: 200, totalRow: true,totalNums:0, sort: true, title: '设备数量'},
                {field: 'num_ratio', title: '数量占比', totalRow: true, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                var departAssetsNums = echarts.init(document.getElementById('departAssetsNums'));
                departAssetsNums.clear();
                departAssetsNums.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    departAssetsNums.setOption({
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
                                name:'科室采购设备数量',
                                type:'pie',
                                radius: ['65%', '80%'],
                                center:['35%', '60%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '18',
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
                    departAssetsNums.setOption({
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
        table.on('sort(departAssetsNumsData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('departAssetsNumsLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        table.render({
            elem: '#departAssetsFeeLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: purAnalysis //数据接口
            ,where: {
                action:'getLists'
                ,type:'assetsFee',
                year:year
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
                {field: 'department', fixed: 'left', title: '科室名称', width: 200, align: 'center'},
                {field: 'total_price', align: 'center', width: 200, totalRow: true, title: '设备费用'},
                {field: 'fee_ratio', title: '费用占比', totalRow: true, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                var departAssetsFee = echarts.init(document.getElementById('departAssetsFee'));
                departAssetsFee.clear();
                departAssetsFee.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    departAssetsFee.setOption({
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
                                name:'科室采购设备费用',
                                type:'pie',
                                radius: ['65%', '80%'],
                                center:['35%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '18',
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
                    departAssetsFee.setOption({
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
            elem: '#assetsBuyTypeLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,url: purAnalysis //数据接口
            ,where: {
                action:'getLists'
                ,type:'buyType',
                year:year
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
                {field: 'buy_type_name', fixed: 'left', title: '购置类型', width: 200, align: 'center'},
                {field: 'buy_type_nums', align: 'center', width: 200, sort: true, totalRow: true,totalNums:0, title: '设备数量'},
                {field: 'num_ratio', title: '数量占比', totalRow: true, align: 'center'}, {
                    field: 'buy_type_price',
                    align: 'center',
                    width: 200,
                    totalRow: true,
                    title: '购置费用（元）'
                },
                {field: 'price_ratio', title: '费用占比', totalRow: true, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                var assetsBuyType = echarts.init(document.getElementById('assetsBuyType'));
                assetsBuyType.clear();
                assetsBuyType.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    assetsBuyType.setOption({
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
                                name:'设备购置类型',
                                type:'pie',
                                radius: ['65%', '80%'],
                                center:['35%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: false,
                                        position: 'center'
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '18',
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
                    assetsBuyType.setOption({
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
        form.on('submit(purAnalysisSearch)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            table.reload('departAssetsNumsLists', {
                url: purAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            table.reload('departAssetsFeeLists', {
                url: purAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            table.reload('assetsBuyTypeLists', {
                url: purAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

    });
    exports('statistics/statisPurchases/purAnalysis', {});
});
