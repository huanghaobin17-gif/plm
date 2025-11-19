layui.define(function (exports) {
    layui.use('form', function () {
        var form = layui.form;
        form.render();

        //切换医院
        form.on('select(hospital_id)', function (data) {
            var params = {};
            var hosid = data.value;
            params.action = 'getType';
            params.hospital_id = hosid;
            $.ajax({
                timeout: 5000,
                type: "GET",
                url: approveLists,
                data: params,
                dataType: "html",
                async: false,
                success: function (data) {
                    $('.approvelist').html('');
                    $('.approvelist').html(data);
                    form.render();
                }
            });
        });

        //切换条件
        form.on('select(condition)', function (data) {
            var xuhao = parseInt(data.elem.getAttribute("data-xuhao"));
            var type = data.elem.getAttribute("data-type");
            var id = type + '_moneyinput_' + xuhao;
            var target = $('#' + id);
            var html = '';
            target.html(html);
            if (data.value == 'egt') {
                html += '<div class="layui-input-inline" style="width: 80px;margin-left: 10px;">\n' +
                    '<input type="text" name="egt[]" placeholder="￥" autocomplete="off" class="layui-input">\n' +
                    '</div>';
            } else if (data.value == 'lt') {
                html += '<div class="layui-input-inline" style="width: 80px;margin-left: 10px;">\n' +
                    '<input type="text" name="lt[]" placeholder="￥" autocomplete="off" class="layui-input">\n' +
                    '</div>';
            } else if (data.value == 'between') {
                html += '<div class="layui-input-inline" style="width: 80px;margin-left: 10px;">\n' +
                    '<input type="text" name="between[' + (xuhao * 2 - 2) + ']" placeholder="￥" autocomplete="off" class="layui-input">\n' +
                    '</div>\n' +
                    '<div class="layui-input-inline" style="width: 80px;margin-left: 10px;">\n' +
                    '<input type="text" name="between[' + (xuhao * 2 - 1) + ']" placeholder="￥" autocomplete="off" class="layui-input">\n' +
                    '</div>';
            } else {
                html = '';
            }
            target.html(html);
        });

        //添加进程
        $(".addProcess").on('click', function () {
            var type = $(this).attr('data-type');
            if (type == 'patrol_approve') {
                layer.msg("巡查保养不支持添加进程", {icon: 2}, 1000);
                return false;
            }
            var id = type + '_allprocess';
            var target = $('#' + id);
            var maxindex = parseInt(target.find("tr:last").find('td:first').html());
            maxindex = maxindex ? maxindex : 0;
            var html = '';
            html = '<tr class="oneprocess">\n' +
                '                                    <td>' + (maxindex + 1) + '</td>\n' +
                '                                    <td>\n' +
                '                                        <input type="hidden" name="processid[' + (maxindex + 1) + ']" value=""/><input type="text"  name="approvesName[' + (maxindex + 1) + ']" value=""  class="layui-input" placeholder="请输入审批名称" />\n' +
                '                                    </td>\n' +
                '                                    <td>\n' +
                '                                        <div class="layui-inline">\n' +
                '                                            <div class="layui-input-inline" style="width: 92px;">\n' +
                '                                                <select name="tags[' + (maxindex + 1) + ']" lay-filter="condition" data-xuhao="' + (maxindex + 1) + '" data-type="' + type + '" lay-search="">\n' +
                '                                                    <option value=""></option>\n' +
                '                                                    <option value="egt">大于等于</option>\n' +
                '                                                    <option value="lt">小于</option>\n' +
                '                                                    <option value="between">区间</option>\n' +
                '                                                </select>\n' +
                '                                            </div>\n' +
                '                                            <div class="layui-inline" id="' + type + '_moneyinput_' + (maxindex + 1) + '"></div>\n' +
                '                                        </div>\n' +
                '                                    </td>\n' +
                '                                    <td>' +
                '                                       <div class="app_user">\n' +
                '                                                        <ul class="' + type + '_userli">\n' +
                '                                                        </ul>\n' +
                '                                       </div>\n' +
                '                                                    <div class="app_icon">\n' +
                '                                                        <i class="layui-icon adduser" id="' + type + '_adduser_' + (maxindex + 1) + '" data-type="' + type + '" style="color: #1E9FFF;cursor: pointer;font-size: 18px;">&#xe608;</i>\n' +
                '                                                    </div>\n' +
                '                                                    <input type="hidden" name="' + type + '_users[' + (maxindex + 1) + ']" value=""/></td>\n' +
                '<td><input type="text"  name="remark[' + (maxindex + 1) + ']"  class="layui-input" placeholder="请输入备注" /></td>' +
                '                                    <td>\n' +
                '                                        <button class="layui-btn layui-btn-xs layui-btn-normal" lay-submit lay-filter="delProcess">删除</button>\n' +
                '                                    </td>\n' +
                '                                </tr>';
            target.append(html);
            form.render();
        });
        //添加进程(维修)
        // $("body").on('click','.addProcess',function () {
        //     var type = $(this).attr('data-type');
        //     var id = type+'_allprocess';
        //     var target = $('#'+id);
        //     var maxindex = parseInt(target.find("tr:last").find('td:first').html());
        //     maxindex = maxindex ? maxindex : 0;
        //     var html = '';
        //     html = '<tr class="oneprocess">\n' +
        //         '                                    <td>'+(maxindex+1)+'</td>\n' +
        //         '                                    <td>\n' +
        //         '                                        <input type="hidden" name="processid['+(maxindex+1)+']" value=""/><input type="text"  name="approvesName['+(maxindex+1)+']" value=""  class="layui-input" placeholder="请输入审批名称" />\n' +
        //         '                                    </td>\n' +
        //         '                                    <td>\n' +
        //         '                                        <div class="layui-inline">\n' +
        //         '                                            <div class="layui-input-inline" style="width: 92px;">\n' +
        //         '                                                <select name="tags['+(maxindex+1)+']" lay-filter="condition" data-xuhao="'+(maxindex+1)+'" data-type="'+type+'" lay-search="">\n' +
        //         '                                                    <option value=""></option>\n' +
        //         '                                                    <option value="egt">大于等于</option>\n' +
        //         '                                                    <option value="lt">小于</option>\n' +
        //         '                                                    <option value="between">区间</option>\n' +
        //         '                                                </select>\n' +
        //         '                                            </div>\n' +
        //         '                                            <div class="layui-inline" id="'+type+'_moneyinput_'+(maxindex+1)+'"></div>\n' +
        //         '                                        </div>\n' +
        //         '                                    </td>\n' +
        //         '                                    <td>' +
        //         '                                       <div class="app_user">\n' +
        //         '                                                        <ul class="'+type+'_userli">\n' +
        //         '                                                        </ul>\n' +
        //         '                                       </div>\n' +
        //         '                                                    <div class="app_icon">\n' +
        //         '                                                        <i class="layui-icon adduser" id="'+type+'_adduser_'+(maxindex+1)+'" data-type="'+type+'" style="color: #1E9FFF;cursor: pointer;font-size: 18px;">&#xe608;</i>\n' +
        //         '                                                    </div>\n' +
        //         '                                                    <input type="hidden" name="'+type+'_users['+(maxindex+1)+']" value=""/></td>\n' +
        //         '                                    <td>\n' +
        //         '                                        <button class="layui-btn layui-btn-xs layui-btn-normal" lay-submit lay-filter="delProcess">删除</button>\n' +
        //         '                                    </td>\n' +
        //         '                                </tr>';
        //     target.append(html);
        //     form.render();
        // });

        //添加审批人
        $("body").on('click', '.adduser', function () {
            var type = $(this).attr('data-type');
            var id = $(this).attr('id');
            top.layer.open({
                id: 'addusers',
                type: 1,
                title: '添加审批人',
                scrollbar: false,
                area: ['38%', '70%'],
                offset: ['20%', ''],
                shadeClose: true,
                isOutAnim: true,
                anim: 2, //动画风格
                closeBtn: 1,
                shade: false,
                content: $('#roles'),
                success: function (layero, index) {
                    $('input[name="apptype"]').val(type);
                    $('input[name="object"]').val(id);
                    var radio = document.getElementsByName("app_pri");
                    if (type == "patrol_approve") {
                        $('input[title=部门审批负责人]').removeProp('checked').prop('disabled', 'disabled');
                        form.render('radio');
                        $(radio[1]).next().click();
                    } else {
                        $('input[title=部门审批负责人]').removeProp('checked').prop('disabled', '');
                        form.render('radio');
                        $(radio[0]).next().click();
                    }
                }

            });
            return false;
        });

        //移除审批人
        $("body").on('click', '.remove_user', function () {
            var xuhao = $(this).attr('data-xuhao');
            var rename = $(this).parent().html();
            rename = rename.replace(' <i class="layui-icon layui-tab-close remove_user" data-xuhao="' + xuhao + '">ဆ</i>', '');
            var clname = $(this).parent().parent().attr('class');
            var jq = '_userli';
            var type = clname.substring(0, (clname.length - jq.length));
            $(this).parent().remove();
            var oldname = $('input[name="' + type + '_users[' + xuhao + ']"]').val();
            var newname = oldname.replace(rename + ",", '');
            newname = newname.replace("," + rename, '');
            newname = newname.replace(rename, '');
            $('input[name="' + type + '_users[' + xuhao + ']"]').val(newname);

        });

        //切换条件
        form.on('radio(app_pri)', function (data) {
            if (data.value == 2) {
                $('.roleuser').show();
            } else {
                $('.roleuser').hide();
            }
        });

        //删除流程
        form.on('submit(delProcess)', function () {
            var index = $(this).parent().parent().next().find('td:first').html();
            // console.log($(this).parent().parent().parent().find('tr').length());
            // if(index == 3){
            //     $(this).parent().parent().next().find('td:first').html(index-1);
            // }
            $(this).parent().parent().remove();
            return false;
        });

        //确认选项审批人
        form.on('submit(confirm)', function (data) {
            var type = $('input[name="apptype"]').val();
            var object = $('input[name="object"]').val();
            var app_pri = $('input[name="app_pri"]:checked').val();
            var appuser = '';
            if (app_pri == 2) {
                appuser = $('select[name="approver_user"] option:selected').val();
                if (!appuser) {
                    layer.msg("请选择指定的用户！", {icon: 2}, 1000);
                    return false;
                }
            } else {
                appuser = app_pri;
            }
            var xuhao = $('#' + object).parent().parent().parent().find('td:first').html();
            var target = $('input[name="' + type + '_users[' + xuhao + ']"]');
            var existsuser = target.val();
            var html = '';
            if (!existsuser) {
                target.val(appuser);
                html = '<li class="liapuser">' + appuser + ' <i class="layui-icon layui-tab-close remove_user" data-xuhao="' + xuhao + '">&#x1006;</i></li>';
            } else {
                //查询是否已存在审批人
                if (existsuser.indexOf(appuser) == -1) {
                    existsuser += ',' + appuser;
                    target.val(existsuser);
                } else {
                    layer.msg("审批人已存在！", {icon: 2}, 1000);
                    return false;
                }
                html = '<li class="liapuser">' + appuser + ' <i class="layui-icon layui-tab-close remove_user" data-xuhao="' + xuhao + '">&#x1006;</i></li>';
            }

            var cl = '.' + type + '_userli';
            $('#' + object).parent().parent().find(cl).append(html);
            parent.layer.closeAll();
        });

        //保存数据
        form.on('submit(save)', function (data) {
            var params = {};
            params = data.field;
            params.action = $(this).attr('data-action');
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name + '/ApproveSetting/addProcess',
                data: params,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                            parent.location.reload();
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete: complete
            });
            return false;
        });
        //开启、关闭审批
        //2019-09-11 许兰填 已知bug：当使用页面刷新时会出现多次调用该方法，
        //修复方案为 $(".openoffapprove").on('click',function ()
        $("body").on('click', '.openoffapprove', function () {
            var params = {};
            params.action = 'offon';
            params.typeid = $(this).attr('data-typeid');
            params.typestatus = $(this).attr('data-status');
            var data_type = $(this).attr('data-type');
            var html = $(this).html();
            var is_dispaly = '0';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name + '/ApproveSetting/addProcess',
                data: params,
                async: false,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        is_dispaly = 1;
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            parent.location.reload();
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete: complete
            });
            // var divs = $("div[data-type=" + data_type + "]");
            // if (is_dispaly == 1) {
            //     if (params.typestatus == 0) {
            //         $(this).attr('data-status', 1);
            //         $(this).html('关闭' + html.substring(2, 10));
            //         $(this).removeClass("layui-btn layui-btn-xs openoffapprove");
            //         $(this).addClass("layui-btn layui-btn-xs layui-btn-danger openoffapprove");
            //     } else {
            //         $(this).attr('data-status', 0);
            //         $(this).html('开启' + html.substring(2, 10));
            //         $(this).removeClass("layui-btn layui-btn-xs layui-btn-danger openoffapprove");
            //         $(this).addClass("layui-btn layui-btn-xs openoffapprove");
            //     }
            //     parent.location.reload();
            // }
            return false;
        });
    });
    exports('basesetting/approveSetting/approveLists', {});
});