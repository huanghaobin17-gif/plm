layui.use(['flow', 'util', 'layer'], function () {
    var flow = layui.flow, util = layui.util, layer = layui.layer;

    //初始化下方导航栏菜单
    menuListSpread();

    //返回顶部工具条
    util.fixbar();

    //初始化获取列表数据
    var currentName = '';
    changeList({}, 'int');

    //搜索
    var searchFormObj = $("#searchForm"),stopCancelSearchObj = $(".stopCancelSearch");
    searchFormObj.submit(function () {
        var field = $("#searchInput").val();
        var params = {};
        params.search = field;
        $("#partStockList").html('');
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
        $("#partStockList").html('');
        changeList(params, 'search');
        stopCancelSearchObj.show();
    });

    /**
     *
     * @param d 传参数是升序还是排序 名称
     */
    $(".orderTypeButton").click(function () {
        var params = {};
        params.order = $(this).attr('ordertype');
        params.sort = $(this).attr('ordersort');
        $("#partStockList").html('');
        changeList(params,'order');
        $('.orderList').hide();
        return false;
    });


    function changeList(params, name) {
        currentName=name;
        flow.load({
            elem: '#partStockList' //指定列表容器
            , isAuto: true
            , mb: 200
            , done: function (page, next) {
                params.page = page;
                var lis = [];
                if(currentName===name){
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
                            $('.sum').html(res.total);
                            next(lis.join(''), res.page < res.pages);
                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2}, 1000);
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
        html += '<div class="'+data.content_name+'">';
        html += '<div class="layui-row">';
        html += '<div class="jumpButton">' + data.operation + '</div>';
        if (data.pic_url) {
            html += '<div class="layui-col-xs3">' + listImg(data) + '</div>';
            html += '<div class="layui-col-xs9">' + listUl(data) + '</div>';
        } else {
            html += '<div class="layui-col-xs12">' + listUl(data) + '</div>';
        }
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
        html += '<li><span class="detailText">配件名称：</span><span class="text">' + data.parts + data.repnum+'</span></li>';
        html += '<li><span class="detailText">配件型号：</span><span class="text">' + data.parts_model + '</span></li>';
        html += '<li><span class="detailText">库存数量：</span><span class="text">' + data.stock_nums + data.unit+ data.need_nums+'</span></li>';
        html += '<li><span class="detailText">配件单价：</span><span class="text">' + data.price + '</span></li>';
        html += '<li><span class="detailText">供 应 商：</span><span class="text">' + data.supplier_name + '</span></li>';
        html += '</ul>';
        return html;
    }


    function show_no_data() {
        $('.layui-flow-more').hide();
        $('.no_data').remove();
        var html = '<div class="no_data">\n' +
            '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
            '          <div class="no_data_tips">暂无库存配件</div>\n' +
            '       </div>';
        $('#partStockList').after(html);
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