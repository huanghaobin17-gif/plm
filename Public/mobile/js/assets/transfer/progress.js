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

    //扫码查询设备详情
    $(document).on('click','#progressbrcode',function(){
        wx.scanQRCode({
            needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
            scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
            success: function (res) {
                var assnum = res.resultStr;// 当needResult 为 1 时，扫码返回的结果
                if(assnum.indexOf("ODE_") > 0){
                    assnum = res.resultStr.substr(9);
                }
                window.location.href = mobile_name+'/Transfer/progress.html?action=detail&assnum=' + assnum;
            }
        });
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
        html += '<div class="content" style="cursor: pointer;" data-id="' + data.atid + '">';
        html += '<div class="layui-row">';
        html += '<div class="jumpButton"><i class="layui-icon layui-icon-right"></i></div>';
        html += '<div class="layui-col-xs12">' + listUl(data) + '</div>';
        html += '</div>';
        html += '</div>';
        html += '</li>';
        return html;
    }

    //组合列表UL内容
    function listUl(data) {
        var html = '<ul class="detail">';
        html += '<li><span class="detailText">转科单号：</span><span class="text">' + data.transfernum + '</span></li>';
        html += '<li class="list_li_width"><span class="detailText">设备名称：</span><span class="text">' + data.assets + '</span></li>';
        html += '<li><span class="detailText">规格型号：</span><span class="text">' + data.model + '</span></li>';
        html += '<li><span class="detailText">设备编号：</span><span class="text">' + data.assnum + '</span></li>';
        html += '<li><span class="detailText">转出科室：</span><span class="text">' + data.tranout_depart_name + ' </span> <img src="/Public/mobile/images/icon/right.png" style="width: 24px;"/></li>';
        html += '<li><span class="detailText">转入科室：</span><span class="text">' + data.tranin_depart_name + ' </span> <img src="/Public/mobile/images/icon/left.png" style="width: 24px;"/></li>';
        html += '<li><span class="detailText">进程状态：</span><span class="text">' + data.show_status_name + '</span></li>';
        html += '</ul>';
        return html;
    }


    function show_no_data() {
        $('.layui-flow-more').hide();
        $('.no_data').remove();
        var html = '<div class="no_data">\n' +
            '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
            '          <div class="no_data_tips">暂无转科记录</div>\n' +
            '       </div>';
        $('#progress').after(html);
    }

    //设备详情
    $(document).on('click', '.content', function () {
        window.location.href = mobile_name+'/Transfer/progress.html?action=detail&atid=' + $(this).attr('data-id')
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