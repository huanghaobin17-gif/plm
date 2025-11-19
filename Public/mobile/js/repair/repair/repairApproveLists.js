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
        $("#repairApproveLists").html('');
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
        $("#repairApproveLists").html('');
        changeList(params, 'search');
        stopCancelSearchObj.show();
    });
    /**
     *
     * @param d 传参数是升序还是排序 名称
     */
    $(".orderTypeButton").click(function () {
        var params = {};
        params.order = $(this).attr('orderType');
        params.sort = $(this).attr('orderSort');
        $("#repairApproveLists").html('');
        changeList(params,'order');
        return false;
    });

    function changeList(params, name) {
        currentName=name;
        flow.load({
            elem: '#repairApproveLists' //指定列表容器
            , isAuto: true
            , mb: 200
            , done: function (page, next) {
                params.page = page;
                params.action = 'repairApproveLists';
                var lis = [];
                if (currentName === name) {
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: repairApproveListsUrl,
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
                            next(lis.join(''), page < res.pages);
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
        html += '<div class="content">';
        html += '<div class="layui-row">';
        html += '<div class="jumpButton">' + data.operation + '</div>';
        html += '<div class="layui-col-xs12">' + listUl(data) + '</div>';
        html += '</div>';
        html += '</div>';
        html += '</li>';
        return html;
    }

    //组合列表UL内容
    function listUl(data) {
        var html = '<ul class="detail">';
        html += '<li><span class="detailText">维修单号：</span><span class="text">' + data.repnum + '</span></li>';
        html += '<li><span class="detailText">报修科室：</span><span class="text">' + data.department + '</span></li>';
        html += '<li><span class="detailText">设备名称：</span><span class="text">' + data.assets + '</span></li>';
        html += '<li><span class="detailText">规格型号：</span><span class="text">' + data.model + '</span></li>';
        html += '<li><span class="detailText">报修时间：</span><span class="text">' + data.applicant_time + '</span></li>';
        html += '</ul>';
        return html;
    }

    function show_no_data() {
        $('.layui-flow-more').hide();
        $('.no_data').remove();
        var html = '<div class="no_data">\n' +
            '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
            '          <div class="no_data_tips">暂无相关维修审批</div>\n' +
            '       </div>';
        $('#repairApproveLists').after(html);
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