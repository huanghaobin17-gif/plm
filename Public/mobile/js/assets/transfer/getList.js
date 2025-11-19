layui.use(['flow', 'util', 'layer', 'element'], function () {
    var flow = layui.flow, util = layui.util, layer = layui.layer, element = layui.element;

    //初始化下方导航栏菜单
    menuListSpread();

    //返回顶部工具条
    util.fixbar();

    //定义一个全局空对象
    var currentName = '';
    //初始化获取列表数据
    changeList({}, 'int');
    
    //搜索
    var searchFormObj = $("#searchForm"),stopCancelSearchObj = $(".stopCancelSearch");
    searchFormObj.submit(function () {
        var field = $("#searchInput").val();
        var params = {};
        params.search = field;
        $("#transferList").html('');
        changeList(params, 'search');
        document.activeElement.blur();
        return false;
    });
    searchFormObj.click(function(){
        var hasClass = $(this).parent().hasClass("weui-search-bar_focusing");
        if (hasClass == true){
            stopCancelSearchObj.hide();
        }
    });
    stopCancelSearchObj.click(function(){
        $("#searchBar").addClass("weui-search-bar_focusing");
        $(this).hide();
    });
    //取消按钮
    $("#searchCancel").on('click',function () {
        $("#searchInput").val('');
        var params = {};
        $("#transferList").html('');
        changeList(params, 'search');
        stopCancelSearchObj.show();
    });

    /**
     *
     * @param d 传参数是升序还是排序 名称
     */
    $(".orderTypeButton").click(function () {
        $("#transferList").remove();    
        $(document).unbind('scroll');
        $('#layui-card-body').append('<ul id="transferList"></ul>');
        var params = {};
        params.search = $("#searchInput").val();
        params.order = $(this).attr('ordertype');
        params.sort = $(this).attr('ordersort');
        $("#transferList").html('');
        changeList(params, 'order');
        $('.orderList').hide();
        return false;
    });

    //按分类搜索
    $('.soncat').on('click',function () {
        $("#transferList").remove();    
        $(document).unbind('scroll');
        $('#layui-card-body').append('<ul id="transferList"></ul>');
        var params = {};
        params.search = $("#searchInput").val();
        params.catid = $(this).attr('data-id');
        $("#transferList").html('');
        changeList(params, 'search');
        $.closePopup();
        bodyObj.css("position", "relative");
        bodyObj.css("overflow", "auto");
        return false;
    });

    //按科室搜索
    $('.js_grid').on('click',function () {
        $("#transferList").remove();    
        $(document).unbind('scroll');
        $('#layui-card-body').append('<ul id="transferList"></ul>');
        var params = {};
        params.search = $("#searchInput").val();
        params.departid = $(this).attr('data-id');
        $("#transferList").html('');
        changeList(params, 'search');
        $.closePopup();
        bodyObj.css("position", "relative");
        bodyObj.css("overflow", "auto");
        return false;
    });

    function changeList(params, name) {
        currentName = name;
        flow.load({
            elem: '#transferList' //指定列表容器
            , isAuto: true
            , mb: 200
            , done: function (page, next) {
                params.page = page;
                var lis = [];
                if (currentName === name) {
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        async: true,
                        success: function (res) {
                            if (res.code === 200) {
                                layui.each(res.rows, function (index, item) {
                                    lis.push(refreshList(item));
                                });
                            }else{
                                show_no_data();
                            }
                            $('.total_sum').html(res.total);
                            next(lis.join(''), res.page < res.pages);
                            new Swiper('.listCarousel', carouselConfig());
                        },
                        error: function () {
                            $.toptip('网络访问失败', 'error');
                        }
                    });
                }
            }
        });
    }

    /**
     *
     * @param data  后台返回的数组信息
     * @returns {string}
     */
    function refreshList(data) {
        $('.no_data').remove();
        var html = '<li>';
        html += '<div class="content" style="cursor: pointer;" data-id="' + data.assid + '">';
        html += '<div class="layui-row">';
        html += '<div class="jumpButton">'+data.url+'</div>';
        if (data.pic_url) {
            html += '<div class="layui-col-xs3">' + listImg(data) + '</div>';
        }
        html += '<div class="layui-col-xs9">' + listUl(data) + '</div>';
        html += '</div>';
        html += '</div>';
        html += '</li>';
        return html;
    }

    //组合列表图片
    function listImg(data) {
        var html = '<div class="swiper-container listCarousel">';
        html += '<div class="swiper-wrapper">';
        $.each(data.pic_url, function (k, v) {
            html += '<div class="swiper-slide"><img src="' + v + '" class="photo"></div>';
        });
        html += '</div>';
        html += '<div class="swiper-pagination"></div>';
        html += '</div>';
        return html;
    }


    //组合列表UL内容
    function listUl(data) {
        var html = '<ul class="detail">';
        html += '<li><span class="detailText">设备名称：</span><span class="text">' + data.assets + '</span></li>';
        html += '<li><span class="detailText">规格型号：</span><span class="text">' + data.model + '</span></li>';
        html += '<li><span class="detailText">设备编号：</span><span class="text">' + data.assnum + '</span></li>';
        html += '<li><span class="detailText">使用科室：</span><span class="text">' + data.department + '</span></li>';
        html += '<li><span class="detailText">当前状态：</span><span class="text">' + data.status_name + '</span></li>';
        html += '</ul>';
        return html;
    }

    function show_no_data() {
        $('.layui-flow-more').hide();
        $('.no_data').remove();
        var html = '<div class="no_data">\n' +
            '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
            '          <div class="no_data_tips">暂无相关数据</div>\n' +
            '       </div>';
        $('#transferList').after(html);
    }

    if (screen.width >= 768) {
        $(".searchList .wx-icon").css('left', '20%');
    }
    //排序列表(防冒泡)
    var orderListObj = $(".orderList");
    $(".order").on("click", function (a) {
        if (orderListObj.is(":hidden")) {
            orderListObj.addClass('animated slideInRight');
            orderListObj.show();
        } else {
            orderListObj.hide();
        }
        $(document).one("click", function () {
            orderListObj.hide();
        });
        a.stopPropagation();
    });
    orderListObj.on("click", function (a) {
        a.stopPropagation();
    });
    //        分类按钮
    var bodyObj = $("body");
    $("#categoryListButton").click(function () {
        $("#categoryList").popup();
        bodyObj.css("position", "fixed");
        bodyObj.css("overflow", "hidden");
    });

    //        科室按钮
    $("#departmentListButton").click(function () {
        bodyObj.css("position", "fixed");
        bodyObj.css("overflow", "hidden");
        /*判断是否多级科室 两种样式(多级科室列表 单级科室九宫格)*/
        $("#departmentList").popup();//单级科室样式
        //$("#departmentLevel").popup();//多级科室样式
    });
    //关闭分类 科室 popup按钮
    $(".weui-popup__modal .weui-icon-cancel").click(function () {
        $.closePopup();
        bodyObj.css("position", "relative");
        bodyObj.css("overflow", "auto");
    });

    //扫码转科
    $(document).on('click','#transferListsBrcode',function(){
        wx.scanQRCode({
            needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
            scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
            success: function (res) {
                var assnum = res.resultStr;// 当needResult 为 1 时，扫码返回的结果
                if(assnum.indexOf("ODE_") > 0){
                    assnum = res.resultStr.substr(9);
                }
                window.location.href = mobile_name+'/Transfer/add.html?assnum=' + assnum+'&action=brcode';
            }
        });
    });
});

