layui.use(['flow', 'util', 'layer'], function () {
    var flow = layui.flow, util = layui.util, layer = layui.layer;

    //初始化下方导航栏菜单
    menuListSpread();

    //返回顶部工具条
    util.fixbar(); 

    if(show_repair){
        //维修审批列表
        repair_approve('DESC','applicant_time');
        function repair_approve(order,sort) {
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: {'action':'repair','order':order,'sort':sort},
                dataType: "json",
                async: true,
                success: function (data) {
                    $('.repair_sum').html(data.total);
                    if (data.code === 200) {
                        $.each(data.rows,function (index,item) {
                            var html = '<li>\n' +
                                '                    <div class="content">\n' +
                                '                        <div class="layui-row">\n' +
                                '                            <div class="jumpButton">'+item.url+'</div>\n' +
                                '                            <div class="layui-col-xs12">\n' +
                                '                                <ul class="detail">\n' +
                                '                                    <li><span class="text">维修单号：</span><span class="detailText">'+item.repnum+'</span></li>\n' +
                                '                                    <li><span class="text">报修科室：</span><span class="detailText">'+item.department+'</span></li>\n' +
                                '                                    <li><span class="text">设备名称：</span><span class="detailText">'+item.assets+'</span></li>\n' +
                                '                                    <li><span class="text">设备编号：</span><span class="detailText">'+item.assnum+'</span></li>\n' +
                                '                                    <li><span class="text">规格型号：</span><span class="detailText">'+item.model+'</span></li>\n' +
                                '                                    <li><span class="text">报修时间：</span><span class="detailText">'+item.applicant_time+'</span></li>\n' +
                                '                                </ul>\n' +
                                '                            </div>\n' +
                                '                        </div>\n' +
                                '                    </div>\n' +
                                '                </li>';
                            $('#repair_approve').append(html);
                        })
                    }else{
                        show_no_repair_data(data.msg)
                    }
                },
                error: function () {
                    $.toptip('网络访问失败', 'error');
                }
            });
        }
        function show_no_repair_data(msg) {
            $('#repair_approve').html('');
            var html = '<div class="no_data">\n' +
                '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
                '          <div class="no_data_tips">'+msg+'</div>\n' +
                '       </div>';
            $('#repair_approve').after(html);
        }
        //维修排序
        $(".repair_button").click(function () {
            var params = {};
            params.order = $(this).attr('ordertype');
            params.sort = $(this).attr('ordersort');
            $("#repair_approve").html('');
            repair_approve(params.order,params.sort);
            $('.repair_order').hide();
            return false;
        });
        //排序列表(防冒泡)
        var r_orderListObj = $(".repair_order");
        $(".r_order").on("click", function (a) {
            if (r_orderListObj.is(":hidden")) {
                r_orderListObj.addClass('animated slideInRight');
                r_orderListObj.show();
            } else {
                r_orderListObj.hide();
            }
            $(document).one("click", function () {
                r_orderListObj.hide();
            });
            a.stopPropagation();
        });
        r_orderListObj.on("click", function (a) {
            a.stopPropagation();
        });
    }

    if(show_transfer){
        //转科审批列表
        transfer_approve('DESC','applicant_time');
        function transfer_approve(order,sort) {
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: {'action':'transfer','order':order,'sort':sort},
                dataType: "json",
                async: true,
                success: function (data) {
                    $('.transfer_sum').html(data.total);
                    if (data.code === 200) {
                        $.each(data.rows,function (index,item) {
                            var html = '<li>\n' +
                                '                    <div class="content">\n' +
                                '                        <div class="layui-row">\n' +
                                '                            <div class="jumpButton">'+item.url+'</div>\n' +
                                '                            <div class="layui-col-xs12">\n' +
                                '                                <ul class="detail">\n' +
                                '                                    <li><span class="text">转出科室：</span><span class="detailText">'+item.tranout_depart_name+'</span><img class="fx" src="/Public/mobile/images/icon/right.png"/></li>\n' +
                                '                                    <li><span class="text">转入科室：</span><span class="detailText">'+item.tranin_depart_name+'</span><img class="fx" src="/Public/mobile/images/icon/left.png"/></li>\n' +
                                '                                    <li><span class="text">设备名称：</span><span class="detailText">'+item.assets+'</span></li>\n' +
                                '                                    <li><span class="text">设备编号：</span><span class="detailText">'+item.assnum+'</span></li>\n' +
                                '                                    <li><span class="text">规格型号：</span><span class="detailText">'+item.model+'</span></li>\n' +
                                '                                    <li><span class="text">申请时间：</span><span class="detailText">'+item.applicant_time+'</span></li>\n' +
                                '                                </ul>\n' +
                                '                            </div>\n' +
                                '                        </div>\n' +
                                '                    </div>\n' +
                                '                </li>';
                            $('#transfer_approve').append(html);
                        })
                    }else{
                        show_no_transfer_data(data.msg)
                    }
                },
                error: function () {
                    $.toptip('网络访问失败', 'error');
                }
            });
        }
        function show_no_transfer_data(msg) {
            $('#transfer_approve').html('');
            var html = '<div class="no_data">\n' +
                '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
                '          <div class="no_data_tips">'+msg+'</div>\n' +
                '       </div>';
            $('#transfer_approve').after(html);
        }
        //转科排序
        $(".transfer_button").click(function () {
            var params = {};
            params.order = $(this).attr('ordertype');
            params.sort = $(this).attr('ordersort');
            $("#transfer_approve").html('');
            transfer_approve(params.order,params.sort);
            $('.transfer_order').hide();
            return false;
        });
        //排序列表(防冒泡)
        var t_orderListObj = $(".transfer_order");
        $(".t_order").on("click", function (a) {
            if (t_orderListObj.is(":hidden")) {
                t_orderListObj.addClass('animated slideInRight');
                t_orderListObj.show();
            } else {
                t_orderListObj.hide();
            }
            $(document).one("click", function () {
                t_orderListObj.hide();
            });
            a.stopPropagation();
        });
        t_orderListObj.on("click", function (a) {
            a.stopPropagation();
        });
    }

    if(show_borrow){
        //借调审批列表
        borrow_approve('DESC','apply_time');
        function borrow_approve(order,sort) {
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: {'action':'borrow','order':order,'sort':sort},
                dataType: "json",
                async: true,
                success: function (data) {
                    $('.borrow_sum').html(data.total);
                    if (data.code === 200) {
                        $.each(data.rows,function (index,item) {
                            var html = '<li>\n' +
                                '                    <div class="content">\n' +
                                '                        <div class="layui-row">\n' +
                                '                            <div class="jumpButton">'+item.url+'</div>\n' +
                                '                            <div class="layui-col-xs12">\n' +
                                '                                <ul class="detail">\n' +
                                '                                    <li><span class="text">借出科室：</span><span class="detailText">'+item.department+'</span><img class="fx" src="/Public/mobile/images/icon/right.png"/></li>\n' +
                                '                                    <li><span class="text">借入科室：</span><span class="detailText">'+item.apply_department+'</span><img class="fx" src="/Public/mobile/images/icon/left.png"/></li>\n' +
                                '                                    <li><span class="text">设备名称：</span><span class="detailText">'+item.assets+'</span></li>\n' +
                                '                                    <li><span class="text">规格型号：</span><span class="detailText">'+item.model+'</span></li>\n' +
                                '                                    <li><span class="text">申请时间：</span><span class="detailText">'+item.apply_time+'</span></li>\n' +
                                '                                    <li><span class="text">预计归还：</span><span class="detailText">'+item.estimate_back+'</span></li>\n' +
                                '                                </ul>\n' +
                                '                            </div>\n' +
                                '                        </div>\n' +
                                '                    </div>\n' +
                                '                </li>';
                            $('#borrow_approve').append(html);
                        })
                    }else{
                        show_no_borrow_data(data.msg)
                    }
                },
                error: function () {
                    $.toptip('网络访问失败', 'error');
                }
            });
        }
        function show_no_borrow_data(msg) {
            $('#borrow_approve').html('');
            var html = '<div class="no_data">\n' +
                '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
                '          <div class="no_data_tips">'+msg+'</div>\n' +
                '       </div>';
            $('#borrow_approve').after(html);
        }
        //转科排序
        $(".borrow_button").click(function () {
            var params = {};
            params.order = $(this).attr('ordertype');
            params.sort = $(this).attr('ordersort');
            $("#borrow_approve").html('');
            borrow_approve(params.order,params.sort);
            $('.borrow_order').hide();
            return false;
        });
        //排序列表(防冒泡)
        var b_orderListObj = $(".borrow_order");
        $(".b_order").on("click", function (a) {
            if (b_orderListObj.is(":hidden")) {
                b_orderListObj.addClass('animated slideInRight');
                b_orderListObj.show();
            } else {
                b_orderListObj.hide();
            }
            $(document).one("click", function () {
                b_orderListObj.hide();
            });
            a.stopPropagation();
        });
        b_orderListObj.on("click", function (a) {
            a.stopPropagation();
        });
    }
      if(show_scrap){
        //借调审批列表
        scrap_approve('DESC','apply_time');
        function scrap_approve(order,sort) {
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: {'action':'scrap','order':order,'sort':sort},
                dataType: "json",
                async: true,
                success: function (data) {
                    $('.scrap_sum').html(data.total);
                    if (data.code === 200) {
                        $.each(data.rows,function (index,item) {
                            var html = '<li>\n' +
                                '                    <div class="content">\n' +
                                '                        <div class="layui-row">\n' +
                                '                            <div class="jumpButton">'+item.url+'</div>\n' +
                                '                            <div class="layui-col-xs12">\n' +
                                '                                <ul class="detail">\n' +
                                '                                    <li><span class="text">报废单号：</span><span class="detailText">'+item.scrapnum+'</span></li>\n' +
                                '                                    <li><span class="text">设备名称：</span><span class="detailText">'+item.assets+'</span></li>\n' +
                                '                                    <li><span class="text">设备编号：</span><span class="detailText">'+item.assnum+'</span></li>\n' +
                                '                                    <li><span class="text">报废日期：</span><span class="detailText">'+item.scrapdate+'</span></li>\n' +
                                '                                    <li><span class="text">申请人：</span><span class="detailText">'+item.add_user+'</span></li>\n' +
                                '                                    <li><span class="text">使用科室：</span><span class="detailText">'+item.department+'</span></li>\n' +
                                '                                </ul>\n' +
                                '                            </div>\n' +
                                '                        </div>\n' +
                                '                    </div>\n' +
                                '                </li>';
                            $('#scrap_approve').append(html);
                        })
                    }else{
                        show_no_scrap_data(data.msg)
                    }
                },
                error: function () {
                    $.toptip('网络访问失败', 'error');
                }
            });
        }
        function show_no_scrap_data(msg) {
            $('#scrap_approve').html('');
            var html = '<div class="no_data">\n' +
                '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
                '          <div class="no_data_tips">'+msg+'</div>\n' +
                '       </div>';
            $('#scrap_approve').after(html);
        }
        //转科排序
        $(".scrap_button").click(function () {
            var params = {};
            params.order = $(this).attr('ordertype');
            params.sort = $(this).attr('ordersort');
            $("#scrap_approve").html('');
            scrap_approve(params.order,params.sort);
            $('.scrap_order').hide();
            return false;
        });
        //排序列表(防冒泡)
        var s_orderListObj = $(".scrap_order");
        $(".s_order").on("click", function (a) {
            if (s_orderListObj.is(":hidden")) {
                s_orderListObj.addClass('animated slideInRight');
                s_orderListObj.show();
            } else {
                s_orderListObj.hide();
            }
            $(document).one("click", function () {
                s_orderListObj.hide();
            });
            a.stopPropagation();
        });
        s_orderListObj.on("click", function (a) {
            a.stopPropagation();
        });
    }
});