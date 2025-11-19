layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'element'], function () {
        //初始化  todo
        var currentType = 1;
        var form = layui.form, $ = layui.jquery, layer = layui.layer, element = layui.element;
        form.render();

        //切换医院
        form.on('select(hospital_id)',function (data) {
            var params = {};
            var hosid = data.value;
            params.action = 'getType';
            params.hospital_id = hosid;
            $.ajax({
                timeout: 5000,
                type: "GET",
                url: repairAssignUrl,
                data: params,
                dataType: "html",
                async: false,
                success: function (data) {
                    var repairAssign_obj=$('.repairAssign');
                    repairAssign_obj.html('');
                    repairAssign_obj.html(data);
                    currentType = 1;
                    form.render();
                }
            });
        });


        //选项卡切换
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

            var userid = body.find('.add_count').find('select[name="user"]').val();
            var username = body.find('.add_count').find('select[name="user"] option:selected').text();
            var valuedataObj = body.find('.add_count').find('select[name="dataValue"]');

            var valuedata = '';
            var valuedataname = '';
            $.each(valuedataObj.find('option:selected'), function (key, val) {
                valuedataname += ',' + $(val).text();
                valuedata += ',' + $(val).val();
            });

            if (!userid || !valuedata) {
                layer.msg("参数补全,请补充", {icon: 2, time: 2000});
                return false;
            }
            params.userid = userid;
            params.username = username;
            params.valuedata = valuedata.substr(1);
            params.valuedataname = valuedataname.substr(1);
            params.currentType = currentType;
            params.hospital_id=$('input[name="hos_id"]').val();
            params.type = 'addAssign';

            console.log(params);
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
                            layui.index.render();
                            setTimeout(function(){
                                switch (currentType) {
                                    case 1:
                                        //分类
                                        element.tabChange('repairAssignTab', 1);
                                        break;
                                    case 2:
                                        //科室
                                        element.tabChange('repairAssignTab', 2);
                                        break;
                                    case 3:
                                        //辅助分类
                                        element.tabChange('repairAssignTab', 3);
                                        break;
                                    case 4:
                                        //设备
                                        element.tabChange('repairAssignTab', 4);
                                        break;
                                }
                            }, 1000);
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
            var params={};
            var assignid=$(this).parent().parent().siblings('.assignid_td').find('input[name="assignid"]').val();
            params.type = 'delAssign';
            params.assignid = assignid;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/RepairSetting/repairAssign.html',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            layui.index.render();
                            setTimeout(function(){
                                switch (currentType) {
                                    case 1:
                                        //分类
                                        element.tabChange('repairAssignTab', 1);
                                        break;
                                    case 2:
                                        //科室
                                        element.tabChange('repairAssignTab', 2);
                                        break;
                                    case 3:
                                        //辅助分类
                                        element.tabChange('repairAssignTab', 3);
                                        break;
                                    case 4:
                                        //设备
                                        element.tabChange('repairAssignTab', 4);
                                        break;
                                }
                            }, 1000);
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
        //重置按钮
        form.on('submit(resetAssign)', function () {
            //手动清除所有对应input下的数据.
            var add_count=$(this).parent('form').parent('td').parent('tr.add_count');
            add_count.find('input').val('');
            add_count.find('.SumoSelect').find("select option").attr("selected",true).siblings("option").attr("selected",false);
            var html=add_count.find('.SumoSelect').find("select").html();
            add_count.find('.SumoSelect').find("select").html(html);
            add_count.find('.SumoSelect').find("select")[0].sumo.reload();
            return false;
        });
        //多选插件设置
        $(document).ready(function () {
            $('.various').SumoSelect({
                csvDispCount: 5,
                selectAll: true,
                search: true,
                searchText: '',
                noMatch: '暂无可分配数据',
                captionFormatAllSelected: '已选择全部结果',
                captionFormat: '已选择{0}个结果',
                locale: ['OK', 'Cancel', '全选']
            });
        });
        //修改下拉内容
        function changeSelect(params, selectObj) {
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
                        $.each(data.result, function (k, v) {
                            html += '<option ' + v.selected + ' value="' + v.id + '" data-name="' + v.name + '">' + v.name + '</option>';
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
        //有权限管理的选项卡 需要通过修改用户 重新获取对应的dataValue
        form.on('select(change_user)', function (data) {
            var dataValeSelectObj = $(data.elem).parent().next().find('select');
            if (currentType === 2 || currentType === 4) {
                //在科室||设备选项卡的时候
                var params = {};
                var selectedValue = data.value;
                if (selectedValue) {
                    var checked = '';
                    dataValeSelectObj.find("option:selected").each(function () {
                        checked += "," + $(this).val();
                    });
                    params.value = checked.substr(1);
                    params.userid = selectedValue;
                    if (currentType === 2) {
                        params.type = 'getDepartment';
                    } else {
                        params.type = 'getAssets';
                    }
                    changeSelect(params, dataValeSelectObj);
                }
            }
            // form.render();
        });
        //修改dataValue操作
        $('select.change_dataValue').on('sumo:closed', function (sumo) {
            var old_val = $(this).attr('data-val');
            var assignid=$(this).parent().parent().siblings('.assignid_td').find('input[name="assignid"]').val();
            console.log(old_val);
            var params={};
            var valuedata = '';
            var valuedataname = '';
            $.each($(this).find('option:selected'), function (k, v) {
                valuedata += ',' + $(v).val();
                valuedataname += ',' + $(v).text();
            });
            params.valuedata = valuedata.substr(1);
            if(params.valuedata!==old_val){
                params.valuedataname = valuedataname.substr(1);
                params.assignid = assignid;
                params.style = currentType;
                params.type = 'saveValueData';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name+'/RepairSetting/repairAssign.html',
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status === 1) {
                            layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                layui.index.render();
                                setTimeout(function(){
                                    switch (currentType) {
                                        case 1:
                                            //分类
                                            element.tabChange('repairAssignTab', 1);
                                            break;
                                        case 2:
                                            //科室
                                            element.tabChange('repairAssignTab', 2);
                                            break;
                                        case 3:
                                            //辅助分类
                                            element.tabChange('repairAssignTab', 3);
                                            break;
                                        case 4:
                                            //设备
                                            element.tabChange('repairAssignTab', 4);
                                            break;
                                    }
                                }, 1000);
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
        });
    });
    exports('repair/repairSetting/repairAssign', {});
});







