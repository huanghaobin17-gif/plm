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


    function changeList(params, name) {
        currentName = name;
        flow.load({
            elem: '#reminderList' //指定列表容器
            , isAuto: true
            , mb: 200
            , done: function (page, next) {
                params.page = page;
                params.action='getReminderList';
                if (borid!=="") {
                    params.borid=borid;
                }
                var lis = [];
                if (currentName === name) {
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: reminderListUrl,
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
                            $('.total_sum').html(res.total);
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
        var html = '<div class="layui-card">';
        html += '<table class="layui-table" lay-even>';
        html += '<tr><th>流水号</th><td>'+data.borrow_num+'</td></tr>';
        html += '<tr><th>申请科室</th><td>'+data.apply_department+'</td></tr>';
        html += '<tr><th>申请人</th><td>'+data.apply_user+'</td></tr>';
        html += '<tr><th>申请时间</th><td>'+data.apply_time+'</td></tr>';
        html += '<tr><th>借调原因</th><td>'+data.borrow_reason+'</td></tr>';
        html += '<tr><th>预计归还时间</th><td>'+data.estimate_back+'</td></tr>';
        html += '<tr><th>逾期时长</th><td>'+data.overdue+'</td></tr>';
        html += '</table>';
        html += '<div class="enter"><button class="layui-btn layui-btn-fluid layui-btn-normal sendOutReminder" data-borid="'+data.borid+'">点击催还</button></div>';
        html += '</div>';
        return html;
    }

    $(document).on('click','.sendOutReminder',function () {
        var borid=$(this).data('borid');
        layer.confirm('确定该设备未归还，要发送催还提醒？',{icon: 3, title:'发送借调催还信息'}, function(index){
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: reminderListUrl,
                data: {borid:borid,action:'sendOutReminder'},
                dataType: "json",
                async: true,
                success: function (res) {
                    if (res.code === 200) {
                        $.toptip(res.msg, 'success');
                    } else {
                        $.toptip(res.msg, 'error');
                    }
                },
                error: function () {
                    $.toptip('网络访问失败', 'error');
                }
            });
            layer.close(index);
        });
    });


    function show_no_data() {
        $('.layui-flow-more').hide();
        $('.no_data').remove();
        var html = '<div class="no_data">\n' +
            '          <div class="no_data_img"><img src="/Public/mobile/images/icon/u83.png"/></div>\n' +
            '          <div class="no_data_tips">暂无相关数据</div>\n' +
            '       </div>';
        $('#reminderList').after(html);
    }

    //设备详情
    $(document).on('click', '.content', function () {
        window.location.href = mobile_name+'/Lookup/showAssets.html?assid=' + $(this).attr('data-id')
    });


});

