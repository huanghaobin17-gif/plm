var admin = layui.admin;
layui.define(function(exports){
    layui.use(['carousel', 'echarts','form'], function() {
        var $ = layui.$
            , form = layui.form
            , carousel = layui.carousel
            , echarts = layui.echarts;
        form.render();
        var tdiv = $('#pic-desc').find('div');
        var chart_tdiv = $('#more_chart').find('.layui-col-xs6');
        var ids = [];
        var chart_ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.index-pic-width').attr('id');
            if(tmpid){
                if($("#"+tmpid).parent().attr('class').indexOf('on_use') > 0){
                    ids.push(tmpid);
                }
            }
        });
        $.each(chart_tdiv,function (index,item) {
            if($(this).attr('class').indexOf('on_use') > 0){
                //再用的才获取数据
                var tmpid = $(this).find('.div-chart-show-workjob').attr('id');
                if(tmpid){
                    chart_ids.push(tmpid);
                }
            }
        });
        get_assets_data();
        function get_assets_data() {
            $.each(ids,function (index,item) {
                var idname = echarts.init(document.getElementById(item));
                idname.clear();
                idname.showLoading();
            });
            var result = {};
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":"getAssetsData","params":ids},
                dataType: "json",
                async: false,
                success: function (data) {
                    if (data.status == 1) {
                        $.each(ids,function (index,item) {
                            var idname = echarts.init(document.getElementById(item));
                            idname.hideLoading();
                        });
                        result = data;
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
            if($.inArray('repair_scrap_assets',ids) < 0){
                ids.push('repair_scrap_assets');
            }
            $.each(ids,function (index,item) {
                var idname = echarts.init(document.getElementById(item));
                idname.clear();
                if(result.show_type == 'annular'){
                    idname.setOption({
                        tooltip: {
                            trigger: 'item',
                            // formatter: "{a} <br/>{b}"
                            formatter: "{a} <br/>{b} "
                        },
                        legend: {
                            orient: 'horizontal',
                            x: 'center',
                            y: 'bottom',
                            data:result['data'][item]
                        },
                        series: [
                            {
                                name:result['data']['title'][item],
                                type:'pie',
                                radius: ['55%', '65%'],
                                center:['50%', '33%'],
                                avoidLabelOverlap: false,
                                // label: result.label,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'center',
                                        formatter : function(param){
                                            if(item == 'repair_scrap_assets'){
                                                if(param.value > result['data']['max'][item]-1){
                                                    return param['data']['title']+' '+param.value+'\n'+param['data']['zhanbi']+':'+param['data']['ratio'];
                                                }
                                                else{
                                                    return '';
                                                }
                                            }else{
                                                if(param.value == result['data']['max'][item]){
                                                    return param['data']['title']+' '+param.value+'\n'+param['data']['zhanbi']+':'+param['data']['ratio'];
                                                }
                                                else{
                                                    return '';
                                                }
                                            }

                                        },
                                        fontSize:'18'
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
                                data: result['data'][item]
                            }
                        ]
                    });

                }else{
                    idname.setOption({
                        tooltip: {
                            trigger: 'item',
                            // formatter: "{a} <br/>{b}"
                            formatter: "{a} <br/>{b} "
                        },
                        legend: {
                            orient: 'vertical',
                            x: 'center',
                            y: 'bottom',
                            data:result['data'][item]
                        },
                        label: {
                            normal: {
                                //position: 'inner'
                                position: [10, 10]
                            }
                        },
                        series: [
                            {
                                name:result['data']['title'][item],
                                type:'pie',
                                radius : '60%',
                                center:['50%', '38%'],
                                label: {
                                    normal: {
                                        show: false
                                    }
                                },
                                // labelLine: {
                                //     normal: {
                                //         show: true
                                //     }
                                // },
                                data: result['data'][item]
                            }
                        ]
                    });
                }

                /*$.each(result['data'][item],function (index1,item1) {
                   var html = '';
                   html += '<li>'+item1.title+'设备：'+item1.value+'</li>';
                   $("#"+item).parent().find('ul').append(html);
                });*/
            });
        }

        //获取设备维修情况
        var id_name = 'target_chart_assets_repair';
        var year = 3;
        var count_type = 'free';
        var show_type = 'line';
        get_assets_data_chart(id_name,year,count_type,show_type);
        function get_assets_data_chart(id_name,year,count_type,show_type) {
            var idname = echarts.init(document.getElementById(id_name));
            idname.clear();
            idname.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name,"year":year,"count_type":count_type,"show_type":show_type},
                dataType: "json",
                async: false,
                success: function (charData) {
                    if(!$.isEmptyObject(charData)){
                        var idname = echarts.init(document.getElementById(id_name));
                        idname.hideLoading();
                        idname.setOption({
                            // color: ['#3398DB'],
                            title: {
                                text: charData['title']
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : charData['tooltip']['axisPointer']['type']        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            legend: {
                                data:charData['legend']
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            toolbox: {
                                feature: {
                                    saveAsImage: {}
                                }
                            },
                            xAxis: {
                                type: 'category',
                                boundaryGap: false,
                                //axisLabel: {interval:1},//X坐标轴刻度间隔一个显示
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                type: 'value',
                                name:charData['yAxis']['name']
                            },
                            series: charData['series']
                        });
                    }
                }
            });
        }
        form.on('submit(target_chart_assets_repair)', function (data) {
            if($(this).attr('class').indexOf('layui-btn-normal') > 0){
                //当前正在显示的图表，无需重复获取数据
                return false;
            }
            var year = $(this).attr('data-year');
            var count_type = $(this).parent().parent().find("select[name='target_count_type']").val();
            var show_type = 'line';
            get_assets_data_chart('target_chart_assets_repair',year,count_type,show_type);
            var buts = $(this).parent().find('button');
            $.each(buts,function (index,val) {
                $(val).attr('class','layui-btn layui-btn-sm layui-btn-primary')
            });
            $(this).attr('class','layui-btn layui-btn-sm layui-btn-normal');
            return false;
        });
        form.on('submit(target_chart_depart_repair)', function (data) {
            if($(this).attr('class').indexOf('layui-btn-normal') > 0){
                //当前正在显示的图表，无需重复获取数据
                return false;
            }
            var year2 = $(this).attr('data-year');
            get_depart_repair_free('target_chart_depart_repair',year2);
            var buts = $(this).parent().find('button');
            $.each(buts,function (index,val) {
                $(val).attr('class','layui-btn layui-btn-sm layui-btn-primary')
            });
            $(this).attr('class','layui-btn layui-btn-sm layui-btn-normal');
            return false;
        });
        //切换统计类型
        $.each(chart_ids,function (index,item) {
            form.on('select('+item+')',function (data) {
                chang_type(item);
            });
        });
        function chang_type(item) {
            switch (item){
                case 'target_chart_assets_repair':
                    var buts = $('.'+item).find('button');
                    $.each(buts,function (index,val) {
                        if($(val).attr('class').indexOf('layui-btn-normal') > 0){
                            year = $(val).attr('data-year');
                        }
                    });
                    var count_type = $("select[name='target_count_type']").val();
                    var show_type = 'line';
                    get_assets_data_chart(item,year,count_type,show_type);
                    break;
                case 'target_chart_assets_add':
                    var year3 = $("select[name='change_year_add']").val();
                    var show_type3 = $("select[name='target_show_type_add']").val();
                    get_assets_add(item,year3,show_type3);
                    break;
                case 'target_chart_assets_scrap':
                    var year4 = $("select[name='change_year_scrap']").val();
                    var show_type4 = $("select[name='target_show_type_scrap']").val();
                    get_assets_scrap(item,year4,show_type4);
                    break;
                case 'target_chart_assets_adverse':
                    var year5 = $("select[name='change_year_adverse']").val();
                    var show_type5 = $("select[name='target_show_type_adverse']").val();
                    get_assets_adverse(item,year5,show_type5);
                    break;
                case 'target_chart_assets_purchases':
                    var year6 = $("select[name='change_year_purchases']").val();
                    var show_type6 = $("select[name='target_show_type_purchases']").val();
                    get_assets_purchases(item,year6,show_type6);
                    break;
                case 'target_chart_assets_benefit':
                    var year7 = $("select[name='change_year_benefit']").val();
                    var count_type7 = $("select[name='target_show_type_benefit']").val();
                    get_assets_benefit(item,year7,count_type7);
                    break;
                case 'target_chart_assets_move':
                    var year8 = $("select[name='change_year_move']").val();
                    //var count_type8 = $("select[name='target_show_type_move']").val();
                    var count_type8 = 'trend';
                    get_assets_move(item,year8,count_type8);
                    break;
                case 'target_chart_assets_patrol':
                    var year9 = $("select[name='change_year_patrol']").val();
                    var count_type9 = $("select[name='target_show_type_patrol']").val();
                    get_assets_patrol(item,year9,count_type9);
                    break;
            }
        }

        function no_data(idname) {
            idname.setOption({
                color:"#E6E6E6",
                title : {
                    subtext: '暂无相关数据',
                    x:'center'
                },
                series: [
                    {
                        type:'pie',
                        center: ['45%', '60%'],
                        radius: ['60%', '80%'],
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


        //科室维修费用分析
        var id_name2 = 'target_chart_depart_repair';
        if($.inArray(id_name2,chart_ids) >= 0){
            var year2 = 3;
            get_depart_repair_free(id_name2,year2);
        }
        function get_depart_repair_free(id_name2,year2) {
            var idname = echarts.init(document.getElementById(id_name2));
            idname.clear();
            idname.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name2,"year":year2},
                dataType: "json",
                async: false,
                success: function (charData) {
                    var idname = echarts.init(document.getElementById(id_name2));
                    idname.hideLoading();
                    if(!$.isEmptyObject(charData)){
                        idname.setOption({
                            // color: ['#3398DB'],
                            title : {
                                subtext: '单位：元',
                                x:'left'
                            },
                            tooltip : {
                                trigger: 'item',
                                formatter: "{a} <br/>{b} : {c} ({d}%)"
                            },
                            legend: {
                                type: 'scroll',
                                orient: 'vertical',
                                x:'right',
                                right: 5,
                                top: 20,
                                bottom: 20,
                                data: charData['legend']['data'],
                                selected: charData['legend']['selected']
                            },
                            //series :charData['series']
                            series :[
                                {
                                    name: '科室维修费用',
                                    type: 'pie',
                                    radius : '80%',
                                    center: ['42%', '55%'],
                                    data:charData['series']['data'],
                                    itemStyle: {
                                        emphasis: {
                                            shadowBlur: 10,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    }
                                }
                            ]
                        });
                    }else{
                        no_data(idname);
                    }
                }
            });
        }

        //设备增加情况
        var id_name3 = 'target_chart_assets_add';
        if($.inArray(id_name3,chart_ids) >= 0){
            var year3 = current_year;
            var count_type3 = 'trend';
            get_assets_add(id_name3,year3,count_type3);
        }
        function get_assets_add(id_name3,year3,count_type3) {
            var init_chart_3 = echarts.init(document.getElementById(id_name3));
            init_chart_3.clear();
            init_chart_3.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name3,"year":year3,"count_type":count_type3},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_3.hideLoading();
                    if(count_type3 == 'trend'){
                        init_chart_3.setOption({
                            color: ['#1E9FFF'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            legend: {
                                data:['新增设备']
                            },
                            xAxis: {
                                type: 'category',
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                name:'单位：台',
                                type: 'value',
                                boundaryGap: [0, 3]
                            },
                            series: [{
                                name:'新增设备',
                                type: 'line',
                                data: charData['series']['data'],
                                smooth: true
                            }]
                        });
                    }else if(count_type3 == 'depart'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_3.setOption({
                                color: ['#0F9911','#6C449D', '#305378','#BB4472','#EEDD78','#8DC1A9'],
                                title : {
                                    subtext: '单位：台',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室设备增加数量',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_3);
                        }
                    }
                }
            });
        }

        //设备报废情况
        var id_name4 = 'target_chart_assets_scrap';
        if($.inArray(id_name4,chart_ids) >= 0){
            var year4 = current_year;
            var count_type4 = 'trend';
            get_assets_scrap(id_name4,year4,count_type4);
        }
        function get_assets_scrap(id_name4,year4,count_type4) {
            var init_chart_4 = echarts.init(document.getElementById(id_name4));
            init_chart_4.clear();
            init_chart_4.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name4,"year":year4,"count_type":count_type4},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_4.hideLoading();
                    if(count_type4 == 'trend'){
                        init_chart_4.setOption({
                            color: ['#DEA753'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            legend: {
                                data:['报废设备']
                            },
                            xAxis: {
                                type: 'category',
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                name:'单位：台',
                                type: 'value',
                                boundaryGap: [0, 1]
                            },
                            series: [{
                                name:'报废设备',
                                type: 'line',
                                data: charData['series']['data'],
                                smooth: true
                            }]
                        });
                    }else if(count_type4 == 'depart'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_4.setOption({
                                color: ['#73B9BC','#E69D87', '#73A373','#EA7E53','#EEDD78','#8DC1A9'],
                                title : {
                                    subtext: '单位：台',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室设备报废数量',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_4);
                        }
                    }
                }
            });
        }

        //设备不良事件情况
        var id_name5 = 'target_chart_assets_adverse';
        if($.inArray(id_name5,chart_ids) >= 0){
            var year5 = current_year;
            var count_type5 = 'trend';
            get_assets_adverse(id_name5,year5,count_type5);
        }
        function get_assets_adverse(id_name5,year5,count_type5) {
            var init_chart_5 = echarts.init(document.getElementById(id_name5));
            init_chart_5.clear();
            init_chart_5.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name5,"year":year5,"count_type":count_type5},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_5.hideLoading();
                    if(count_type5 == 'trend'){
                        init_chart_5.setOption({
                            color: ['#782762'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            legend: {
                                data:['不良事件']
                            },
                            xAxis: {
                                type: 'category',
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                name:'单位：件',
                                type: 'value',
                                boundaryGap: [0, 3]
                            },
                            series: [{
                                name:'不良事件',
                                type: 'line',
                                data: charData['series']['data'],
                                smooth: true
                            }]
                        });
                    }else if(count_type5 == 'depart'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_5.setOption({
                                color: ['#8378EA','#E7BCF3', '#37A2DA','#FFDB5C','#FB7293','#FF9F7F'],
                                title : {
                                    subtext: '单位：件',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室设备不良事件',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_5);
                        }
                    }
                }
            });
        }

        //设备采购费用情况
        var id_name6 = 'target_chart_assets_purchases';
        if($.inArray(id_name6,chart_ids) >= 0){
            var year6 = current_year;
            var count_type6 = 'trend';
            get_assets_purchases(id_name6,year6,count_type6);
        }
        function get_assets_purchases(id_name6,year6,count_type6) {
            var init_chart_6 = echarts.init(document.getElementById(id_name6));
            init_chart_6.clear();
            init_chart_6.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name6,"year":year6,"count_type":count_type6},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_6.hideLoading();
                    if(count_type6 == 'trend'){
                        init_chart_6.setOption({
                            color: ['#439522'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            legend: {
                                data:['设备采购费用']
                            },
                            xAxis: {
                                type: 'category',
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                name:'单位：元',
                                type: 'value',
                                boundaryGap: [0, 0.2]
                            },
                            series: [{
                                name:'设备采购费用',
                                type: 'line',
                                data: charData['series']['data'],
                                smooth: true
                            }]
                        });
                    }else if(count_type6 == 'free'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_6.setOption({
                                color: ['#DA5E58','#2F4554', '#29914A','#D48265','#91C7AE','#CA8622','#3FA7DC'],
                                title : {
                                    subtext: '单位：元',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室采购支出',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_6);
                        }
                    }else if(count_type6 == 'nums'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_6.setOption({
                                color: ['#DA5E58','#2F4554', '#29914A','#D48265','#91C7AE','#CA8622','#3FA7DC'],
                                title : {
                                    subtext: '单位：台',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室采购数量',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_6);
                        }
                    }
                }
            });
        }

        //设备效益情况
        var id_name7 = 'target_chart_assets_benefit';
        if($.inArray(id_name7,chart_ids) >= 0){
            var year7 = current_year;
            var count_type7 = 'trend';
            get_assets_benefit(id_name7,year7,count_type7);
        }
        function get_assets_benefit(id_name7,year7,count_type7) {
            var init_chart_7 = echarts.init(document.getElementById(id_name7));
            init_chart_7.clear();
            init_chart_7.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name7,"year":year7,"count_type":count_type7},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_7.hideLoading();
                    if(count_type7 == 'trend'){
                        init_chart_7.setOption({
                            color: ['#439522'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : charData['tooltip']['axisPointer']['type']        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            legend: {
                                data:charData['legend']['data']
                            },
                            xAxis: {
                                type: 'category',
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                name:'单位：万元',
                                type: 'value',
                                boundaryGap: [0, 0.2]
                            },
                            series: [
                                charData['series']['total_income'],
                                charData['series']['total_cost'],
                                charData['series']['total_profit']
                            ]
                        });
                    }else if(count_type7 == 'income'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_7.setOption({
                                color: ['#439522','red', '#1D85A3','#EE6767','blueviolet','#E5C553'],
                                title : {
                                    subtext: '单位：万元',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室收入',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_7);
                        }
                    }else if(count_type7 == 'expenditure'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_7.setOption({
                                color: ['#439522','red', '#1D85A3','#EE6767','blueviolet','#E5C553'],
                                title : {
                                    subtext: '单位：万元',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data: charData['legend']['data']
                                },
                                series : [
                                    {
                                        name: '费用支出',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data:charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_7);
                        }
                    }
                }
            });
        }

        //设备转移情况
        var id_name8 = 'target_chart_assets_move';
        if($.inArray(id_name8,chart_ids) >= 0){
            var year8 = current_year;
            var count_type8 = 'trend';
            get_assets_move(id_name8,year8,count_type8);
        }
        function get_assets_move(id_name8,year8,count_type8) {
            var init_chart_8 = echarts.init(document.getElementById(id_name8));
            init_chart_8.clear();
            init_chart_8.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name8,"year":year8,"count_type":count_type8},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_8.hideLoading();
                    if(count_type8 == 'trend'){
                        init_chart_8.setOption({
                            color: ['#782762'],
                            tooltip: {
                                trigger: 'axis'
                            },
                            legend: {
                                data:['设备转科','设备外调','设备借入','设备借出']
                            },
                            grid: {
                                left: '3%',
                                right: '4%',
                                bottom: '3%',
                                containLabel: true
                            },
                            xAxis: {
                                type: 'category',
                                boundaryGap: false,
                                data: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
                            },
                            yAxis: {
                                name:'单位：台',
                                type: 'value',
                                boundaryGap: [0, 1]
                            },
                            series: [
                                {
                                    name:'设备转科',
                                    type:'line',
                                    smooth: true,
                                    data:charData['transfer_data']
                                },
                                {
                                    name:'设备外调',
                                    type:'line',
                                    smooth: true,
                                    data:charData['outside_data']
                                },
                                {
                                    name:'设备借入',
                                    type:'line',
                                    smooth: true,
                                    data:charData['borrow_in_data']
                                },
                                {
                                    name:'设备借出',
                                    type:'line',
                                    smooth: true,
                                    data:charData['borrow_out_data']
                                }
                            ]
                        });
                    }else if(count_type8 == 'depart'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_8.setOption({
                                color: ['#8378EA','#E7BCF3', '#37A2DA','#FFDB5C','#FB7293','#FF9F7F'],
                                title : {
                                    subtext: '单位：件',
                                    x:'left'
                                },
                                tooltip : {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                                },
                                grid: {
                                    left: '2%',
                                    right: '3%',
                                    bottom: '2%',
                                    containLabel: true
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    width:'160px',
                                    data:charData['legend']['data'],
                                    selected:charData['legend']['selected']
                                },
                                series:  [
                                    {
                                        name: '科室设备不良事件',
                                        type: 'pie',
                                        radius : '80%',
                                        center: ['42%', '55%'],
                                        data: charData['series']['data'],
                                        itemStyle: {
                                            emphasis: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }
                                ]
                            });
                        }else{
                            //没数据
                            no_data(init_chart_8);
                        }
                    }
                }
            });
        }

        //设备保养情况
        var id_name9 = 'target_chart_assets_patrol';
        if($.inArray(id_name9,chart_ids) >= 0){
            var year9 = current_year;
            var count_type9 = 'trend';
            get_assets_patrol(id_name9,year9,count_type9);
        }
        function get_assets_patrol(id_name9,year9,count_type9) {
            var init_chart_9 = echarts.init(document.getElementById(id_name9));
            init_chart_9.clear();
            init_chart_9.showLoading();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Index/target',
                data: {"action":id_name9,"year":year9,"count_type":count_type9},
                dataType: "json",
                async: false,
                success: function (charData) {
                    init_chart_9.hideLoading();
                    if(count_type9 == 'trend'){
                        init_chart_9.setOption({
                            color: ['#0C998A'],
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            grid: {
                                left: '2%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            legend: {
                                data:['保养次数']
                            },
                            xAxis: {
                                type: 'category',
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                name:'单位：次',
                                type: 'value',
                                boundaryGap: [0, 3]
                            },
                            series: [{
                                name:'保养次数',
                                type: 'line',
                                data: charData['series']['data'],
                                smooth: true
                            }]
                        });
                    }else if(count_type9 == 'abnormal'){
                        if(!$.isEmptyObject(charData)){
                            init_chart_9.setOption({
                                color: ['#B8680A','#0A6764'],
                                title : {
                                    subtext: '单位：台'
                                },
                                tooltip : {
                                    trigger: 'axis',
                                    axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                        type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                                    }
                                },
                                legend: {
                                    data:['正常设备','异常设备']
                                },
                                calculable : true,
                                xAxis : [
                                    {
                                        type : 'category',
                                        data : ['1 月','2 月','3 月','4 月','5 月','6 月','7 月','8 月','9 月','10 月','11 月','12 月']
                                    }
                                ],
                                yAxis : [
                                    {
                                        type : 'value'
                                    }
                                ],
                                series : [
                                    {
                                        name:'正常设备',
                                        type:'bar',
                                        data:charData['normal']
                                    },
                                    {
                                        name:'异常设备',
                                        type:'bar',
                                        data:charData['not_normal']

                                    }
                                ]
                            });
                        }else{
                            //没数据
                            init_chart_9.setOption({
                                color:"#E6E6E6",
                                title : {
                                    subtext: '暂无相关数据',
                                    x:'center'
                                },
                                series: [
                                    {
                                        type:'pie',
                                        center: ['45%', '60%'],
                                        radius: ['60%', '80%'],
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
                }
            });
        }

        //统计图表显示设置
        $("#target_chart_setting").on('click',function () {
            var url = admin_name+'/Index/target?action=show_setting';
            top.layer.open({
                type: 2,
                title: '统计图表显示设置',
                area: ['620px', '260px'],
                offset: '100px',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: [url],
                btn: ['确定', '取消'],
                yes: function(index, layero){
                    //按钮【确定】的回调
                    var parent_params = fun();
                    var new_show_ids = {};
                    var need_hide = [];
                    var baoliu = ['target_chart_assets_repair'];
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: admin_name+'/Index/target',
                        data: parent_params,
                        dataType: "json",
                        async: false,
                        beforeSend:beforeSend,
                        success: function (data) {
                            if (data.status == 1) {
                                new_show_ids = data.new_show_ids;
                                layer.msg(data.msg,{icon : 1,time:1000},function () {
                                    layer.closeAll();
                                    $.each(chart_ids,function (index,item) {
                                        if(item != 'target_chart_assets_repair'){
                                            if($.inArray(item,new_show_ids) < 0){
                                                //原来显示的图表不在新设置范围内，则隐藏
                                                $('.'+item).removeClass('on_use');
                                                $('.'+item).addClass('not_use');
                                                need_hide.push(item);
                                            }else{
                                                //原来显示的图表在新设置范围内，删除新设置中的值
                                                new_show_ids.splice($.inArray(item,new_show_ids),1);
                                                baoliu.push(item);
                                            }
                                        }
                                    });
                                    //显示剩余的新设置id
                                    $.each(new_show_ids,function (index,item) {
                                        $('.'+item).removeClass('not_use');
                                        $('.'+item).addClass('on_use');
                                        if(item == 'target_chart_depart_repair'){
                                            var year2 = 3;
                                            get_depart_repair_free(item,year2);
                                            var buts = $(this).parent().find('button');
                                            $.each(buts,function (index,val) {
                                                $(val).attr('class','layui-btn layui-btn-sm layui-btn-primary')
                                            });
                                            $(this).attr('class','layui-btn layui-btn-sm layui-btn-normal');
                                        }else{
                                            chang_type(item);
                                        }
                                        baoliu.push(item);
                                    });
                                    chart_ids = [];
                                    chart_ids = baoliu;
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2,time:1000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2},1000);
                        },
                        complete:complete
                    });


                },
                btn2: function(index, layero){

                }
            });
        });

        //全院设备概况显示设置
        $("#survey_setting").on('click',function () {
            var url = admin_name+'/Index/target?action=survey_setting';
            top.layer.open({
                type: 2,
                title: '全院设备概况显示设置',
                area: ['620px', '300px'],
                offset: '100px',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: [url],
                btn: ['确定', '取消'],
                yes: function(index, layero){
                    //按钮【确定】的回调
                    var parent_params = fun_survey();
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: admin_name+'/Index/target',
                        data: parent_params,
                        dataType: "json",
                        async: false,
                        beforeSend:beforeSend,
                        success: function (data) {
                            if (data.status == 1) {
                                layer.msg(data.msg,{icon : 1,time:1000},function () {
                                    layer.closeAll();
                                    //原来的ID先全部隐藏
                                    $.each(ids,function (index,item) {
                                        if(item != 'repair_scrap_assets'){
                                            $("#"+item).parent().removeClass('on_use');
                                            $("#"+item).parent().addClass('not_use');
                                        }
                                    });
                                    ids = data.new_show_ids;
                                    $.each(ids,function (index,item) {
                                        $("#"+item).parent().removeClass('not_use');
                                        $("#"+item).parent().addClass('on_use');
                                    });
                                    get_assets_data();
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2,time:2000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2},1000);
                        },
                        complete:complete
                    });


                },
                btn2: function(index, layero){

                }
            });
        });
    });
    $(function(){
        if ($(window).width() > 1366){
            $(".noticeTitle").each(function(k,v){
                var realTitle = $(v).children().attr('title');
                if (realTitle.length > 16){
                    realTitle = realTitle.substring(0,16);
                    $(v).children().html(realTitle+'...');
                }else {
                    $(v).children().html(realTitle);
                }
            });
            //        监听收缩
            admin.on('side(target)', function(obj){
                var status = obj.status;
                if (status == 'spread'){
                    $(".noticeTitle").each(function(k,v){
                        var realTitle = $(v).children().attr('title');
                        if (realTitle.length > 16){
                            realTitle = realTitle.substring(0,16);
                            $(v).children().html(realTitle+'...');
                        }else {
                            $(v).children().html(realTitle);
                        }
                    });
                }else {
                    $(".noticeTitle").each(function(k,v){
                        var realTitle = $(v).children().attr('title');
                        if (realTitle.length > 19){
                            realTitle = realTitle.substring(0,18);
                            $(v).children().html(realTitle+'...');
                        }else {
                            $(v).children().html(realTitle);
                        }
                    });
                }
            });
        }else {
            $(".noticeTitle").each(function(k,v){
                var realTitle = $(v).children().attr('title');
                if (realTitle.length > 11){
                    realTitle = realTitle.substring(0,11);
                    $(v).children().html(realTitle+'...');
                }else {
                    $(v).children().html(realTitle);
                }
            });
            //        监听收缩
            admin.on('side(target)', function(obj){
                var status = obj.status;
                if (status == 'spread'){
                    $(".noticeTitle").each(function(k,v){
                        var realTitle = $(v).children().attr('title');
                        if (realTitle.length > 11){
                            realTitle = realTitle.substring(0,11);
                            $(v).children().html(realTitle+'...');
                        }else {
                            $(v).children().html(realTitle);
                        }
                    });
                }else {
                    $(".noticeTitle").each(function(k,v){
                        var realTitle = $(v).children().attr('title');
                        if (realTitle.length > 14){
                            realTitle = realTitle.substring(0,14);
                            $(v).children().html(realTitle+'...');
                        }else {
                            $(v).children().html(realTitle);
                        }
                    });
                }
            });
        }
    });

//发布公告
    $('#addnoticeIndex').on('click', function () {
        var flag = 1;
        var url = $(this).attr('data-url');
        top.layer.open({
            id: 'addnoticeIndexs',
            type: 2,
            title: $(this).html(),
            shade: 0,
            anim: 2,
            scrollbar: false,
            area: ['75%', '100%'],
            offset: 'r',//弹窗位置固定在右边
            closeBtn: 1,
            content: [url],
            end: function () {
                if (flag) {
                    location.reload();
                }
            },
            cancel: function () {
                //如果是直接关闭窗口的，则不刷新表格
                flag = 0;
            }
        });
        return false;
    });
//查看公告
    $('.showNotice').on('click', function () {
        var url = $(this).attr('data-url');
        var notid = $(this).attr('data-id');
        top.layer.open({
            type: 2,
            title: $(this).attr('title'),
            shade: 0,
            offset: 'r',//弹窗位置固定在右边
            anim: 2,
            scrollbar: false,
            area: ['75%', '100%'],
            closeBtn: 1,
            content: [url + '?notid=' + notid+'&type=showNotice']
        });
        return false;
    });
    exports('login/index/target', {});
});


