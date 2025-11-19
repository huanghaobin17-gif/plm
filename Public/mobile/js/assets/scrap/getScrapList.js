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
    var searchFormObj = $("#searchForm"), stopCancelSearchObj = $(".stopCancelSearch");
    searchFormObj.submit(function () {
        var field = $("#searchInput").val();
        var params = {};
        params.search = field;
        $("#getScrapList").html('');
        changeList(params, 'search');
        document.activeElement.blur();
        return false;
    });
    searchFormObj.click(function () {
        var hasClass = $(this).parent().hasClass("weui-search-bar_focusing");
        if (hasClass == true) {
            stopCancelSearchObj.hide();
        }
    });
    stopCancelSearchObj.click(function () {
        $("#searchBar").addClass("weui-search-bar_focusing");
        $(this).hide();
    });
    //取消按钮
    $("#searchCancel").on('click', function () {
        $("#searchInput").val('');
        var params = {};
        $("#getScrapList").html('');
        changeList(params, 'search');
        stopCancelSearchObj.show();
    });

    /**
     *
     * @param d 传参数是升序还是排序 名称
     */
    $(".orderTypeButton").click(function () {
        var params = {};
        params.search = $("#searchInput").val();
        params.order = $(this).attr('ordertype');
        params.sort = $(this).attr('ordersort');
        $("#getScrapList").html('');
        changeList(params, 'order');
        $('.orderList').hide();
        return false;
    });

    function changeList(params, name) {
        currentName = name;
        flow.load({
            elem: '#getScrapList' //指定列表容器
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
                            } else {
                                show_no_data();
                            }
                            $('.sum').html(res.total);
                            next(lis.join(''), res.page < res.pages);
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
        html += '<div class="content" style="cursor: pointer;" data-id="' + data.scrid + '">';
        html += '<div class="layui-row">';
        html += '<div class="jumpButton"><i class="layui-icon layui-icon-right"></i></div>';
        if (data.pic_url) {
            html += '<div class="layui-col-xs3">' + listImg(data) + '</div>';
        }
        html += '<div class="layui-col-xs12">' + listUl(data) + '</div>';
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
        html += '<li class="list_li_width"><span class="detailText">设备名称：</span><span class="text">' + data.assets + '</span></li>';
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
        $('#getScrapList').after(html);
    }

    //报废详情
    $(document).on('click', '.content', function () {
        window.location.href = mobile_name+'/Scrap/getScrapList.html?action=showScrap&scrid=' + $(this).attr('data-id')
    });

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
});

