layui.define(function (exports) {
    var current_classname = '';
    layui.use(['layer', 'form'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        //先更新页面部分需要提前渲染的控件
        form.render();
        var params = {};
        params.action = 'get_content';
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: design,
            data: params,
            dataType: "json",
            async: true,
            success: function (res) {
                if (res.status == 1) {
                    $.each(res.data, function (index, item) {
                        var classname = item.temp_name;
                        if (classname.indexOf('user') >= 0) {
                            $('.' + classname).css('border', '1px solid #FFB800');
                        }
                        if (classname == res.is_select) {
                            current_classname = res.is_select;
                            $('.' + classname).parent().find('div:last').find('button:first').addClass('layui-btn-disabled');
                        }
                        if ($('#LAY-Assets-Print-design .' + classname).find('.show_table').length >= 1) {
                            //斑马打印机
                            $('.' + classname).find('div:first').html(res.hospital_name);
                            var tr = $('#LAY-Assets-Print-design .' + classname).find('.show_table').find('tr');
                            $.each(tr, function (index2, item2) {
                                var tar = $(this);
                                var td = tar.find('td');
                                var i = 0;
                                if (td.length == 1) {
                                    $.each(item.show_fields, function (index3, item3) {
                                        if (index2 == i) {
                                            tar.find('td').html(item3 + '：' + res['assInfo'][index][index3]);
                                        }
                                        i++;
                                    });
                                } else {
                                    if (tar.find('td:first').attr('class').indexOf('line_height') > 0) {
                                        $.each(item.show_fields, function (index3, item3) {
                                            if (index2 == i) {
                                                tar.find('td:first').html(item3 + '：' + res['assInfo'][index][index3]);
                                            }
                                            i++;
                                        });
                                    } else {
                                        $.each(item.show_fields, function (index3, item3) {
                                            if (index2 == i) {
                                                tar.find('td:last').html(item3 + '：' + res['assInfo'][index][index3]);
                                            }
                                            i++;
                                        });
                                    }
                                }
                            });
                        } else if ($('#LAY-Assets-Print-design .' + classname).find('.czsy_table').length >= 1) {
                            //兄弟打印机
                            // $('.' + classname).find('div:first').html(res.hospital_name);
                            $('.' + classname).find('.hospital_name').html(res.hospital_name);
                            var casy_tr = $('#LAY-Assets-Print-design .' + classname).find('.czsy_table').find('tr');
                            $.each(casy_tr, function (index2, item2) {
                                var tar = $(this);
                                var td = tar.find('td');
                                var i = 0;
                                $.each(item.show_fields, function (index3, item3) {
                                    if (index2 == i) {
                                        tar.find('td:first').html(item3);
                                        tar.find('td:nth-child(2) div').html(res['assInfo'][index][index3]);
                                    }
                                    i++;
                                });
                            });
                        }
                    });
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2}, 1000);
            }
        });
        $('.design_label').click(function () {
            var flag = 1;
            top.layer.open({
                type: 2,
                title: '设计标签',
                area: ['780px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: [design + '?action=design_label'],
                end: function () {
                    if (flag) {
                        parent.location.reload();
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
        });
        $('.default').click(function () {
            var classname = $(this).parent().parent().find('div:first').attr('class');
            if (current_classname != classname) {
                var params = {};
                params.temp_name = classname;
                params.action = 'set_default';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: design,
                    data: params,
                    dataType: "json",
                    async: true,
                    success: function (res) {
                        if (res.status == 1) {
                            current_classname = res.temp_name;
                            $('.' + res.temp_name).parent().find('div:last').find('button:first').addClass('layui-btn-disabled');
                            $.each(res.others, function (index, item) {
                                $('.' + item).parent().find('div:last').find('button:first').removeClass('layui-btn-disabled');
                            });
                            layer.msg(res.msg, {icon: 1, time: 1000});
                        } else {
                            layer.msg(res.msg, {icon: 2}, 1000);
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2}, 1000);
                    }
                });
            }
        });
    });

    $(document).on('click', '.print_test', function () {
        $('#print_test').find('div').remove();
        var classname = $(this).parent().parent().find('div:first').attr('class');
        var html = '<div class="' + classname + '" style="width: 781px; height: 425px;">';
        html += $('.' + classname).html();
        html += '</div>';
        $('#print_test').append(html);
        $('#print_test').append(html);
        $('#print_test').append(html);
        $('#print_test').append(html);
        $('#print_test').append(html);
        $('#print_test').show();
        $('#print_test').printArea();
        $('#print_test').hide();
        return false;
    });

    $(document).on('click', '.delete', function () {
        var classname = $(this).parent().parent().find('div:first').attr('class');
        layer.confirm('确定删除该标签吗？', {
            icon: 3,
            title: '删除用户自定义标签'
        }, function (index) {
            var params = {};
            params.action = 'delete_design';
            params['temp_name'] = classname;
            $.ajax({
                type: "POST",
                url: design,
                data: params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            $('.' + classname).parent().remove();
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 5});
                }
            });
            layer.close(index);
        });
        return false;
    });
    exports('assets/print/design', {});
});
