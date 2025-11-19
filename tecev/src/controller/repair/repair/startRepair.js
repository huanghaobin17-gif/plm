layui.define(function(exports){
    layui.use(['layer', 'form', 'upload', 'laydate', 'tipsType', 'suggest'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, upload = layui.upload, laydate = layui.laydate,
            tipsType = layui.tipsType, suggest = layui.suggest;

        //初始化tips的选择功能
        tipsType.choose();
        //初始化搜索建议插件
        suggest.search();

        laydate.render(dateConfig('#followdate')); //跟进时间元素渲染
        laydate.render(dateConfig('#nextdate')); //预计下一次跟进时间元素渲染
        laydate.render({
            elem: '#service_date' //指定元素
            , type: 'datetime',
            calendar: true
            , trigger: 'click'
            , done: function (value, date) {
                if (date.hours === 0 && date.minutes === 0 && date.seconds === 0) {
                    layer.confirm('当前选择日期暂无具体时间,是否需要补充具体时间？', {
                        btn: ['是', '否'],
                        title: '是否需要补充时间',
                        closeBtn: 0
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            $("#service_date").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                    });
                }
            }
        });


        var thisbody = $('#LAY-Repair-Repair-startRepair');

        var delPartid = [];
        //被移除的相关文件ID
        var delfileid = [];
        //当前被移除的配件次数
        var delPartNum = 0;
        //当前新添加的配件次数
        var addPartNum = 0;

        //配件搜索建议
        var existPartsInfo = [];

        form.verify({
            price: function (value) { //value：表单的值、item：表单的DOM对象
                if (!/^(([1-9]\d*)|\d)(\.\d{1,3})?$/.test(value)) {
                    return '请输入正确的金额';
                }
            }
        });

        //其他费用验证
        var historyOtherPirce=parseFloat(thisbody.find('input[name="other_price"]').val());
        thisbody.find('input[name="other_price"]').change(function () {
            var value=parseFloat($(this).val());
            if (!check_price($(this).val())) {
                layer.msg("请输入合理的费用！", {icon: 2, time: 2000});
                $(this).addClass('border-color-red');
                return false;
            }else{
                $(this).removeClass('border-color-red');
                var other_price=parseFloat(thisbody.find('input[name="actual_price"]').val())-historyOtherPirce;
                thisbody.find('input[name="actual_price"]').val(other_price+value);
                historyOtherPirce=value;
            }
        });

        //联系号码验证
        thisbody.find('input[name="username_tel"]').change(function () {
            console.log($(this).val());
            if($(this).val()){
                if (!checkTel($(this).val())) {
                    layer.msg("请输入合理的联系号码！", {icon: 2, time: 2000});
                    $(this).addClass('border-color-red');
                    return false;
                }else{
                    $(this).removeClass('border-color-red');
                }
            }else{
                $(this).removeClass('border-color-red');
            }
        });

        //联系号码验证
        thisbody.find('input[name="assist_engineer_tel"]').change(function () {
            if($(this).val()){
                if (!checkTel($(this).val())) {
                    layer.msg("请输入合理的联系号码！", {icon: 2, time: 2000});
                    $(this).addClass('border-color-red');
                    return false;
                }else{
                    $(this).removeClass('border-color-red');
                }
            }else{
                $(this).removeClass('border-color-red');
            }
        });



        //维修结束
        form.on('submit(endStartRepair)', function (data) {
            if(thisbody.find('.border-color-red').length>0){
                layer.msg("请处理标红的异常项！", {icon: 2, time: 2000});
                return false;
            }
            var params = data.field;
            var layedit = layui.layedit;
            layedit.sync(layeditIndex);
            params.dispose_detail = layedit.getContent(layeditIndex);
            params.overEngineer = parseInt(YES_STATUS);
            params = getPartsInfo(params);
            params = getFollowInfo(params);
            params = getFileInfo(params);
            submit($, params, startRepairUrl);
            return false;
        });

        //不提交数据
        form.on('submit(none)', function (data) {
            return false;
        });

        //保存维信息
        form.on('submit(keepStartRepair)', function (data) {
            if(thisbody.find('.border-color-red').length>0){
                layer.msg("请处理标红的异常项！", {icon: 2, time: 2000});
                return false;
            }
            var params = data.field;
            var layedit = layui.layedit;
            layedit.sync(layeditIndex);
            params.dispose_detail = layedit.getContent(layeditIndex);
            params = getPartsInfo(params);
            params = getFollowInfo(params);
            params = getFileInfo(params);
            submit($, params, startRepairUrl);
            return false;
        });
        //添加跟进
        form.on('submit(addFollow)', function (data) {
            var params = data.field;
            //跟进时间
            var followdate = params.followdate;
            //处理详情
            var remark = params.remark;
            //预计下一次跟进时间
            var nextdate = params.nextdate;
            //判断下一条记录是否符合的时间
            var maxtime = thisbody.find("input[name='maxtime']").val();
            if (!followdate) {
                layer.msg("请填写跟进时间！", {icon: 2, time: 2000});
                return false;
            }
            if (!remark) {
                layer.msg("请填写处理详情！", {icon: 2, time: 2000});
                return false;
            }
            if (!nextdate) {
                layer.msg("请填写预计下一步跟进时间！", {icon: 2, time: 2000});
                return false;
            }
            if (followdate > nextdate) {
                layer.msg("跟进时间不能大于预计下次跟进时间！", {icon: 2, time: 2000});
                return false;
            } else if (followdate < maxtime) {
                layer.msg('下一条记录跟进时间不能比上一条记录跟进时间小', {icon: 2});
                return false;
            }
            var editRow = $(this).attr('edit-followRow');
            if (editRow) {
                //编辑对应行数据
                var target = thisbody.find('#' + editRow);
                target.find('td.followdate').html(followdate);
                target.find('td.remark').html(remark);
                target.find('td.nextdate').html(nextdate);
                layer.msg("修改跟进记录成功！", {icon: 1, time: 2000});
                thisbody.find('.addFollow').removeAttr('edit-followRow');
                thisbody.find('.addFollow').html('添加');
                thisbody.find('.addNumber').html('添加');
            } else {
                var tr = thisbody.find('.addFollow-list').find('tr');
                var xuhao = tr.length;
                var html = '<tr id="row_' + xuhao + '">\n' +
                    '<td class="td-align-center xuhao">' + xuhao + '</td>\n' +
                    '<td class="td-align-center followdate">' + followdate + '</td>\n' +
                    '<td class="td-align-center remark">' + remark + '</td>\n' +
                    '<td class="td-align-center nextdate">' + nextdate + '</td>\n' +
                    '<td class="td-align-center">\n' +
                    '<div class="layui-btn-group"><button type="button" class="layui-btn layui-btn-xs layui-btn-warm" lay-submit="" lay-filter="editFollow" data-row="' + xuhao + '"><i class="layui-icon">&#xe642;</i></button>\n' +
                    '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger" lay-submit="" lay-filter="deleteFollow"  style="margin-left:5px" data-row="' + xuhao + '"><i class="layui-icon">&#xe640;</i></button></div>\n' +
                    '</td>\n' +
                    '</tr>';
                layer.msg("添加跟进记录成功！", {icon: 1, time: 2000});
                thisbody.find(".addFollow-list").prepend(html);
                thisbody.find("input[name='maxtime']").val(nextdate);
            }
            //清空输入框数据
            thisbody.find("input[name='followdate']").val('');
            thisbody.find("input[name='remark']").val('');
            thisbody.find("input[name='nextdate']").val('');
            return false;
        });

        //编辑跟进
        form.on('submit(editFollow)', function (data) {
            var row = $(this).attr('data-row');
            var target = thisbody.find('#row_' + row);
            var followdate = target.find('td.followdate').html();
            var remark = target.find('td.remark').html();
            var nextdate = target.find('td.nextdate').html();
            thisbody.find('input[name="followdate"]').val(followdate);
            thisbody.find('input[name="remark"]').val(remark);
            thisbody.find('input[name="nextdate"]').val(nextdate);
            thisbody.find('.addFollow').attr('edit-followRow', 'row_' + row);
            thisbody.find('.addFollow').html('修改');
            thisbody.find('.addNumber').html('修改');
            thisbody.find("input[name='maxtime']").val($(this).parents('.addFollow-list').children('tr').find('.nextdate').html());
            return false;
        });
        //删除跟进
        form.on('submit(deleteFollow)', function (data) {
            $(this).parent().parent().parent().remove();
            thisbody.find("input[name='maxtime']").val('');
            layer.msg("删除跟进记录成功！", {icon: 1, time: 2000});
            //重新编排序号
            var tr = thisbody.find('.addFollow-list').find('tr');
            var i = 1;
            $.each(tr, function () {
                $(this).find('td.xuhao').html(i);
                i++;
            });
            return false;
        });

        //清空输入框数据(跟进详情重置按钮)
        thisbody.find('#followReset').on('click', function () {
            thisbody.find("input[name='followdate']").val('');
            thisbody.find("input[name='remark']").val('');
            thisbody.find("input[name='nextdate']").val('');
            return false;
        });

        //选择协助工程师 获取号码
        form.on('select(assist_engineer)', function (data) {
            var usernameval = data.value;
            if (parseInt(usernameval) !== -1) {
                $.each(userJson, function (index, data) {
                    if (usernameval === data.username) {
                        thisbody.find('#assist_engineer_tel').val(data.telephone);
                    }
                });
            } else {
                thisbody.find('#assist_engineer_tel').val('');
            }
            form.render();
        });

        //配件名称搜索建议
        thisbody.find("#partsNameSearch").bsSuggest(
            returnPartsInfo()
        ).on('onDataRequestSuccess', function (e, result) {
            existPartsInfo = result.value;
            $.each(existPartsInfo, function (k, v) {
                v.is_show = 1;
            });
        }).on('onSetSelectValue', function (e, keyword, data) {
            thisbody.find("input[name='part_model']").val(data.parts_model);
        }).on('onUnsetSelectValue', function () {
            //不正确
            thisbody.find("input[name='part_model']").val('');
        });

        //添加配件/服务
        form.on('submit(addParts)', function (data) {
            var params = data.field;
            //查询配件名是否重复
            var repeat = 0;
            var repeat_tr = {};
            var partsTbody = thisbody.find('.partsTbody');
            params.parts = $.trim(params.parts);
            params.part_model = $.trim(params.part_model);
            if (!params.parts) {
                layer.msg("请填写配件名称！", {icon: 2, time: 1000});
                return false;
            }
            if (!params.sum) {
                layer.msg("请填写配件数量！", {icon: 2, time: 1000});
                return false;
            } else {
                var reg = /^[0-9]*[1-9][0-9]*$/;
                if (!reg.test(params.sum)) {
                    layer.msg("配件数量格式不正确！", {icon: 2, time: 1000});
                    return false;
                }
                //else {
                //    var personalTotal = 0;
                //    var is_exist = false;
                //    if (params.is_out == '') {
                //        $.each(personalPartsInfo, function (k, v) {
                //            if (params.parts == v.parts && params.part_model == v.parts_model) {
                //                is_exist = true;
                //                personalTotal = v.total;
                //                return false;
                //            }
                //        });
                //        if(is_exist){
                //            if (parseInt(params.sum) > parseInt(personalTotal)){
                //                layer.msg("配件数量大于个人库存数量，请先勾选出库", {icon: 2, time: 1000});
                //                return false;
                //            }
                //        }else{
                //            layer.msg("配件不存在于个人库存，请先勾选出库", {icon: 2, time: 1000});
                //            return false;
                //        }
                //        params.statusName = '否';
                //    }else {
                //        params.statusName = '是';
                //    }
                //}
            }
            partsTbody.find('.tr_part').each(function (index, value) {
                if ($.trim($(value).find('.parts').html()) === params.parts && $.trim($(value).find('.part_model').html()) === params.part_model) {
                    repeat = 1;
                    repeat_tr = $(value);
                    return false;
                }
            });
            if (repeat === 1) {
                //配件此型号已存在
                layer.msg('配件/服务:' + params.parts + ' 型号:' + params.part_model + ' 已存在', {icon: 2}, 1000);
                return false;
            }
            var xuhao = partsTbody.find('tr').length;
            var tr = '<tr class="tr_part" data-partid="' + xuhao + '" id="editRow' + xuhao + '">';
            tr += '<td class="parts">' + params.parts + '</td>';
            tr += '<td class="part_model">' + params.part_model + '</td>';
            tr += '<td class="sum">' + params.sum + '</td>';
            //tr += '<td class="statusName">' + params.statusName + '</td>';
            tr += '<td class="adduser">' + username + '</td>';
            tr += '<td><div class="layui-btn-group"><button type="button" class="layui-btn layui-btn-xs layui-btn-warm save_part" ><i class="layui-icon">&#xe642;</i></button> <button type="button" class="layui-btn layui-btn-xs layui-btn-danger remove_part"  ><i class="layui-icon">&#xe640;</i></button></td></div>';
            tr += '</tr>';
            partsTbody.prepend(tr);
            layer.msg('添加成功', {icon: 1}, 1000);
            thisbody.find("input[name='parts']").val('');
            thisbody.find("input[name='part_model']").val('');
            thisbody.find("input[name='sum']").val('');
            //$("input[name='status']").prop('checked',true);
            //$("input[name='is_out']").val('1');
            thisbody.find(".sumPrice").html('0');
            //form.render();

            //搜索建议插件重载
            var showPartsInfo = [], result = {};
            $.each(existPartsInfo, function (k, v) {
                if (v.parts === params.parts && v.parts_model === params.part_model) {
                    v.is_show = 0;
                }
            });
            $.each(existPartsInfo, function (k, v) {
                if (parseInt(v.is_show) === 1) {
                    showPartsInfo[k] = v;
                }
            });
            showPartsInfo = $.grep(showPartsInfo, function (n, i) {
                return n;
            }, false);
            result.value = showPartsInfo;
            prevParts = result;
            refreshDropMenu(result);
            //添加次数+1
            addPartNum++;
            checkDoEnd();
            return false;
        });


        //清空输入框数据(配件重置按钮)
        thisbody.find('#partReset').on('click', function () {
            thisbody.find("input[name='parts']").val('');
            thisbody.find("input[name='part_model']").val('');
            thisbody.find("input[name='sum']").val('');
            //$("input[name='status']").prop('checked',false);
            //$("input[name='is_out']").val('');
            //form.render('checkbox');
            return false;
        });

        //上传附件
        var fileData = {};
        uploadFile = upload.render({
            elem: '#startRepairFile'  //绑定元素
            , url: startRepairUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: fileData
            , choose: function (obj) {
                //选择文件后
                fileData.action = 'upload';
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = thisbody.find('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = thisbody.find('.addFileTbody');
                    var html = '<tr class="fileDataTr isAddFile">';
                    html += '<td class="file_name">' + res.formerly + '</td>';
                    html += '<td class="add_user">' + res.add_user + '</td>';
                    html += '<td class="add_time">' + res.add_time + '</td>';
                    html += '<div class="layui-btn-group">';
                    var input = '<input type="hidden" name="file_id" value="0">';
                    input += '<input type="hidden" name="file_type" value="' + res.file_type + '">';
                    input += '<input type="hidden" name="save_name" value="' + res.title + '">';
                    input += '<input type="hidden" name="file_size" value="' + res.size + '">';
                    input += '<input type="hidden" name="file_name" value="' + res.file_name + '">';
                    input += '<input type="hidden" name="file_url" value="' + res.file_url + '">';

                    var button = '<button class="layui-btn layui-btn-xs downFile" lay-event="" style="" data-url="">下载</button>';
                    if (res.file_type !== 'doc' || res.file_type !== 'docx') {
                        button += '<button class="layui-btn layui-btn-xs layui-btn-normal showFile" lay-event="" style="" data-url="">预览</button>';
                    }
                    button += '<button class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</button>';
                    html += '<td><div class="layui-btn-group">' + input + button + '</div></td>';
                    html += '</div>';
                    addFileTbody.append(html);
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function () {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        //预览文件
        $(document).on('click', '.showFile', function () {
            var path = $(this).siblings('input[name="file_url"]').val();
            var name = $(this).siblings('input[name="file_name"]').val();
            var ext = $(this).siblings('input[name="file_type"]').val();
            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + ' 文件查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url + '?path=' + path + '&filename=' + name + '.' + ext]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
            return false;
        });

        //下载文件
        $(document).on('click', '.downFile', function () {
            var params = {};
            params.path = $(this).siblings('input[name="file_url"]').val();
            params.filename = $(this).siblings('input[name="file_name"]').val();
            postDownLoadFile({
                url: admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
            return false;
        });

        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = thisbody.find('.addFileTbody');
            var params = {};
            params.action = 'deleteFile';
            params.file_id = $(this).siblings('input[name="file_id"]').val();

            if (parseInt(params.file_id) > 0) {
                delfileid.push(params.file_id);
            }
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            return false;
        });

        //编辑配件
        $(document).on('click', '.save_part', function () {
            var layer = layui.layer;
            var a_this = $(this).parent().parent().parent();
            var partid = a_this.attr('data-partid');
            var parts = a_this.find('.parts').html();
            var part_model = a_this.find('.part_model').html();
            var sum = a_this.find('.sum').html();
            //var statusName = a_this.find('.statusName').html();
            var obj_parts = thisbody.find("input[name='parts']");
            var obj_part_model = thisbody.find("input[name='part_model']");
            var obj_addPartsInfo = thisbody.find(".addPartsInfo");
            //var obj_statusName=$("input[name='status']");
            //var obj_isOut=$("input[name='is_out']");
            obj_parts.val(parts);
            obj_part_model.val(part_model);
            //配件名称和型号不能修改
            obj_parts.attr('readonly', true);
            obj_part_model.attr('readonly', true);
            thisbody.find("input[name='sum']").val(sum);
            //if (statusName == '是'){
            //    obj_isOut.val('1');
            //    obj_statusName.prop('checked',true);
            //}else {
            //    obj_isOut.val('');
            //    obj_statusName.prop('checked',false);
            //}
            thisbody.find("input[name='editPartId']").val(partid);
            thisbody.find("#partReset").hide();
            obj_addPartsInfo.attr('lay-filter', 'editPart');
            obj_addPartsInfo.html('修改');
            thisbody.find(".remove_part").hide();
            //layui.form.render('checkbox');
            return false;
        });

        //移除自修配件
        $(document).on('click', '.remove_part', function () {
            var a_this = $(this).parent().parent().parent();
            var partsName = a_this.find('.parts').html();
            var parts_model = a_this.find('.part_model').html();
            layer.confirm('确定移除此配件？', {icon: 3, title: '移除提示'}, function (index) {
                a_this.remove();
                delPartNum++;
                if (a_this.data('partid') > 0) {
                    //删除的配件记录
                    delPartid.push(a_this.data('partid'));
                }

                layer.close(index);

                var showPartsInfo = [], result = {};
                $.each(existPartsInfo, function (k, v) {
                    if (v.parts === partsName && v.parts_model === parts_model) {
                        v.is_show = 1;
                    }
                });
                $.each(existPartsInfo, function (k, v) {
                    if (parseInt(v.is_show) === 1) {
                        showPartsInfo[k] = v;
                    }
                });
                showPartsInfo = $.grep(showPartsInfo, function (n, i) {
                    return n;
                }, false);
                result.value = showPartsInfo;
                checkDoEnd();
                //重载搜索建议插件
                refreshDropMenu(result);
            });
        });

        //获取维修跟进信息
        function getFollowInfo(params) {
            var target = thisbody.find('.addFollow-list').find('tr');
            var followdate = [], remark = [], nextdate = [];
            $.each(target, function () {
                var f = $.trim($(this).find('td.followdate').html());
                if (f) {
                    followdate.push(f);
                    //处理详情
                    var r = $.trim($(this).find('td.remark').html());
                    r = r ? r : '';
                    remark.push(r);
                    //下一次跟进时间
                    var n = $.trim($(this).find('td.nextdate').html());
                    n = n ? n : '';
                    nextdate.push(n);
                }
            });
            params.followdate = followdate;
            params.remark = remark;
            params.nextdate = nextdate;
            return params;
        }

        //获取配件信息
        function getPartsInfo(params) {
            var partname = '';
            var model = '';
            var num = '';
            var partid = '';
            //var partsStatus = '';
            thisbody.find('.partsTbody').find('.tr_part').each(function () {
                var patrs = $(this).find('.parts').html();
                var part_partid = $(this).data('partid');
                var part_model = $(this).find('.part_model').html();
                var sum = $(this).find('.sum').html();
                //var statusName = $(this).find('.statusName').html();
                if (part_model) {
                    model += part_model + '|';
                }
                if (part_partid) {
                    partid += part_partid + '|';
                }
                if (patrs) {
                    partname += patrs + '|';
                }
                if (sum) {
                    num += sum + '|';
                }
                //partsStatus += statusName + '|';
            });
            params.partname = partname;
            params.model = model;
            params.num = num;
            params.partid = partid;
            //params.partsStatus = partsStatus;
            return params;
        }

        //获取上传文件信息
        function getFileInfo(params) {
            //获取上传文件信息
            var fileDataTr = thisbody.find('.isAddFile');
            if (fileDataTr.length > 0) {
                params.file_name = '';
                params.save_name = '';
                params.file_type = '';
                params.file_size = '';
                params.file_url = '';
                $.each(fileDataTr, function (key, value) {
                    params.file_name += $(value).find('input[name="file_name"]').val() + '|';
                    params.save_name += $(value).find('input[name="save_name"]').val() + '|';
                    params.file_type += $(value).find('input[name="file_type"]').val() + '|';
                    params.file_size += $(value).find('input[name="file_size"]').val() + '|';
                    params.file_url += $(value).find('input[name="file_url"]').val() + '|';
                });
            }
            params.delfileid=delfileid.join(",");
            return params;
        }

        //更新配件建议搜索
        function refreshDropMenu(data) {
            var refresh = thisbody.find('.partsNameDiv');
            var html = refresh.html();
            refresh.html('');
            refresh.html(html);
            thisbody.find("#partsNameSearch").bsSuggest({
                getDataMethod: "data",
                data: data,
                effectiveFields: ["parts", "parts_model"],
                searchFields: ["parts"],
                effectiveFieldsAlias: {parts: "配件名称", parts_model: "配件型号"},
                ignorecase: false,
                showHeader: true,
                listStyle: {
                    "max-height": "330px", "max-width": "500px",
                    "overflow": "auto", "width": "400px", "text-align": "center"
                },
                showBtn: false,     //不显示下拉按钮
                idField: "parts",
                keyField: "parts",
                clearable: false
            }).on('onSetSelectValue', function (e, keyword, data) {
                thisbody.find("input[name='part_model']").val(data.parts_model);
            }).on('onUnsetSelectValue', function () {
                //不正确
                thisbody.find("input[name='part_model']").val('');
            });
        }

        //验证是否允许结束此维修单
        function checkDoEnd() {
            if (addPartNum > delPartNum) {
                //有新添加配件 需要等待出库 不允许结束
                thisbody.find('#endRepair').attr('lay-tips', '新申请了配件,请等待配件出库后继续维修再结束维修');
                thisbody.find('#endRepair').attr('class', 'layui-btn layui-btn-disabled');
                thisbody.find('#endRepair').attr('disabled','true');
            } else {
                //无添加配件 允许结束
                thisbody.find('#endRepair').removeAttr('lay-tips', '');
                thisbody.find('#endRepair').attr('class', 'layui-btn');
                thisbody.find('#endRepair').removeAttr('disabled');
            }
        }
    });
    exports('controller/repair/repair/startRepair', {});
});
