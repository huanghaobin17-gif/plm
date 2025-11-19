layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'upload', 'tipsType', 'formSelects', 'suggest'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate,
            upload = layui.upload, suggest = layui.suggest, tipsType = layui.tipsType, formSelects = layui.formSelects;
        //初始化搜索建议插件
        suggest.search();
        //初始化tips的选择功能
        tipsType.choose();
        //预计修复日期元素渲染
        laydate.render({
            elem: '#expect_time',
            calendar: true,
            min: now_date
            , trigger: 'click'
            , type: 'datetime'
            , done: function (value, date) {
                if (date.hours == 0 && date.minutes == 0 && date.seconds == 0) {
                    layer.confirm('当前选择日期暂无具体时间,是否需要补充具体时间？', {
                        btn: ['是', '否'],
                        title: '是否需要补充时间'
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            $("#expect_time").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                    });
                }
            }
        });

        //默认自修
        var repair_type = 0;
        //配件搜索建议
        var existPartsInfo = [];


        var thisbody = $('#LAY-Repair-Repair-accept');
        //故障类型 多选框初始配置
        formSelects.render('type', selectParams(1));
        formSelects.btns('type', selectParams(2), selectParams(3));

        //故障问题 多选框初始配置
        formSelects.render('problem', selectParams(1));
        formSelects.btns('problem', selectParams(2));
        //监听联动变化事件
        formSelects.on('type', function () {
            setTimeout(function () {
                var parentid = formSelects.value('type', 'valStr');
                if (!parentid) {
                    //local模式
                    formSelects.data('problem', 'local', {arr: []});
                } else {
                    //server模式
                    formSelects.data('problem', 'server', {
                        url: "accept?action=getRepairProblem&parentid=" + parentid+'&repid='+repid
                    });
                }
            }, 100)
        });

        //过长的维修问题增加tips
        thisbody.find(".xm-select").click(function () {
            var fs = $(this).parents('.xm-select-parent').attr('fs_id');
            if (fs === 'problem') {
                var title = $(this).parent().next().find('dd');
                $.each(title, function (k, v) {
                    if ($.trim($(v).attr('class')) === '') {
                        var span = $(this).find('span').html();
                        if (span.length >= 23) {
                            $(this).find('span').attr('lay-tips', span);
                        }
                    }
                })
            }
        });

        //监听通知-接单步骤
        form.on('submit(notice)', function (data) {
            if (parseInt(data.field['expect_arrive']) > parseInt(data.field['uptime'])) {
                layer.msg('预计到场时间最大不能超过' + data.field['uptime'] + '分钟', {icon: 2}, 1000);
                return false;
            } else {
                var params = data.field;
                submit($, params, acceptUrl);
                return false;
            }
        });
        //监听提交-暂时保存
        form.on('submit(tmp_save)', function (data) {
            //故障问题
            var problem = formSelects.value('problem', 'valStr');
            //正则替换字符
            var replaceReg = new RegExp(",", "g");//g,表示全部替换。
            problem = problem.replace(replaceReg, "|");
            var params = data.field;
            params.tmp_save = 1;
            params.problem = problem;
            var isScene = parseInt(thisbody.find('select[name="is_scene"]').val());

            //非现场解决
            var rType = parseInt(thisbody.find("select[name='repair_type']").val());
            if (rType === 0) {
                //自修，读取配件信息
                params = getPartsInfo(params);
                if (!params) {
                    return false;
                }
            }
            if (rType === 2) {
                //第三方维修，读取报价记录信息
                params = getOfferCompanyInfo(params,'tmp_save');
                if (!params) {
                    return false;
                }
            }

            //获取上传文件信息
            var fileDataTr = thisbody.find('.fileDataTr');
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
            submit($, params, acceptUrl);
            return false;
        });
        //监听提交-检修
        form.on('submit(doAccpet)', function (data) {
            //故障问题
            var problem = formSelects.value('problem', 'valStr');
            //正则替换字符
            var replaceReg = new RegExp(",", "g");//g,表示全部替换。
            problem = problem.replace(replaceReg, "|");
            var params = data.field;
            params.problem = problem;
            if(!params.problem){
                layer.msg("请选择故障问题！", {icon: 2}, 1000);
                return false;
            }
            var isScene = parseInt(thisbody.find('select[name="is_scene"]').val());
            if (isScene === 1) {
                //现场解决，读取配件信息
                if (!$.trim(params.dispose_detail)) {
                    layer.msg("处理详情不能为空！", {icon: 2}, 1000);
                    return false;
                }
                params = getPartsInfo(params);
                if (!params) {
                    return false;
                }
            } else {
                //非现场解决
                var rType = parseInt(thisbody.find("select[name='repair_type']").val());
                if (rType === 0) {
                    //自修，读取配件信息
                    params = getPartsInfo(params);
                    if (!params) {
                        return false;
                    }
                }
                if (rType === 2) {
                    //第三方维修，读取报价记录信息
                    params = getOfferCompanyInfo(params,'doAccpet');
                    if (!params) {
                        return false;
                    }
                }
            }

            //获取上传文件信息
            var fileDataTr = thisbody.find('.fileDataTr');
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
            submit($, params, acceptUrl);
            return false;
        });
        //选择是否现场解决
        form.on('select(FilterIs_scene)', function (data) {
            var scene = parseInt(data.value);
            if (scene === 1) {
                thisbody.find('.is_scene').show();
                thisbody.find('.not_scene').hide();
                thisbody.find('.offer').hide();
                thisbody.find('.addParts').show();
                //thisbody.find('.addParts').hide();
            } else {
                if (repair_type === 0) {
                    thisbody.find('.offer').hide();
                    thisbody.find('.addParts').show();
                } else if (repair_type === 2) {
                    thisbody.find('.offer').show();
                    thisbody.find('.addParts').hide();
                }
                thisbody.find('.is_scene').hide();
                thisbody.find('.not_scene').show();
            }
            // form.render();
        });
        //选择维修性质
        form.on('select(repairType)', function (data) {
            repair_type = parseInt(data.value);
            if (repair_type === 0) {
                thisbody.find('.addParts').show();
                thisbody.find('.offer').hide();
                thisbody.find('.factory').hide();
            } else if (repair_type === 2) {
                thisbody.find('.offer').show();
                thisbody.find('.addParts').hide();
                thisbody.find('.factory').hide();
            } else if (repair_type === 1) {
                thisbody.find('.offer').hide();
                thisbody.find('.addParts').hide();
                thisbody.find('.factory').show();
            }
            // form.render();
        });

        //播放停止语音
        var audio = document.getElementById("audio");
        if(audio){
            var btn = document.getElementById("media");
            btn.onclick = function () {
                if (audio.paused) { //判断当前的状态是否为暂停，若是则点击播放，否则暂停
                    audio.play();
                }else{
                    audio.pause();
                }
            };
        }
        $("#showImages").click(function () {
            var result = {};
            result.start = 0;
            result.data = [];
            $(".imageUrl").each(function (k, v) {
                var imageUrlObj = {};
                imageUrlObj.src = $(v).val();
                imageUrlObj.thumb = $(v).val();
                result.data.push(imageUrlObj);
            });
            //显示相册层
            layer.photos({
                photos: result
                , anim: 5
                , maxmin: false
            });
        });

        //是否出库
        //form.on('checkbox(isOut)', function(data){
        //    var checked = data.elem.checked,is_out = $("input[name='is_out']");
        //    if (checked == true){
        //        is_out.val('1');
        //    }else {
        //        is_out.val('');
        //    }
        //});

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
            //$(".sumPrice").html('0');
            form.render();
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
            showPartsInfo = $.grep(showPartsInfo, function (n) {
                return n;
            }, false);
            result.value = showPartsInfo;
            prevParts = result;
            refreshDropMenu(result);
            return false;
        });

        //编辑配件/服务
        form.on('submit(editPart)', function (data) {
            var params = data.field;
            var editPartId = params.editPartId;
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
            var editPartTr = thisbody.find("#editRow" + editPartId);
            var obj_parts = thisbody.find("input[name='parts']");
            var obj_part_model = thisbody.find("input[name='part_model']");
            var obj_addPartsInfo = thisbody.find(".addPartsInfo");
            $(editPartTr).find('.parts').html(params.parts);
            $(editPartTr).find('.part_model').html(params.part_model);
            $(editPartTr).find('.sum').html(params.sum);
            $(editPartTr).find('.statusName').html(params.statusName);
            layer.msg("修改成功", {icon: 1, time: 1000});
            obj_parts.removeAttr('readonly');
            obj_part_model.removeAttr('readonly');
            thisbody.find("#partReset").show();
            thisbody.find(".remove_part").show();
            obj_addPartsInfo.attr('lay-filter', 'addParts');
            obj_addPartsInfo.html('添加');
            obj_parts.val('');
            obj_part_model.val('');
            //$("input[name='status']").prop('checked',true);
            //$("input[name='is_out']").val('1');
            thisbody.find("input[name='sum']").val('');
            form.render('checkbox');
        });
        //清空输入框数据(第三方重置按钮)
        thisbody.find('#offerReset').on('click', function () {
            thisbody.find("select[name='offer_company']").val('');
            thisbody.find("input[name='offer_contacts']").val('');
            thisbody.find("input[name='telphone']").val('');
            thisbody.find("input[name='total_price']").val('');
            thisbody.find("input[name='invoice']").val('');
            thisbody.find("input[name='cycle']").val('');
            thisbody.find("input[name='remark']").val('');
            form.render();
            return false;
        });
        //清空输入框数据(配件重置按钮)
        thisbody.find('#partReset').on('click', function () {
            thisbody.find("input[name='parts']").val('');
            thisbody.find("input[name='part_model']").val('');
            thisbody.find("input[name='sum']").val('');
            //$("input[name='status']").prop('checked',false);
            //$("input[name='is_out']").val('');
            form.render('checkbox');
            return false;
        });
        //新增报价公司
        form.on('submit(addCompany)', function (data) {
            var repid = thisbody.find("input[name='repid']").val();
            var table_tbody = thisbody.find('.offerTbody').find('tbody.offer_tbody');
            params = data.field;
            params.repid = repid;
            if (!params.offer_company) {
                layer.msg("请选择报价公司", {icon: 2, time: 1000});
                return false;
            } else {
                var existOlisd = params.offer_company;
                $.each(companyInfo, function (k, v) {
                    if (params.offer_company === v.olsid) {
                        params.olsid = v.olsid;
                        params.offer_company = v.sup_name;
                    }
                })
            }
            if (!params.offer_contacts) {
                layer.msg("请填写报价公司联系人！", {icon: 2, time: 1000});
                return false;
            }
            if (!params.telphone) {
                layer.msg("请填写报价公司联系人方式！", {icon: 2, time: 1000});
                return false;
            } else {
                if (!checkTel(params.telphone)) {
                    layer.msg("报价公司联系人方式格式不正确，请填写正确的手机号或座机号码！", {icon: 2, time: 2000});
                    return false;
                }
            }
            if (isOpenOffer_formOffer === DO_STATUS) {
                if (!params.total_price) {
                    layer.msg("请填写服务金额！", {icon: 2, time: 1000});
                    return false;
                } else {
                    var reg = /^\d+(?=\.{0,1}\d+$|$)/;
                    if (!reg.test(params.total_price)) {
                        layer.msg("服务金额格式不正确！", {icon: 2, time: 1000});
                        return false;
                    }
                }
            }
            if (!params.invoice) {
                layer.msg("请填写发票！", {icon: 2, time: 1000});
                return false;
            }
            if (!params.cycle) {
                layer.msg("请填写到货/服务周期！", {icon: 2, time: 1000});
                return false;
            }
            params.offer_company = $.trim(params.offer_company);
            var html = '<tr class="offid_tr" data-offid="" data-olsid="'+params.olsid+'">';
            html += '<td class="company_td" style="text-align: center; vertical-align: middle; ">' + params.offer_company + '</td>';
            html += '<td class="contacts_td" style="text-align: center; padding: 0;">' + params.offer_contacts + '</td>';
            html += '<td class="telphone_td" style="text-align: center; padding: 0;">' + params.telphone + '</td>';
            if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
                html += '<td class="total_price" style="text-align: center; vertical-align: middle;">' + params.total_price + '</td>';
            }
            html += '<td class="invoice_td" style="text-align: center; padding: 0;">' + params.invoice + '</td>';
            html += '<td class="cycle_td" style="text-align: center; padding: 0;">' + params.cycle + '</td>';
            if (isOpenOffer_formOffer !== DO_STATUS) {
                html += '<td style="text-align: center; vertical-align: middle; "><input name="proposal" type="radio" value="' + params.offer_company + '"></td>';
                // html += '<td style="text-align: center; padding: 0;"><input name="proposal_info" type="text" class="layui-textarea"> <textarea></textarea> </td>';
                html += '<td style="text-align: center; padding: 0;"> <textarea name="proposal_info"></textarea> </td>';
            }
            html += '<td class="remark_td" style="text-align: center; padding: 0;">' + params.remark + '</td>';
            if (isOpenOffer_formOffer === DO_STATUS) {
                html += '<td style="width: 30px;text-align: center"><input name="last_decisioin" type="radio" value="' + params.offer_company + '"></td>';
            }
            html += '<td style="width:70px;text-align: center; vertical-align: middle; "><button type="button" class="layui-btn layui-btn-xs layui-btn-danger remove_company" ><i class="layui-icon">&#xe640;</i></button></td>';
            html += '</tr>';
            table_tbody.prepend(html);
            layer.msg('添加成功', {icon: 1}, 1000);
            //清除一条新添加的公司
            var companyHtml = '<option value="">请选择公司</option>';
            $.each(companyInfo, function (k, v) {
                if (existOlisd === v.olsid) {
                    v.status = 0;
                }
            });
            $.each(companyInfo, function (k, v) {
                if (parseInt(v.status) === 1) {
                    companyHtml += '<option value="' + v.olsid + '">' + v.sup_name + '</option>'
                }
            });
            thisbody.find("select[name='offer_company']").html(companyHtml);
            //清空输入框数据
            thisbody.find("select[name='offer_company']").val('');
            thisbody.find("input[name='offer_contacts']").val('');
            thisbody.find("input[name='telphone']").val('');
            thisbody.find("input[name='total_price']").val('');
            thisbody.find("input[name='invoice']").val('');
            thisbody.find("input[name='cycle']").val('');
            thisbody.find("input[name='remark']").val('');
            form.render();
            return false;
        });
        uploadFile = upload.render({
            elem: '#acceptFile'  //绑定元素
            , url: acceptUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = thisbody.find('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = thisbody.find('.addFileTbody');
                    var input = '<input type="hidden" name="file_name" value="' + res.formerly + '">';
                    input += '<input type="hidden" name="save_name" value="' + res.title + '">';
                    input += '<input type="hidden" name="file_type" value="' + res.ext + '">';
                    input += '<input type="hidden" name="file_size" value="' + res.size + '">';
                    input += '<input type="hidden" name="file_url" value="' + res.path + '">';
                    var html = '<tr class="fileDataTr">';
                    html += '<td class="fileName">' + input + res.formerly + '</td>';
                    html += '<td class="addFileuser">' + res.adduser + '</td>';
                    html += '<td class="addFileTime">' + res.thisTime + '</td>';
                    html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
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

        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = thisbody.find('.addFileTbody');
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
            return false;
        });

        //pdf
        form.on('submit(pdf)', function (data) {
            var params = data.field;
            window.location.href = admin_name+'/Public/repairToPdf.html?repid=' + params.repid;
            return false;
        });

        //第三方选择公司联动填充输入框
        form.on('select(companyInfo)', function (data) {
            var companyData = {};
            $.each(companyInfo, function (k, v) {
                if (data.value === v.olsid) {
                    companyData.salesman_name = v.salesman_name;
                    companyData.salesman_phone = v.salesman_phone;
                }
            });
            thisbody.find("input[name='offer_contacts']").val(companyData.salesman_name);
            thisbody.find("input[name='telphone']").val(companyData.salesman_phone);
        });


        thisbody.find('.addTypeAndProblem').on('click', function () {
            var url = admin_name+'/Repair/accept?action=addTypeAndProblem', flag = 1;
            top.layer.open({
                type: 2,
                title: '<i class="layui-icon">&#xe654;</i>' + ' 新增故障问题',
                shade: 0,
                offset: 'r',//弹窗位置固定在右边
                anim: 2,
                scrollbar: false,
                area: ['630px', '100%'],
                closeBtn: 1,
                content: [url],
                end: function () {
                    if (flag === 1) {
                        formSelects.data('type', 'server', {
                            url: admin_name+'/Repair/accept?action=getRepairType'
                        });
                        formSelects.render('type', selectParams(1));
                        formSelects.btns('type', selectParams(2), selectParams(3));
                        formSelects.render('problem', selectParams(1));
                        formSelects.btns('problem', selectParams(2));
                    }
                },
                cancel: function () {
                    flag = 0
                }
            });
            return false;
        });


        //移除自修配件
        $(document).on('click', '.remove_part', function () {
            var a_this = $(this).parent().parent().parent();
            var partsName = a_this.find('.parts').html();
            var parts_model = a_this.find('.part_model').html();
            layer.confirm('确定移除此配件？', {icon: 3, title: '移除提示'}, function (index) {
                a_this.remove();
                layer.close(index);
                //重载搜索建议插件
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
                showPartsInfo = $.grep(showPartsInfo, function (n) {
                    return n;
                }, false);
                result.value = showPartsInfo;
                refreshDropMenu(result);
            });
        });
        //第三方报价 点击移除公司
        $(document).on('click', '.remove_company', function () {
            var a_this = $(this).parent().parent();
            layer.confirm('确定移除此公司报价吗？', {icon: 3, title: '移除提示'}, function (index) {
                a_this.remove();
                thisbody.find('select[name="last_decisioin"]').find('#option_' + a_this.find('.company_td').html()).remove();
                // form.render('select');
                //当添加过配件物料并且默认公司名称是删除的这一家公司 则隐藏配件信息form
                if (a_this.find('.company_td').html() === thisbody.find('input[name="Company"]').val()) {
                    thisbody.find('.matter').hide();
                }
                layer.close(index);
                //清除一条新添加的公司
                var companyHtml = '<option value="">请选择公司</option>';
                $.each(companyInfo, function (k, v) {
                    if (a_this.find('.company_td').html() === v.sup_name) {
                        v.status = 1;
                    }
                });
                $.each(companyInfo, function (k, v) {
                    if (parseInt(v.status) === 1) {
                        companyHtml += '<option value="' + v.olsid + '">' + v.sup_name + '</option>'
                    }
                });
                thisbody.find("select[name='offer_company']").html(companyHtml);
                layui.form.render();
            });
        });
        //获取所有配件信息 - 自修
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
                if (!part_model) {
                    part_model = '--';
                }
                if (!part_partid) {
                    part_partid = '--';
                }
                partname += patrs + '|';
                model += part_model + '|';
                num += sum + '|';
                partid += part_partid + '|';
                //partsStatus += statusName + '|';
            });
            params.partname = partname;
            params.model = model;
            params.num = num;
            params.partid = partid;
            //params.partsStatus = partsStatus;
            return params;
        }

        //获取所有第三方报价信息
        function getOfferCompanyInfo(params,type) {
            var companyOlsid = '';
            var companyName = '';
            var contracts = '';
            var telphone = '';
            var offid = '';
            var invoice = '';
            var totalPrice = '';
            var cycle = '';
            var proposal_info = '';
            var remark = '';
            var company_td = thisbody.find('.company_td');
            if (company_td.length === 0&&type !='tmp_save') {
                layer.msg("无报价公司1！", {icon: 2}, 1000);
                return false;
            }
            thisbody.find('.offid_tr').each(function () {
                companyOlsid += $(this).data('olsid') + '|';
                offid += $(this).data('offid') + '|';
            });
            company_td.each(function () {
                companyName += $(this).html() + '|';
            });
            thisbody.find('.contacts_td').each(function () {
                contracts += $(this).html() + '|';
            });
            thisbody.find('.telphone_td').each(function () {
                telphone += $(this).html() + '|';
            });
            thisbody.find('.invoice_td').each(function () {
                invoice += $(this).html() + '|';
            });
            thisbody.find('.cycle_td').each(function () {
                cycle += $(this).html() + '|';
            });
            thisbody.find('.remark_td').each(function () {
                remark += $(this).html() + '|';
            });

            thisbody.find('.total_price').each(function () {
                totalPrice += $(this).html() + '|';
            });
            params.totalPrice = totalPrice;

            thisbody.find('textarea[name="proposal_info"]').each(function () {
                proposal_info += $(this).val() + '|';
            });

            // //记录厂商ID
            // companyNameArr = companyName.split('|');
            // $.each(companyInfo, function (k, v) {
            //     $.each(companyNameArr, function (k1, v1) {
            //         if (v.sup_name === v1) {
            //             companyOlsid += v.olsid + '|'
            //         }
            //     })
            // });
            params.olsid = companyOlsid;
            params.companyName = companyName;
            params.offid = offid;
            params.contracts = contracts;
            params.telphone = telphone;
            params.invoice = invoice;
            params.cycle = cycle;
            params.proposal = thisbody.find('input:radio[name="proposal"]:checked').val();
            params.remark = remark;
            params.proposal_info = proposal_info;
            if (isOpenOffer_formOffer === DO_STATUS) {
                params.decision_reasion = thisbody.find('textarea[name="decision_reasion"]').val();
                if (!params.last_decisioin&&type !='tmp_save') {
                    layer.msg("请选择最终报价厂家！", {icon: 2}, 1000);
                    return false;
                }
            }
            return params;
        }


        //点击编辑报价按钮
        $(document).on('click', '.save_part', function () {
            var a_this = $(this).parent().parent().parent();
            var partid = a_this.attr('data-partid');
            var parts = a_this.find('.parts').html();
            var part_model = a_this.find('.part_model').html();
            var sum = a_this.find('.sum').html();
            //var statusName = a_this.find('.statusName').html();
            var obj_parts = thisbody.find("input[name='parts']");
            var obj_part_model = thisbody.find("input[name='part_model']");
            var obj_addPartsInfo = thisbody.find(".addPartsInfo");
            //var obj_statusName=thisbody.find("input[name='status']");
            //var obj_isOut=thisbody.find("input[name='is_out']");
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
            layui.form.render('checkbox');
            return false;
        });


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

    });
    exports('controller/repair/repair/accept', {});
});







