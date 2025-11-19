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
        $("#borrowInCheckList").html('');
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
        $("#borrowInCheckList").html('');
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
        $("#borrowInCheckList").html('');
        changeList(params, 'order');
        $('.orderList').hide();
        return false;
    });

    function changeList(params, name) {
        currentName = name;
        flow.load({
            elem: '#borrowInCheckList' //指定列表容器
            , isAuto: true
            , mb: 200
            , done: function (page, next) {
                params.page = page;
                var lis = [];
                if (currentName === name) {
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: borrowInCheckListUrl,
                        data: params,
                        dataType: "json",
                        async: true,
                        success: function (res) {
                            if (res.code === 200) {
                                layui.each(res.rows, function (index, item) {
                                    lis.push(refreshList(item));
                                });
                            }
                            $('.total_sum').html(res.total);
                            next(lis.join(''), res.page < res.pages);
                            new Swiper('.listCarousel', carouselConfig());
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
        var html = '<li>';
        html += '<div class="content" style="cursor: pointer;" data-id="' + data.assid + '">';
        html += '<div class="layui-row">';
        html += '<div class="jumpButton">'+data.operation+'</div>';
        html += '<div class="layui-col-xs12">' + listUl(data) + '</div>';
        html += '</div>';
        html += '</div>';
        html += '</li>';
        return html;
    }

    //组合列表UL内容
    function listUl(data) {
        var html = '<ul class="detail">';
        html += '<li class="list_li_width"><span class="detailText">设备名称：</span><span class="text">' + data.assets + '</span></li>';
        html += '<li><span class="detailText">借调单号：</span><span class="text">' + data.borrow_num + '</span></li>';
        html += '<li><span class="detailText">被借科室：</span><span class="text">' + data.department + '</span></li>';
        html += '<li><span class="detailText list_title_3">申请人：</span><span class="text">' + data.apply_user + '</span></li>';
        html += '<li><span class="detailText">申请时间：</span><span class="text">' + data.apply_time + '</span></li>';
        html += '</ul>';
        return html;
    }

    //设备详情
    $(document).on('click', '.content', function () {
        window.location.href = mobile_name+'/Lookup/showAssets.html?assid=' + $(this).attr('data-id')
    });


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

