layui.define(function(exports){
    layui.use(['form','tipsType'], function () {
        var form = layui.form,tipsType=layui.tipsType;
        tipsType.choose();
        //监听提交
        form.on('submit(editRolePrivi)', function (data) {
            var url = admin_name+'/Privilege/editRolePrivi';
            var roleid = $("input[name='roleid']").val();
            var menuids = new Array();
            var idstr = '';
            $.each($('input:checkbox:checked'), function () {
                menuids.push($(this).val());//向数组中添加元素
            });
            var params = {};
            params['menuid'] = menuids.join(',');
            params['roleid'] = roleid;
            $.ajax({
                type: "POST",
                url: url,
                data: params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        CloseWin(data.msg, 1, 1);
                    } else {
                        CloseWin(data.msg, 2, 0);
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 2});
                }
            });
            return false;
        });


        var defaultRoleArr = [];
        var defaultRoleName = '';

        form.on('checkbox(allChoose)', function (data) {
            var child = $(data.elem).parents('td').nextAll('td').find('input[type="checkbox"]');
            if (!data.elem.checked) {
                var value = 0;
                child.each(function (index, item) {
                    if (defaultRoleArr[$(item).val()]) {
                        value = $(item).val();
                        return false;
                    }
                });
                if (defaultRoleArr[value]) {
                    layer.confirm('取消此权限会导致 "' + defaultRoleName + '" 的部分功能无法正常使用，是否继续取消？', {
                        icon: 3, title: '取消权限',
                        cancel: function (index) {
                            $(data.elem)[0].checked = true;
                            layer.close(index);
                            form.render('checkbox');
                        }
                    }, function (index) {
                        child.each(function (index, item) {
                            item.checked = false;
                        });
                        form.render('checkbox');
                        layer.close(index);
                    }, function (index) {
                        $(data.elem)[0].checked = true;
                        form.render('checkbox');
                        layer.close(index);
                    });
                }else{
                    child.each(function (index, item) {
                        item.checked = data.elem.checked;
                    });
                    form.render('checkbox');
                }
            }else{
                child.each(function (index, item) {
                    item.checked = data.elem.checked;
                });
                form.render('checkbox');
            }
        });

        form.on('checkbox(allmenu)', function (data) {
            var trclass = $(data.elem).parents('td').parents('tr').attr('class');
            var child = $('.' + trclass).find('td').find('input[type="checkbox"]');
            if (!data.elem.checked) {
                var value = 0;
                child.each(function (index, item) {
                    if (defaultRoleArr[$(item).val()]) {
                        value = $(item).val();
                        return false;
                    }
                });
                if (defaultRoleArr[value]) {
                    layer.confirm('取消此权限会导致 "' + defaultRoleName + '" 的部分功能无法正常使用，是否继续取消？', {
                        icon: 3, title: '取消权限',
                        cancel: function (index) {
                            $(data.elem)[0].checked = true;
                            layer.close(index);
                            form.render('checkbox');
                        }
                    }, function (index) {
                        console.log(1);
                        child.each(function (index, item) {
                            item.checked = false;
                        });
                        form.render('checkbox');
                        layer.close(index);
                    }, function (index) {
                        $(data.elem)[0].checked = true;
                        form.render('checkbox');
                        layer.close(index);
                    });
                }else{
                    child.each(function (index, item) {
                        item.checked = data.elem.checked;
                    });
                    form.render('checkbox');
                }
            }else{
                child.each(function (index, item) {
                    item.checked = data.elem.checked;
                });
                form.render('checkbox');
            }
        });


        //选择默认角色
        form.on('radio(defaultRole)', function (data) {
            if (defaultRoleName === $(data.elem).attr('title')['title']) {
                //取消选择默认角色
                data.elem.checked = false;
                $.each($('input[name="menuids"]'), function (k, v) {
                    if (defaultRoleArr[$(v).val()]) {
                        v.checked = false;
                    }
                });
                form.render();
                defaultRoleArr = [];
                defaultRoleName = '';
                return false;
            }
            if (defaultRoleArr) {
                //如果之前有选择其他角色 先清除前面角色的选项
                $.each($('input[name="menuids"]'), function (k, v) {
                   /* if (defaultRoleArr[$(v).val()]) {
                        v.checked = false;
                    }*/
                    v.checked = false;
                });
                form.render();
            }
            defaultRoleName = $(data.elem).attr('title')['title'];
            var url = admin_name+'/Privilege/editRolePrivi';
            var params = {};
            if (data.value) {
                params.roleid = data.value;
                params.type = 'getRoleMenu';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: params,
                    datatype: "json",//返回数据的格式
                    //成功返回之后调用的函数
                    success: function (data) {
                        if (data.status === 1) {
                            defaultRoleArr = data.result;
                            $.each($('input[name="menuids"]'), function (k, v) {
                                if (data.result[$(v).val()]) {
                                    v.checked = true;
                                }
                            });
                            form.render('checkbox');
                        } else {
                            defaultRoleArr = [];
                            CloseWin(data.msg, 2, 0);
                        }
                    },
                    //调用出错执行的函数
                    error: function () {
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 2});
                    }
                });
            }
        });

        //点击列表项
        form.on('checkbox(choose_parent)', function (data) {
            var value = data.value;
            var input = $(data.elem);
            var operation = $(data.elem).parents('td').next().find('input[type="checkbox"]:checked');
            //取消选中验证是否有建议角色的项/或者已选中所关联的功能项
            if (!data.elem.checked) {
                //判断是否是建议的角色的
                if (defaultRoleArr[value]) {
                    layer.confirm('取消此权限会导致 "' + defaultRoleName + '" 的部分功能无法正常使用，是否继续取消？', {
                        icon: 3, title: '取消权限',
                        cancel: function (index) {
                            $(data.elem)[0].checked = true;
                            form.render('checkbox');
                            layer.close(index);
                        }
                    }, function (index) {
                        layer.close(index);
                    }, function (index) {
                        $(data.elem)[0].checked = true;
                        form.render('checkbox');
                        layer.close(index);
                    });
                    return false;
                }
                //已选中所关联的功能项
                operation.each(function (index, item) {
                    if ($(item).data('partid') === parseInt(value)) {
                        layer.confirm('取消此权限会导致 "' + $(item)['context']['title'] + '" 等功能无法正常使用，是否继续取消？', {
                            icon: 3, title: '取消权限',
                            cancel: function (index) {
                                $(data.elem)[0].checked = true;
                                form.render('checkbox');
                                layer.close(index);
                            }
                        }, function (index) {
                            layer.close(index);
                        }, function (index) {
                            $(data.elem)[0].checked = true;
                            form.render('checkbox');
                            layer.close(index);
                        });
                        return false;
                    }
                });
            }
        });

        //点击功能项
        form.on('checkbox(choose_op)', function (data) {
            var value = data.value;
            //取消选中验证是否有建议角色的项
            if (!data.elem.checked) {
                if (defaultRoleArr[value]) {
                    layer.confirm('取消此权限会导致 "' + defaultRoleName + '" 的部分功能无法正常使用，是否继续取消？', {
                        icon: 3, title: '取消权限',
                        cancel: function (index) {
                            $(data.elem)[0].checked = true;
                            form.render('checkbox');
                            layer.close(index);
                        }
                    }, function (index) {
                        layer.close(index);
                    }, function (index) {
                        $(data.elem)[0].checked = true;
                        form.render('checkbox');
                        layer.close(index);
                    });
                    return false;
                }
            } else {
                //选中后辅助选择父类项目
                var partid = $(data.elem).data('partid');
                if (partid) {
                    $('#menuids_' + partid)[0].checked = true;
                    form.render('checkbox');
                }
            }
        });


    });
    exports('controller/basesetting/privilege/editRolePrivi', {});
});