layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'upload', 'tipsType', 'formSelects','suggest'], function () {
        var layer = layui.layer,
            formSelects = layui.formSelects,
            form = layui.form,
            laydate = layui.laydate,
            upload = layui.upload,
            tipsType = layui.tipsType,
            suggest = layui.suggest,
            $ = layui.jquery;
        //先更新页面部分需要提前渲染的控件
        form.render();
        tipsType.choose();

        //初始化搜索建议插件
        suggest.search();

        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));

        laydate.render(dateConfig('#editOLSContractSign_date'));
        laydate.render(dateConfig('#editOLSContractEnd_date'));
        laydate.render(dateConfig('#editOLSContractCheck_date'));

        form.verify({
            tel: function (value) {
                if (value !== '') {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '号码首尾不能出现下划线\'_\'';
                    }
                    if (!checkTel(value)) {
                        return '请正确填写号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                    }
                }
            },
            checkEmail: function (value) {
                if (value !== '') {
                    if (!/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(value)) {
                        return '请输入正确的邮箱';
                    }
                }
            },
            price: function (value) {
                if (!check_price(value)) {
                    return '请输入正确的合同金额';
                }
            }
        });


        //监听提交
        form.on('submit(add)', function (data) {
            var erro = false;
            var bug_msg = '';
            var params = data.field;
            params.supplier_name = $('select[name="supplier_id"]').find('option[value="' + params.supplier_id + '"]').html();
            //获取分期信息
            var phaseDataTr = $('.phaseDataTr');
            if (phaseDataTr.length > 0) {
                params.pay_amount='';
                params.estimate_pay_date='';
                params.real_pay_date='';
                params.phase='';
                params.phase_sum=0;
                $.each(phaseDataTr, function (key, value) {
                    var phase = $(value).find('.phaseTd').html();
                    var pay_amount = $(value).find('input[name="pay_amount"]').val();
                    var estimate_pay_date = $(value).find('input[name="estimate_pay_date"]').val();
                    var real_pay_date = $(value).find('input[name="real_pay_date"]').val();
                    if (!pay_amount) {
                        bug_msg = '第' + phase + '期合同付款信息 付款金额未补充';
                        erro=true;
                        return true;
                    }
                    if (!estimate_pay_date) {
                        bug_msg = '第' + phase + '期合同付款信息 预计付款时间未补充';
                        erro=true;
                        return true;
                    }
                    params.phase_sum+=Number(pay_amount);
                    params.pay_amount += pay_amount + '|';
                    params.phase += phase + '|';
                    params.estimate_pay_date += estimate_pay_date + '|';
                    params.real_pay_date += real_pay_date + '|';
                });
                if(params.phase_sum!==Number(params.contract_amount)){
                    layer.msg('合同付款明细总金额需要等于合同金额,不需要录入的请移除', {icon: 2, time: 3000});
                    return false;
                }

                if (erro===true) {
                    layer.msg(bug_msg, {icon: 2, time: 3000});
                    return false;
                }
            }
            //获取上传文件信息
            var fileDataTr=$('.fileDataTr');
            if (fileDataTr.length > 0) {
                params.file_name='';
                params.save_name='';
                params.file_type='';
                params.file_size='';
                params.file_url='';
                $.each(fileDataTr, function (key, value) {
                    params.file_name += $(value).find('input[name="file_name"]').val() + '|';
                    params.save_name += $(value).find('input[name="save_name"]').val() + '|';
                    params.file_type +=  $(value).find('input[name="file_type"]').val() + '|';
                    params.file_size +=  $(value).find('input[name="file_size"]').val() + '|';
                    params.file_url +=  $(value).find('input[name="file_url"]').val() + '|';
                });
            }
            console.log(params);
            switch (parseInt(params.contract_type)){
                case parseInt(CONTRACT_TYPE_SUPPLIER):
                    //获取采购设备明细
                    params=getAddPurchaseAssetsData(params);
                    if(params.addAssets_sum!==Number(params.contract_amount)){
                        bug_msg = '采购设备价格总价需要等于合同金额';
                        erro=true;
                    }
                    break;
                case parseInt(CONTRACT_TYPE_REPAIR):
                    //获取维修明细 todo
                    break;
            }
            if (erro===true) {
                layer.msg(bug_msg, {icon: 2, time: 3000});
                return false;
            }
            console.log(params);
            submit($, params, editOLSContractUrl);
            return false;
        });

        var old_contract_type = 0;
        var old_olsid = 0;
        var dateNum = 0;
        var phase = 1;
        //选择合同类型
        form.on('select(contract_type)', function (data) {
            var contract_type = parseInt(data.value);
            if (data.value) {
                if (contract_type !== old_contract_type) {
                    //显示对应div
                    var addContractTypeDiv = $('.addContractTypeDiv');
                    //采购
                    var editOLSContractAddAssets = $('#editOLSContractAddAssets');
                    //维修
                    var editOLSContractAddRepair = $('#editOLSContractAddRepair');
                    switch (contract_type) {
                        case parseInt(CONTRACT_TYPE_SUPPLIER):
                            addContractTypeDiv.hide();
                            editOLSContractAddAssets.show();
                            break;
                        case parseInt(CONTRACT_TYPE_REPAIR):
                            addContractTypeDiv.hide();
                            editOLSContractAddRepair.show();
                            break;
                    }
                    var params = {};
                    params.action = 'getSuppliers';
                    params.contract_type = contract_type;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editOLSContractUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                var html = '<option value="">请选择乙方单位</option>';
                                $.each(data.result, function (key, value) {
                                    html += '<option value="' + value.olsid + '">' + value.sup_name + '</option>';
                                });
                                $('select[name="supplier_id"]').html(html);
                                form.render();
                                old_contract_type = contract_type;

                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请先选择合同类型</option>';
                //选择空名称 复位填充数据
                $('select[name="supplier_id"]').html(html);
                $('.addContractTypeDiv').hide();
                form.render();
                old_contract_type = 0;
            }
        });



        //乙方单位
        form.on('select(supplier_id)', function (data) {
            var olsid = parseInt(data.value);
            if (data.value) {
                if (olsid !== old_olsid) {
                    var params = {};
                    params.action = 'getSalesman';
                    params.olsid = olsid;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editOLSContractUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                $('input[name="supplier_contacts"]').val(data.result.salesman_name);
                                $('input[name="supplier_phone"]').val(data.result.salesman_phone);
                                form.render();
                                old_olsid = olsid;
                            } else {
                                //无录入数据 复位
                                $('input[name="supplier_contacts"]').val('');
                                $('input[name="supplier_phone"]').val('');
                                form.render();
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                //选择空名称 复位填充数据
                $('input[name="supplier_contacts"]').val('');
                $('input[name="supplier_phone"]').val('');
                form.render();
                old_olsid = 0;
            }
        });

        //点击补充厂家
        $(document).on('click', '#addSupplier', function (){
            layer.open({
                id: 'editOLSContractAddSupplier',
                type: 1,
                title: '添加厂商',
                area: ['750px', '450px'],
                offset: 'center',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addSuppliersDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddSuppliers();
                    form.on('submit(addSuppliers)', function (data) {
                        var params = data.field;
                        params.action = 'addSuppliers';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: editOLSContractUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('input[name="sup_num"]').val(data.result.sup_num);
                                    if(old_contract_type!==0){
                                        switch (old_contract_type){
                                            //采购
                                            case parseInt(CONTRACT_TYPE_SUPPLIER):
                                                var arr = params.suppliers_type.split(',');
                                                if( $.inArray('1',arr)>=0 ||  $.inArray('2',arr)>=0){
                                                    var html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                                    $('select[name="supplier_id"]').append(html);
                                                    form.render();
                                                }
                                        }
                                    }
                                    layer.close(layer.index);
                                    layer.msg('补充成功', {icon: 1, time: 3000});
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                        return false;
                    });
                    form.render('select');
                }
            });
        });

        //点击添加期数
        $(document).on('click', '#addPhase', function () {
            var notPhaseDataTr = $('.notPhaseDataTr');
            if (notPhaseDataTr.length > 0) {
                notPhaseDataTr.remove();
            }
            var addPhaseTbody = $('.addPhaseTbody');
            var html = '<tr class="phaseDataTr">';
            html += ' <td class="phaseTd">' + phase + '</td>';
            html += '<td class="pay_amount_td"><input type="text" name="pay_amount" autocomplete="off" placeholder="" value="" class="layui-input"></td>';
            html += '<td><input type="text" name="estimate_pay_date" autocomplete="off" placeholder="请选择预计付款日期" class="layui-input" id="estimate_pay_date' + dateNum + '"></td>';
            html += '<td><input type="text" name="real_pay_date" autocomplete="off" placeholder="请选择付款日期" class="layui-input" id="real_pay_date' + dateNum + '"></td>';
            html += '<td class="pay_status">未付款</td>';
            html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_pay">移除</div></td>';
            addPhaseTbody.append(html);
            laydate.render({
                elem: '#estimate_pay_date' + dateNum
                , calendar: true
                , min: '1'
                , done: function (value, date, endDate) {

                }
            });
            laydate.render({
                elem: '#real_pay_date' + dateNum
                , calendar: true
                , min: '1'
                , done: function (value) {
                    var dateTd = $(this.elem[0]).parent('td');
                    var pay_amount = dateTd.siblings('.pay_amount_td').find('input[name="pay_amount"]').val();
                    if (value !== '' && pay_amount > 0) {
                        dateTd.siblings('.pay_status').html('<span style="color:green">已付款</span>');
                    }
                }
            });
            dateNum++;
            phase++;
        });
        //移除期次
        $(document).on('click', '.del_pay', function () {
            var thisTr = $(this).parents('tr');
            var addPhaseTbody = $('.addPhaseTbody');
            var phaseDataTr = $('.phaseDataTr');
            thisTr.remove();
            if (addPhaseTbody.find('tr').length > 0) {
                $.each(addPhaseTbody.find('tr'), function (key, value) {
                    console.log($(value).find('.phaseTd').html(key + 1));
                })
            } else {
                addPhaseTbody.html('<tr class="notPhaseDataTr"><td colspan="6" style="text-align: center!important;">暂无数据</td></tr>');
            }
            phase--;
            layer.msg('移除成功', {icon: 1}, 1000);
        });

        //上传文件
        uploadFile = upload.render({
            elem: '#editOLSContractFile'  //绑定元素
            , url: editOLSContractUrl //接口
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
                    var notFileDataTr = $('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = $('.addFileTbody');
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
            , error: function (index, upload) {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });
        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = $('.addFileTbody');
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
        });
        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).siblings('input[name="path"]').val();
            var name = $(this).siblings('input[name="name"]').val();
            var ext = $(this).siblings('input[name="ext"]').val();
            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + '相关文件查看',
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
        });

        //已添加的设备
        var old_add_assets = [];
        //点击添加采购设备明细
        $(document).on('click', '#addPurchaseAssets', function () {
            layer.open({
                id: 'editOLSContractOpenAddAssets',
                type: 1,
                title: '添加采购设备明细',
                area: ['670px', '310px'],
                offset: 'center',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addPurchaseAssetsDiv'),
                success: function (layero, index) {
                    //复位
                    resetAddPurchaseAssets();
                    form.on('submit(addPurchaseAssets)', function (data) {
                        var params = data.field;
                        if (old_add_assets[params.assets_name] === true) {
                            layer.msg(params.assets_name + '已添加请勿重复添加', {icon: 2}, 1000);
                            return false;
                        }
                        if (!params.assets_name) {
                            layer.msg('请选择补充需要添加的设备', {icon: 2}, 1000);
                            return false;
                        }
                        if (!params.assets_supplier_id) {
                            layer.msg('请选择补充需要添加的生产厂商', {icon: 2}, 1000);
                            return false;
                        }

                        if (!check_price(params.assets_price)) {
                            layer.msg('请补充单价', {icon: 2}, 1000);
                            return false;
                        }
                        if (!check_num(params.assets_num)) {
                            layer.msg('请补充数量', {icon: 2}, 1000);
                            return false;
                        }
                        params.supplier = $('select[name="assets_supplier_id"]').find('option[value="' + params.assets_supplier_id + '"]').html();
                        params.sum = params.assets_price * params.assets_num;
                        old_add_assets[params.assets_name] = true;
                        var notAssetsDataTr = $('.notAssetsDataTr');
                        if (notAssetsDataTr.length > 0) {
                            notAssetsDataTr.remove();
                        }
                        var addAssetsTbody = $('.addAssetsTbody');
                        var html = '<tr class="assetsDataTr">';
                        html += '<td class="addAssets_assets_td">' + params.assets_name + '</td>';
                        html += '<td class="addAssets_model_td">' + params.model + '</td>';
                        html += '<td class="addAssets_supplier_td">' + '<input type="hidden" name="addAssets_supplier_id" value="' + params.assets_supplier_id + '"><span class="addAssets_supplier_value">' + params.supplier + '</span></td>';
                        html += '<td class="addAssets_price_td">' + params.assets_price + '</td>';
                        html += '<td class="addAssets_num_td">' + params.assets_num + '</td>';
                        html += '<td class="addAssets_sum_td">' + params.sum + '</td>';
                        html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_assets">移除</div></td>';
                        addAssetsTbody.append(html);
                        layer.close(layer.index);
                        layer.msg('添加成功', {icon: 1}, 1000);
                        return false;
                    });
                    form.render('select');
                }
            });
        });
        //移除设备明细
        $(document).on('click', '.del_assets', function () {
            var thisTr = $(this).parents('tr');
            var addAssetsTbody = $('.addAssetsTbody');
            old_add_assets[thisTr.find('.addAssets_assets_td').html()]=false;
            thisTr.remove();
            if (addAssetsTbody.find('tr').length === 0) {
                addAssetsTbody.html('<tr class="notAssetsDataTr"><td colspan="7" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
        });


        //添加明细-补充厂家
        $(document).on('click', '#addAssetsSupplier', function (){
            layer.open({
                id: 'editOLSContractAddSupplier',
                type: 1,
                title: '添加厂商',
                area: ['750px', '450px'],
                offset: 'center',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addSuppliersDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddSuppliers();
                    form.on('submit(addSuppliers)', function (data) {
                        var params = data.field;
                        params.action = 'addSuppliers';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: editOLSContractUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('input[name="sup_num"]').val(data.result.sup_num);
                                    var html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                    $('select[name="assets_supplier_id"]').append(html);
                                    form.render();
                                    layer.close(layer.index);
                                    layer.msg('补充成功', {icon: 1, time: 3000});
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                        return false;
                    });
                    form.render('select');
                }
            });
        });
        //添加明细-补充设备
        $(document).on('click', '#addAssetsDic', function (){
            layer.open({
                id: 'editOLSContractAddAssetsDicDiv',
                type: 1,
                title: '新增设备字典',
                area: ['450px', '500px'],
                offset: 'center',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addAssetsDicDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddSuppliers();
                    form.on('submit(addDic)', function (data) {
                        var params = data.field;
                        params.action = 'addDic';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: editOLSContractUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('select[name="assets_name"]').append('<option value="' + params.assets + '">' + params.assets + '</option>');
                                    form.render();
                                    layer.close(layer.index);
                                    layer.msg(data.msg, {icon: 1, time: 3000});
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                        return false;
                    });
                    form.render('select');
                }
            });
        });

        //已选省份、城市
        var old_provinces=0;
        var old_city=0;
        //选择省份
        form.on('select(provinces)', function (data) {
            var provinces = parseInt(data.value);
            if (data.value) {
                if (provinces !== old_provinces) {
                    var params = {};
                    params.action = 'getCity';
                    params.provinceid = provinces;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editOLSContractUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if(data.result.length>0){
                                    var html='<option value="">请选择城市</option>';
                                    $('select[name="areas"]').html(html);
                                    $.each(data.result,function (key,value) {
                                        html+='<option value="'+value.cityid+'">'+value.city+'</option>';
                                    });
                                    $('select[name="city"]').html(html);
                                }else{
                                    $('select[name="city"]').html('<option>/</option>');
                                    $('select[name="areas"]').html('<option>/</option>');
                                }
                                form.render();
                                old_provinces = provinces;

                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html='<option value="">请选择省份</option>';
                //选择空名称 复位填充数据
                $('select[name="city"]').html(html);
                $('select[name="areas"]').html(html);
                form.render();
                old_provinces=0;
            }
        });
        //选择城市
        form.on('select(city)', function (data) {
            var city = parseInt(data.value);
            if (data.value) {
                if (city !== old_city) {
                    var params = {};
                    params.action = 'getAreas';
                    params.cityid = city;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editOLSContractUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if(data.result.length>0) {
                                    var html = '<option value="">请选择区/城镇</option>';
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.areaid + '">' + value.area + '</option>';
                                    });
                                    $('select[name="areas"]').html(html);
                                }else{
                                    $('select[name="areas"]').html('<option value="">/</option>');
                                }
                                form.render();
                                old_city = city;
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html='<option value="">请选择城市</option>';
                //选择空名称 复位填充数据
                $('select[name="areas"]').html(html);
                form.render();
                old_city=0;
            }
        });



        //复位添加字典Div from
        var getCat=true;
        function resetAddSuppliers() {
            var catid_obj=$('select[name="catid"]');
            $('input[name="assets"]').val('');
            formSelects.value('assets_category', []);
            $('input[name="salesman_name"]').val('');
            $('input[name="salesman_phone"]').val('');
            $('input[name="address"]').val('');
            catid_obj.val('');
            $('select[name="city"]').html('<option value="">请选择省份</option>');
            $('select[name="areas"]').html('<option value="">请选择城市</option>');

            if(getCat===true){
                getDicCategory();
                var cathtml = initsuggestCate(current_hosid);
                catid_obj.html('');
                catid_obj.html(cathtml);
            }

            form.render();
        }

        //复位添加采购设备明细Div from
        function resetAddPurchaseAssets() {
            $('select[name="assets_name"]').val('');
            $('input[name="model"]').val('');
            $('select[name="assets_supplier_id"]').val('');
            $('input[name="assets_price"]').val('');
            $('input[name="assets_num"]').val('');
            form.render();
        }

        //获取采购设备明细
        function getAddPurchaseAssetsData(params) {
            //获取上传文件信息
            var assetsDataTr=$('.assetsDataTr');
            if (assetsDataTr.length > 0) {
                params.addAssets_assets='';
                params.addAssets_model='';
                params.addAssets_supplier='';
                params.addAssets_supplier_id='';
                params.addAssets_price='';
                params.addAssets_num='';
                params.addAssets_sum=0;
                $.each(assetsDataTr, function (key, value) {
                    params.addAssets_assets += $(value).find('.addAssets_assets_td').html() + '|';
                    params.addAssets_model += $(value).find('.addAssets_model_td').html() + '|';
                    params.addAssets_supplier += $(value).find('.addAssets_supplier_value').html() + '|';
                    params.addAssets_supplier_id += $(value).find('input[name="addAssets_supplier_id"]').val() + '|';
                    params.addAssets_price += $(value).find('.addAssets_price_td').html() + '|';
                    params.addAssets_num += $(value).find('.addAssets_num_td').html() + '|';
                    params.addAssets_sum+=Number($(value).find('.addAssets_sum_td').html());
                });
            }
            return params;
        }

        //获取分类
        function initsuggestCate(id) {
            var html = '<option value=""></option>';
            $.ajax({
                type: "POST",
                url: admin_name+'/Public/getAllCategorySearch?hospital_id='+id,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                //成功返回之后调用的函数
                async:false,
                success: function (data) {
                    getCat=false;
                    if(data.value.length > 0){
                        $.each(data.value,function (i,item) {
                            if(item.parentid > 0){
                                html += '<option value="'+item.catid+'"> ➣ '+item.category+'</option>';
                            }else{
                                html += '<option value="'+item.catid+'">'+item.category+'</option>';
                            }
                        });
                    }else{
                        html = '';
                    }
                }
            });
            return html;
        }

        //选择字典类型
        function getDicCategory() {
            $("#dic_category_add").bsSuggest('init',
                returnDicCategory(current_hosid)
            );
        }

    });
    exports('controller/offlineSuppliers/offlineSuppliers/editOLSContract', {});
});
