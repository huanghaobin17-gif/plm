layui.use(['flow', 'util', 'layer','form'], function () {
    var flow = layui.flow, util = layui.util, layer = layui.layer,form = layui.form;

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
        $("#progress").html('');
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
        $("#progress").html('');
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
        $("#progress").html('');
        changeList(params,'order');
        $('.orderList').hide();
        return false;
    });


    function changeList(params, name) {
        currentName=name;
        flow.load({
            elem: '#progress' //指定列表容器
            , isAuto: true
            , mb: 200
            , done: function (page, next) {
                params.page = page;
                var lis = [];
                if(currentName===name){
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: progressUrl,
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
        var html = '<li>\n' +
            '                    <div class="content">\n' +
            '                        <div class="layui-row">\n' +
            '                            <div class="jumpButton">'+data.url+'</div>\n' +
            '                            <div class="status">\n' +
            '                                <span class="'+data.status_class_name+'">'+data.status_name+'</span>\n' +
            '                            </div>\n' +
            '                            <div class="layui-col-xs12">\n' +
            '                                <ul class="detail">\n' +
            '                                    <li>\n' +
            '                                        <span class="detailText">'+data.repnum+'</span>\n' +
            '                                    </li>\n' +
            '                                    <li>\n' +
            '                                        <span class="detailText">'+data.assets+'</span>\n' +
            '                                    </li>\n' +
            '                                    <li>\n' +
            '                                        <span class="detailText fts">'+data.department+'</span>\n' +
            '                                        <span class="text fts">'+data.applicant_time+'</span>\n' +
            '                                    </li>\n' +
            '                                </ul>\n' +
            '                            </div>\n' +
            '                        </div>\n' +
            '                    </div>\n' +
            '                </li>';
        return html;
    }

    form.on('select(repair_status)', function(data){
        var field = $("#searchInput").val();
        var params = {};
        params.search = field;
        params.status = data.value;
        $("#progress").html('');
        changeList(params, 'select');
        form.render();
    });

    function show_no_data() {
        $('.layui-flow-more').hide();
        $('.no_data').remove();
        var html = '<div class="no_data">\n' +
            '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
            '          <div class="no_data_tips">暂无相关维修进程</div>\n' +
            '       </div>';
        $('#progress').after(html);
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