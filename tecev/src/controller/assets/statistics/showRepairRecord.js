


layui.define(function(exports){
    layui.use([ 'form'], function() {
        var form = layui.form, $ = layui.jquery;
        form.verify({
            priceMin: function (value) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            },
            priceMax: function (value) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            }
        });

        //数据导出
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            var params={};
            params.assid = $('input[name="assid"]').val();
            params.base64Data = myChart.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#404a59'
            });
            postDownLoadFile({
                url:url,
                data:params,
                method:'POST'
            });
            return false;
        });
    });
    /**
     * post请求无法直接发送请求下载excel文档，是因为我们在后台改变了响应头的内容：
     * Content-Type: application/vnd.ms-excel
     * 致post请求无法识别这种消息头,导致无法直接下载。
     * 解决方法：
     * 改成使用form表单提交方式即可
     */
    var postDownLoadFile = function (options) {
        var config = $.extend(true, { method: 'POST' }, options);
        var $iframe = $('<iframe id="down-file-iframe" />');
        var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
        $form.attr('action', config.url);
        for (var key in config.data) {
            $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
        }
        $(document.body).append($iframe);
        $iframe.append($form);
        $form[0].submit();
        $iframe.remove();
    };
// 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('main'));
    var colors = ['#5793f3', '#d14a61', '#675bba'];
    $(function(){
        var params = {};
        params.assid = $('input[name="assid"]').val();
        params.type='RepairRecord';
        showChart(params);
    });
    function showChart(params) {
        myChart.clear();
        //显示一个简单的加载动画
        myChart.showLoading();
        // 异步加载数据
        $.post(admin_name+'/Statistics/assetsSummary.html',params).done(function (resData) {
            //隐藏加载动画
            myChart.hideLoading();
            var seriesData = resData.seriesData;
            var len = resData.seriesStyle.length;
            $.each(resData.seriesStyle,function(index,v){
                if(index == 0){
                    v.symbolSize = function (val) {
                        return val[1] / 0.2;
                    };
                }else if(index > 0 && index < (len/2)){
                    v.symbolSize = function (val) {
                        return val[1] / 0.3;
                    };
                }else{
                    v.symbolSize = function (val) {
                        return val[1] / 0.15;
                    };
                    v.data = seriesData.sort(function (a, b) {
                        return b[1] - a[1];
                    }).slice(0, 12);
                }
            });
            if(resData.lists){
                showTable(resData.lists);
                showConditions(resData.reportTips);
                showTitle(resData.reportTitle);
                // 填入数据
                myChart.setOption({
                    backgroundColor: '#404a59',
                    title: {
                        top: 30,
                        text: resData.reportTitle,
                        subtext: resData.reportTips,
                        left: 'center',
                        textStyle: {
                            color: '#fff'
                        }
                    },
                    toolbox: {
                        show : true,
                        feature : {
                            //dataView : {show: true, readOnly: false},
                            //magicType : {show: true, type: ['pie', 'bar']},
                            //restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    tooltip : {
                        trigger: 'item'
                    },
                    legend: {
                        top: '30',
                        left: '100',
                        data:['维修次数', 'Top 12'],
                        textStyle: {
                            color: '#fff'
                        }
                    },
                    calendar: resData.calendarStyle,
                    series :resData.seriesStyle
                });
            }
        });
    }
    function showTable(res) {
        var html = '';
        var tPart = 0;
        var tActual = 0;
        var wHours = 0;
        $('.conData').html('');
        $.each(res,function(e,val){
            tPart += parseInt(val.partNum);
            tActual += parseFloat(val.actualPrice);
            wHours += parseFloat(val.totalHours);
            html += '<tr>' +
                '<td>'+(e+1)+'</td>\n' +
                '<td>'+val.repnum+'</td>\n' +
                '<td>'+val.applicant+'</td>\n' +
                '<td>'+val.applicant_time+'</td>\n' +
                '<td>'+val.repairEngineer+'</td>\n' +
                '<td>'+val.partNumUrl+'</td>\n' +
                '<td>'+val.actualPrice+'</td>\n' +
                '<td>'+val.totalHours+'</td>\n' +
                '<td>'+val.isComplete+'</td>\n' +
                '<td>'+val.overdate+'</td>\n' +
                '</tr>';
        });
        html += '<tr>' +
            '<td colspan="2" style="text-align: right;color:red;">合计：</td>\n' +
            '<td class="total-font-color">--</td>\n' +
            '<td class="total-font-color">--</td>\n' +
            '<td class="total-font-color">--</td>\n' +
            '<td class="total-font-color">'+tPart+'</td>\n' +
            '<td class="total-font-color">'+tActual+'</td>\n' +
            '<td class="total-font-color">'+wHours+'</td>\n' +
            '<td class="total-font-color">--</td>\n' +
            '<td class="total-font-color">--</td>\n' +
            '</tr>';
        $('.conData').append(html);
    }
    function showConditions(res) {
        $('.reportConditions').html(res);
    }
    function showTitle(res) {
        $('.reportTitle').html(res);
    }
//显示维修配件信息
    function showPartInfo(e){
        var url = admin_name+'/Statistics/showParts?repid='+$(e).attr('data-id');
        top.layer.open({
            type: 2,
            title:'维修设备配件信息',
            shade: 0,
            anim:2,
            offset: 'r',//弹窗位置固定在右边
            scrollbar:false,
            area: ['75%', '100%'],
            closeBtn: 1,
            content: [url]
        });
    }


// [
//     {
//         top: 100,
//         left: 'center',
//         range: ['2016-01-01', '2016-12-31'],
//         splitLine: {
//             show: true,
//             lineStyle: {
//                 color: '#000',
//                 width: 4,
//                 type: 'solid'
//             }
//         },
//         yearLabel: {
//             formatter: '{start}  年',
//             textStyle: {
//                 color: '#fff'
//             }
//         },
//         dayLabel: {
//             textStyle: {
//                 color: '#B5B3B3'
//             },
//             nameMap: 'cn'
//         },
//         monthLabel: {
//             textStyle: {
//                 color: '#B5B3B3'
//             },
//             nameMap: 'cn'
//         },
//         itemStyle: {
//             normal: {
//                 color: '#323c48',
//                 borderWidth: 1,
//                 borderColor: '#111'
//             }
//         }
//     },
//     {
//         top: 300,
//         left: 'center',
//         range: ['2017-01-01', '2017-12-31'],
//         splitLine: {
//             show: true,
//             lineStyle: {
//                 color: '#000',
//                 width: 4,
//                 type: 'solid'
//             }
//         },
//         yearLabel: {
//             formatter: '{start}  年',
//             textStyle: {
//                 color: '#fff'
//             }
//         },
//         dayLabel: {
//             textStyle: {
//                 color: '#B5B3B3'
//             },
//             nameMap: 'cn'
//         },
//         monthLabel: {
//             textStyle: {
//                 color: '#B5B3B3'
//             },
//             nameMap: 'cn'
//         },
//         itemStyle: {
//             normal: {
//                 color: '#323c48',
//                 borderWidth: 1,
//                 borderColor: '#111'
//             }
//         }
//     }
// ],

    exports('controller/assets/statistics/showRepairRecord', {});
});