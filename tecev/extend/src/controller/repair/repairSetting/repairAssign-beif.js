layui.use(['layer', 'form', 'laydate', 'table', 'element'], function () {
    //初始化  todo
    var currentType = 1;
    var form = layui.form, $ = layui.jquery, layer = layui.layer, element = layui.element;
    form.render();
    //选项卡
    element.on('tab(repairAssignTab)', function (data) {
        switch (data.index) {
            case 0:
                currentType = 1;
                break;
            case 1:
                currentType = 2;
                break;
            case 2:
                currentType = 3;
                break;
            case 3:
                currentType = 4;
                break;
        }
    });


    //选择维修工程师弹窗
    $(document).on('click', '.show_user', function () {
        var inputThis = $(this);
        top.layer.open({
            type: 1,
            title: '请选择工程师',
            area: ['450px', '400px'],
            offset: '500px;',
            shade: 0,
            shadeClose: true,
            anim: 5,
            resize: false,
            scrollbar: false,
            isOutAnim: true,
            closeBtn: 1,
            content: $('#userList'),
            end: function () {
                //初始化 清除焦点,清除下拉框选项,隐藏遮罩
                inputThis.parent('td').parent('tr').removeClass('focus');
                $('#applicantSelect').html('');
                form.render();
                $('.Mask').hide();
            },
            success: function () {
                //当前tr设置焦点
                inputThis.parent('td').parent('tr').addClass('focus');
                var checked = inputThis.next('input[name="user_id"]').val();
                var params = {};
                params.type = 'getUser';
                params.userid = checked;
                params.assignStyle = currentType;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name+'/RepairSetting/repairAssign.html',
                    data: params,
                    dataType: "json",
                    beforeSend: beforeSend,
                    complete: complete,
                    success: function (data) {
                        var html = '<option value="">请选择</option>';
                        if (data.status === 1) {
                            var selected = '';
                            $.each(data.result, function (k, v) {
                                selected = '';
                                if (v.userid === checked) {
                                    selected = 'selected';
                                }
                                html += '<option ' + selected + ' value="' + v.userid + '">' + v.username + '</option>';
                            });
                        } else {
                            html = '<option value="">工程师已全部分配</option>';
                        }
                        $('#applicantSelect').html(html);
                        form.render();
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                $('.Mask').show();
            }
        });
    });
    //监听修改维修工程师
    form.on('submit(saveUser)', function (data) {
        var applicantSelect = $('#applicantSelect');
        var selectedName = applicantSelect.next().find('.layui-this').html();
        var params = data.field;
        if (params.applicant === '') {
            layer.msg("请选择指派的工程师！", {icon: 2, time: 2000});
            return false;
        }
        var body = {};
        switch (currentType) {
            case 1:
                //分类
                body = $('.category-body');
                break;
            case 2:
                //科室
                body = $('.department-body');
                break;
            case 3:
                //辅助分类
                body = $('.auxiliary-body');
                break;
            case 4:
                //设备
                body = $('.assets-body');
                break;
        }
        var assignid = body.find('.focus').find('input[name="assignid"]').val();
        if (!assignid) {
            //新增操作
            body.find('.add_count').find('input[name="user_name"]').val(selectedName);
            body.find('.add_count').find('input[name="user_id"]').val(params.applicant);
            applicantSelect.html('');
            form.render();
            layer.closeAll();
        } else {
            //修改操作
            params.type = 'saveUser';
            params.selectedName = selectedName;
            params.assignid = assignid;
            var url = admin_name+'/RepairSetting/repairAssign.html';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: url,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            body.find('.focus').find('input[name="user_name"]').val(selectedName);
                            body.find('.focus').find('input[name="user_id"]').val(params.applicant);
                            applicantSelect.html('');
                            form.render();
                            layer.closeAll();
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
            return false;
        }
    });


    //点击显示选择分类弹窗
    $(document).on('click', '.show_category', function () {
        var inputThis = $(this);
        var categorySelect = $('#categorySelect');
        var categoryList = $('#categoryList');
        top.layer.open({
            type: 1,
            title: '请选择需要分配给工程师的分类',
            area: ['500px', '450px'],
            offset: '500px;',
            shade: 0,
            shadeClose: true,
            anim: 5,
            resize: false,
            scrollbar: false,
            isOutAnim: true,
            closeBtn: 1,
            content: categoryList,
            end: function () {
                //初始化 清除焦点,清除下拉多选框选项,隐藏遮罩
                inputThis.parent('td').parent('tr').removeClass('focus');
                categorySelect.html('');
                categorySelect[0].sumo.reload();
                $('.Mask').hide();
            },
            success: function () {
                //设置tr焦点
                inputThis.parent('td').parent('tr').addClass('focus');
                var checked = inputThis.next('input[name="val_id"]').val();
                var checkedArr = [];
                $.each(checked.split(','), function (k, v) {
                    checkedArr[v] = v;
                });
                var params = {};
                params.value = checked;
                params.type = 'getCategory';
                changeSelect(params, checkedArr, categorySelect);
                $('.Mask').show();
            }
        });
    });
    //监听提交分类
    form.on('submit(saveCategory)', function () {
        var categorySelect = $('#categorySelect');
        var body = $('.category-body');
        var assignid = body.find('.focus').find('input[name="assignid"]').val();
        var params = {};
        var valuedata = '';
        var valuedataname = '';
        categorySelect.find("option:selected").each(function () {
            valuedata += "," + $(this).val();
            valuedataname += "," + $(this).text();
        });
        params.valuedata = valuedata.substr(1);
        params.valuedataname = valuedataname.substr(1);
        if (valuedata === '') {
            layer.msg("请选择分类！", {icon: 2, time: 2000});
            return false;
        }
        if (!assignid) {
            //新增操作
            var add_count = body.find('.add_count');
            addValueData(params, add_count, categorySelect);
        } else {
            //修改操作
            params.assignid = assignid;
            params.style = currentType;
            params.type = 'saveValueData';
            saveValueData(params, body, categorySelect);
        }
        return false;
    });


    //点击显示选择所属科室弹窗
    $(document).on('click', '.show_department', function () {
        var inputThis = $(this);
        var departmentSelect = $('#departmentSelect');
        var departmentList = $('#departmentList');
        var userid = inputThis.parent().prev().find('input[name="user_id"]').val();
        if (!userid) {
            layer.msg("请先选择维修工程师！", {icon: 2, time: 2000});
            return false;
        }
        top.layer.open({
            type: 1,
            title: '请选择需要分配给工程师的科室',
            area: ['500px', '450px'],
            offset: '500px;',
            shade: 0,
            shadeClose: true,
            anim: 5,
            resize: false,
            scrollbar: false,
            isOutAnim: true,
            closeBtn: 1,
            content: departmentList,
            end: function () {
                //初始化 清除焦点,清除下拉多选框选项,隐藏遮罩
                inputThis.parent('td').parent('tr').removeClass('focus');
                departmentSelect.html('');
                departmentSelect[0].sumo.reload();
                $('.Mask').hide();
            },
            success: function () {
                //设置tr焦点
                inputThis.parent('td').parent('tr').addClass('focus');
                var checked = inputThis.next('input[name="val_id"]').val();
                var checkedArr = [];
                $.each(checked.split(','), function (k, v) {
                    checkedArr[v] = v;
                });
                var params = {};
                params.value = checked;
                params.userid = userid;
                params.type = 'getDepartment';
                changeSelect(params, checkedArr, departmentSelect);
                $('.Mask').show();
            }
        });
    });
    //监听提交科室
    form.on('submit(saveDepartment)', function () {
        var departmentSelect = $('#departmentSelect');
        var body = $('.department-body');
        var assignid = body.find('.focus').find('input[name="assignid"]').val();
        var params = {};
        var valuedata = '';
        var valuedataname = '';
        departmentSelect.find("option:selected").each(function () {
            valuedata += "," + $(this).val();
            valuedataname += "," + $(this).text();
        });
        params.valuedata = valuedata.substr(1);
        params.valuedataname = valuedataname.substr(1);
        if (valuedata === '') {
            layer.msg("请选择科室！", {icon: 2, time: 2000});
            return false;
        }
        if (!assignid) {
            //新增操作
            var add_count = body.find('.add_count');
            addValueData(params, add_count, departmentSelect);
        } else {
            //修改操作
            params.assignid = assignid;
            params.style = currentType;
            params.type = 'saveValueData';
            saveValueData(params, body, departmentSelect);
        }
        return false;
    });


    //点击显示选择所属辅助分类弹窗
    $(document).on('click', '.show_auxiliary', function () {
        var inputThis = $(this);
        var auxiliarySelect = $('#auxiliarySelect');
        var auxiliaryList = $('#auxiliaryList');
        top.layer.open({
            type: 1,
            title: '请选择需要分配给工程师的科室',
            area: ['500px', '450px'],
            offset: '500px;',
            shade: 0,
            shadeClose: true,
            anim: 5,
            resize: false,
            scrollbar: false,
            isOutAnim: true,
            closeBtn: 1,
            content: auxiliaryList,
            end: function () {
                //初始化 清除焦点,清除下拉多选框选项,隐藏遮罩
                inputThis.parent('td').parent('tr').removeClass('focus');
                auxiliarySelect.html('');
                auxiliarySelect[0].sumo.reload();
                $('.Mask').hide();
            },
            success: function () {
                //设置tr焦点
                inputThis.parent('td').parent('tr').addClass('focus');
                var checked = inputThis.next('input[name="val_id"]').val();
                var checkedArr = [];
                $.each(checked.split(','), function (k, v) {
                    checkedArr[v] = v;
                });
                var params = {};
                params.value = checked;
                params.type = 'getAuxiliary';
                changeSelect(params, checkedArr, auxiliarySelect);
                $('.Mask').show();
            }
        });
    });
    //监听提交辅助分类
    form.on('submit(saveAuxiliary)', function () {
        var auxiliarySelect = $('#auxiliarySelect');
        var body = $('.auxiliary-body');
        var assignid = body.find('.focus').find('input[name="assignid"]').val();
        var params = {};
        var valuedata = '';
        var valuedataname = '';
        auxiliarySelect.find("option:selected").each(function () {
            valuedata += "," + $(this).val();
            valuedataname += "," + $(this).text();
        });
        params.valuedata = valuedata.substr(1);
        params.valuedataname = valuedataname.substr(1);
        if (valuedata === '') {
            layer.msg("请选择辅助分类！", {icon: 2, time: 2000});
            return false;
        }
        if (!assignid) {
            //新增操作
            var add_count = body.find('.add_count');
            addValueData(params, add_count, auxiliarySelect);
        } else {
            //修改操作
            params.assignid = assignid;
            params.style = currentType;
            params.type = 'saveValueData';
            saveValueData(params, body, auxiliarySelect);
        }
        return false;
    });


    //点击显示选择所属设备弹窗
    $(document).on('click', '.show_assets', function () {
        var inputThis = $(this);
        var assetsSelect = $('#assetsSelect');
        var assetsList = $('#assetsList');
        var userid = inputThis.parent().prev().find('input[name="user_id"]').val();
        if (!userid) {
            layer.msg("请先选择维修工程师！", {icon: 2, time: 2000});
            return false;
        }
        top.layer.open({
            type: 1,
            title: '请选择需要分配给工程师的设备',
            area: ['500px', '450px'],
            offset: '500px;',
            shade: 0,
            shadeClose: true,
            anim: 5,
            resize: false,
            scrollbar: false,
            isOutAnim: true,
            closeBtn: 1,
            content: assetsList,
            end: function () {
                //初始化 清除焦点,清除下拉多选框选项,隐藏遮罩
                inputThis.parent('td').parent('tr').removeClass('focus');
                assetsSelect.html('');
                assetsSelect[0].sumo.reload();
                $('.Mask').hide();
            },
            success: function () {
                //设置tr焦点
                inputThis.parent('td').parent('tr').addClass('focus');
                var checked = inputThis.next('input[name="val_id"]').val();
                var checkedArr = [];
                $.each(checked.split(','), function (k, v) {
                    checkedArr[v] = v;
                });
                var params = {};
                params.value = checked;
                params.userid = userid;
                params.type = 'getAssets';
                changeSelect(params, checkedArr, assetsSelect);
                $('.Mask').show();
            }
        });
    });
    //监听提交设备
    form.on('submit(saveAssets)', function () {
        var assetsSelect = $('#assetsSelect');
        var body = $('.assets-body');
        var assignid = body.find('.focus').find('input[name="assignid"]').val();
        var params = {};
        var valuedata = '';
        var valuedataname = '';
        assetsSelect.find("option:selected").each(function () {
            valuedata += "," + $(this).val();
            valuedataname += "," + $(this).text();
        });
        params.valuedata = valuedata.substr(1);
        params.valuedataname = valuedataname.substr(1);
        if (valuedata === '') {
            layer.msg("请选择设备！", {icon: 2, time: 2000});
            return false;
        }
        if (!assignid) {
            //新增操作
            var add_count = body.find('.add_count');
            addValueData(params, add_count, assetsSelect);
        } else {
            //修改操作
            params.assignid = assignid;
            params.style = currentType;
            params.type = 'saveValueData';
            saveValueData(params, body, assetsSelect);
        }
        return false;
    });


    //监听保存
    form.on('submit(addAssign)', function () {
        var params = {};
        var body = {};
        switch (currentType) {
            case 1:
                //分类
                body = $('.category-body');
                break;
            case 2:
                //科室
                body = $('.department-body');
                break;
            case 3:
                //辅助分类
                body = $('.auxiliary-body');
                break;
            case 4:
                //设备
                body = $('.assets-body');
                break;
        }

        var userid = body.find('.add_count').find('input[name="user_id"]').val();
        var valuedata = body.find('.add_count').find('input[name="val_id"]').val();
        var username = body.find('.add_count').find('input[name="user_name"]').val();
        var valuedataname = body.find('.add_count').find('input[name="val_name"]').val();
        if (!userid || !valuedata) {
            layer.msg("参数补全,请补充", {icon: 2, time: 2000});
            return false;
        }
        params.userid = userid;
        params.valuedata = valuedata;
        params.username = username;
        params.valuedataname = valuedataname;
        params.currentType = currentType;
        params.type = 'addAssign';
        var url = admin_name+'/RepairSetting/repairAssign.html';
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            success: function (data) {
                if (data.status === 1) {
                    var number = body.find('.content').length + 1;
                    var html = newTR(data.result, number);
                    if (body.find('.arc-empty').length > 0) {
                        //没有数据的情况
                        body.find('.arc-empty').remove();
                    }
                    body.find('.add_count').before(html);
                    body.find('.add_count').find('input').val('');
                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                    });
                } else {
                    layer.msg(data.msg, {icon: 2, time: 2000});
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2, time: 2000});
            }
        });
        return false;
    });
    //删除按钮
    form.on('submit(delAssign)', function () {
        return false;
    });
    //重置按钮
    form.on('submit(resetAssign)', function () {
        //手动清除所有对应input下的数据.
        $(this).parent('form').parent('td').parent('tr').find('input').val('');
        return false;
    });
    //多选插件设置
    $(document).ready(function () {
        $('.various').SumoSelect({
            selectAll: true,
            search: true,
            searchText: '',
            noMatch: '暂无可分配数据',
            captionFormatAllSelected: '已选择全部结果',
            captionFormat: '已选择{0}个结果',
            locale: ['OK', 'Cancel', '全选']
        });
    });
    //生成新增tr
    function newTR(data, number) {
        var classname='';
        switch (currentType) {
            case 1:
                //分类
                classname = 'show_category';
                break;
            case 2:
                //科室
                classname = 'show_department';
                break;
            case 3:
                //辅助分类
                classname = 'show_auxiliary';
                break;
            case 4:
                //设备
                classname = 'show_assets';
                break;
        }
        var html = '<tr class="content">';
        html += '<td class="assignid_td" style="text-align: center">' + number + '<input type="hidden" name="assignid" value="' + data.assignid + '"></td>';
        html += '<td class="no-padding-td">';
        html += '<input type="text" name="user_name" value="' + data.username + '" class="layui-input show_user">';
        html += '<input type="hidden" name="user_id" value="' + data.userid + '" class="layui-input">';
        html += '</td>';
        html += '<td class="no-padding-td">';
        html += '<input type="text" name="val_name" value="' + data.data_name + '" class="layui-input '+classname+'">';
        html += '<input type="hidden" name="val_id" value="' + data.data_val + '" class="layui-input">';
        html += '</td>';
        html += '<td class="td-align-center">';
        html += '<form action="" class="layui-form">';
        html += '<button class="layui-btn layui-btn-xs layui-btn-danger" lay-submit lay-filter="delAssign">删除</button>';
        html += '</form>';
        html += '</td>';
        html += '</tr>';
        return html;
    }

    //修改下拉内容
    function changeSelect(params, checkedArr, selectObj) {
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: admin_name+'/RepairSetting/repairAssign.html',
            data: params,
            dataType: "json",
            beforeSend: beforeSend,
            complete: complete,
            success: function (data) {
                var html = '';
                if (data.status === 1) {
                    var selected = '';
                    $.each(data.result, function (k, v) {
                        selected = '';
                        if (v.id === checkedArr[v.id]) {
                            selected = 'selected';
                        }
                        html += '<option ' + selected + ' value="' + v.id + '">' + v.name + '</option>';
                    });
                }
                selectObj.html(html);
                selectObj[0].sumo.reload();
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2, time: 2000});
            }
        });
    }

    //修改分配项
    function saveValueData(params, body, selectObj) {
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: admin_name+'/RepairSetting/repairAssign.html',
            data: params,
            dataType: "json",
            success: function (data) {
                if (data.status === 1) {
                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                        body.find('.focus').find('input[name="val_name"]').val(params.valuedataname);
                        body.find('.focus').find('input[name="val_id"]').val(params.valuedata);
                        selectObj.html('');
                        form.render();
                        layer.closeAll();
                    });
                } else {
                    layer.msg(data.msg, {icon: 2, time: 2000});
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2, time: 2000});
            }
        });
    }

    //新增分配项
    function addValueData(params, add_count, selectObj) {
        add_count.find('input[name="val_name"]').val(params.valuedataname);
        add_count.find('input[name="val_id"]').val(params.valuedata);
        //提交后先清除 科室弹窗的下拉的选项
        selectObj.html('');
        selectObj[0].sumo.reload();
        layer.closeAll();
    }

    
});






