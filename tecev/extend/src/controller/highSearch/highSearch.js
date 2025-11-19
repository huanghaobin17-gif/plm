layui.define(function(exports){
    layui.use(['form','formSelects','laydate','suggest'], function(){
        var form = layui.form,formSelects = layui.formSelects,laydate = layui.laydate,suggest = layui.suggest;
        //先更新页面部分需要提前渲染的控件
        form.render();
        //初始化搜索建议插件
        suggest.search();
        //科室名称 多选框初始配置
        formSelects.render('department',selectParams(1));
        formSelects.btns('department',selectParams(2),selectParams(3));
        //供应商 多选框初始配置
        formSelects.render('supplier', selectParams(1));
        formSelects.btns('supplier',selectParams(2),selectParams(3));
        //生产商 多选框初始配置
        formSelects.render('factory', selectParams(1));
        formSelects.btns('factory',selectParams(2),selectParams(3));
        //维修商 多选框初始配置
        formSelects.render('repair', selectParams(1));
        formSelects.btns('repair',selectParams(2),selectParams(3));
        //设备类型 多选框初始配置
        formSelects.render('assetsType', selectParams(1));
        formSelects.btns('assetsType',selectParams(2),selectParams(3));
        //设备状态 多选框初始配置
        formSelects.render('status', selectParams(1));
        formSelects.btns('status',selectParams(2),selectParams(3));

        formSelects.render('assets_helpcat', selectParams(1));
        formSelects.btns('assets_helpcat',selectParams(2),selectParams(3));

        formSelects.render('assets_capitalfrom', selectParams(1));
        formSelects.btns('assets_capitalfrom',selectParams(2),selectParams(3));

        formSelects.render('assets_finance', selectParams(1));
        formSelects.btns('assets_finance',selectParams(2),selectParams(3));

        //日期元素渲染
        lay('.formatDate').each(function(){
            laydate.render({
                elem: this
                ,trigger: 'click'
            });
        });

        //搜索按钮
        form.on('submit(highSearch)', function(data){
            var params = data.field;
            if (params.addDateStartDate && params.addDateEndDate) {
                if (params.addDateEndDate < params.addDateStartDate) {
                    layer.msg('录入时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (params.guaranteeDateStartDate && params.guaranteeDateEndDate) {
                if (params.guaranteeDateEndDate < params.guaranteeDateStartDate) {
                    layer.msg('截保时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (params.factoryDateStartDate && params.factoryDateEndDate) {
                if (params.factoryDateEndDate < params.factoryDateStartDate) {
                    layer.msg('出厂时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (params.storageDateStartDate && params.storageDateEndDate) {
                if (params.storageDateEndDate < params.storageDateStartDate) {
                    layer.msg('入库时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (params.openDateStartDate && params.openDateEndDate) {
                if (params.openDateEndDate < params.openDateStartDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (params.paytimeStartDate && params.paytimeEndDate) {
                if (params.paytimeEndDate < params.paytimeStartDate) {
                    layer.msg('付款时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (params.buy_priceMin) {
                if (params.buy_priceMax == ''){
                    layer.msg('请补充设备原值区间最大值', {icon: 2});
                    return false;
                }
            }
            if (params.buy_priceMax) {
                if (params.buy_priceMin == ''){
                    layer.msg('请补充设备原值区间最小值', {icon: 2});
                    return false;
                }
            }
            var assetsType = '';
            $("input[name='assetsType']:checked").each(function(k,v){
                assetsType += ","+ $(v).val();
            });
            params.assetsType = assetsType;
            params.supplier = formSelects.value('supplier', 'valStr');
            params.factory = formSelects.value('factory', 'valStr');
            params.repair = formSelects.value('repair', 'valStr');
            params.status = formSelects.value('status', 'valStr');
            params.assets_helpcat = formSelects.value('assets_helpcat', 'valStr');
            params.assets_capitalfrom = formSelects.value('assets_capitalfrom', 'valStr');
            params.assets_finance = formSelects.value('assets_finance', 'valStr');
            params.department = formSelects.value('department', 'valStr');
            var storage = window.localStorage;
            storage.setItem('highSearch',JSON.stringify(params));
            //高级查询条件拼接
            var newParams = params;
            newParams.is_domestic = $("input[name='is_domestic']").parent().find(".layui-form-radioed div").html();
            newParams.pay_status = $("input[name='pay_status']").parent().find(".layui-form-radioed div").html();
            var assetsTypeStr = $("input[name='assetsType']").parent().find(".layui-form-checked span");
            var assetsTypeStrHtml = '';
            assetsTypeStr.each(function (k, v) {
                assetsTypeStrHtml += $(v).html() + ',';
            });
            assetsTypeStrHtml = assetsTypeStrHtml.substring(0, assetsTypeStrHtml.lastIndexOf(','));
            newParams.assetsType = assetsTypeStrHtml;
            newParams.assets_level = $("select[name='assets_level']").parent().find(".layui-this").html();
            params.supplier = formSelects.value('supplier', 'nameStr');
            params.factory = formSelects.value('factory', 'nameStr');
            params.repair = formSelects.value('repair', 'nameStr');
            params.status = formSelects.value('status', 'nameStr');
            params.assets_helpcat = formSelects.value('assets_helpcat', 'nameStr');
            params.assets_capitalfrom = formSelects.value('assets_capitalfrom', 'nameStr');
            params.assets_finance = formSelects.value('assets_finance', 'nameStr');
            params.department = formSelects.value('department', 'nameStr');
            var realCondition = {};
            $.each(newParams, function (k, v) {
                if (v != '' && typeof v != 'undefined') {
                    realCondition[k] = v;
                }
            });
            var html = '';
            console.log(realCondition);
            $.each(realCondition, function (k, v) {
                switch (k) {
                    case 'assets':
                        html += '<span class="highLightSearchText">设备名称：</span>' + v + ' ; ';
                        break;
                    case 'adduser':
                        html += '<span class="highLightSearchText">录入人员：</span>' + v + ' ; ';
                        break;
                    case 'assets_level':
                        html += '<span class="highLightSearchText">管理类别：</span>' + v + ' ; ';
                        break;
                    case 'assetsrespon':
                        html += '<span class="highLightSearchText">设备负责人：</span>' + v + ' ; ';
                        break;
                    case 'assnum':
                        html += '<span class="highLightSearchText">设备编号：</span>' + v + ' ; ';
                        break;
                    case 'registration':
                        html += '<span class="highLightSearchText">注册证编号：</span>' + v + ' ; ';
                        break;
                    case 'assorignum':
                        html += '<span class="highLightSearchText">设备原编号：</span>' + v + ' ; ';
                        break;
                    case 'assorignum_spare':
                        html += '<span class="highLightSearchText">备用原编号：</span>' + v + ' ; ';
                        break;
                    case 'file_number':
                        html += '<span class="highLightSearchText">档案盒编号：</span>' + v + ' ; ';
                        break;
                    case 'buy_priceMin':
                        html += '<span class="highLightSearchText">设备原值：</span>' + v + ' - ' + realCondition['buy_priceMax'] + ' ; ';
                        break;
                    case 'category':
                        html += '<span class="highLightSearchText">设备分类：</span>' + v + ' ; ';
                        break;
                    case 'department':
                        html += '<span class="highLightSearchText">所属科室：</span>' + v + ' ; ';
                        break;
                    case 'factory':
                        html += '<span class="highLightSearchText">生产厂商：</span>' + v + ' ; ';
                        break;
                    case 'is_domestic':
                        html += '<span class="highLightSearchText">国产&进口：</span>' + v + '设备 ; ';
                        break;
                    case 'metering_cycle':
                        html += '<span class="highLightSearchText">计量周期：</span>' + v + '天 ; ';
                        break;
                    case 'model':
                        html += '<span class="highLightSearchText">规格型号：</span>' + v + ' ; ';
                        break;
                    case 'patrol_pm_cycle':
                        html += '<span class="highLightSearchText">保养周期：</span>' + v + '天 ; ';
                        break;
                    case 'patrol_xc_cycle':
                        html += '<span class="highLightSearchText">巡查周期：</span>' + v + '天 ; ';
                        break;
                    case 'pay_status':
                        html += '<span class="highLightSearchText">是否付清：</span>' + v + '设备 ; ';
                        break;
                    case 'quality_cycle':
                        html += '<span class="highLightSearchText">质控周期：</span>' + v + '天 ; ';
                        break;
                    case 'repair':
                        html += '<span class="highLightSearchText">维修厂商：</span>' + v + ' ; ';
                        break;
                    case 'status':
                        html += '<span class="highLightSearchText">设备状态：</span>' + v + ' ; ';
                        break;
                    case 'assets_helpcat':
                        html += '<span class="highLightSearchText">辅助分类：</span>' + v + ' ; ';
                        break;
                    case 'assets_capitalfrom':
                        html += '<span class="highLightSearchText">资金来源：</span>' + v + ' ; ';
                        break;
                    case 'assets_finance':
                        html += '<span class="highLightSearchText">财务分类：</span>' + v + ' ; ';
                        break;
                    case 'serialnum':
                        html += '<span class="highLightSearchText">序列号：</span>' + v + ' ; ';
                        break;
                    case 'supplier':
                        html += '<span class="highLightSearchText">供应厂商：</span>' + v + ' ; ';
                        break;
                    case 'addDateStartDate':
                        if (typeof realCondition['addDateEndDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">录入日期：</span> 大于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">录入日期：</span>' + v + ' - ' + realCondition['addDateEndDate'] + ' ; ';
                        }
                        break;
                    case 'addDateEndDate':
                        if (typeof realCondition['addDateStartDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">录入日期：</span> 小于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">录入日期：</span>' + realCondition['addDateStartDate'] + ' - ' + v + ' ; ';
                        }
                        break;
                    case 'factoryDateStartDate':
                        if (typeof realCondition['factoryDateEndDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">出厂日期：</span> 大于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">出厂日期：</span>' + v + ' - ' + realCondition['factoryDateEndDate'] + ' ; ';
                        }
                        break;
                    case 'factoryDateEndDate':
                        if (typeof realCondition['factoryDateStartDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">出厂日期：</span> 小于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">出厂日期：</span>' + realCondition['factoryDateStartDate'] + ' - ' + v + ' ; ';
                        }
                        break;
                    case 'guaranteeDateStartDate':
                        if (typeof realCondition['guaranteeDateEndDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">截保日期：</span> 大于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">截保日期：</span>' + v + ' - ' + realCondition['guaranteeDateEndDate'] + ' ; ';
                        }
                        break;
                    case 'openDateStartDate':
                        if (typeof realCondition['openDateEndDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">启用日期：</span> 大于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">启用日期：</span>' + v + ' - ' + realCondition['openDateEndDate'] + ' ; ';
                        }
                        break;
                    case 'openDateEndDate':
                        if (typeof realCondition['openDateStartDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">启用日期：</span> 小于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">启用日期：</span>' + realCondition['openDateStartDate'] + ' - ' + v + ' ; ';
                        }
                        break;
                    case 'paytimeStartDate':
                        if (typeof realCondition['paytimeEndDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">付款日期：</span> 大于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">付款日期：</span>' + v + ' - ' + realCondition['paytimeEndDate'] + ' ; ';
                        }
                        break;
                    case 'paytimeEndDate':
                        if (typeof realCondition['paytimeStartDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">付款日期：</span> 小于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">付款日期：</span>' + realCondition['paytimeStartDate'] + ' - ' + v + ' ; ';
                        }
                        break;
                    case 'storageDateStartDate':
                        if (typeof realCondition['storageDateEndDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">入库日期：</span> 大于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">入库日期：</span>' + v + ' - ' + realCondition['storageDateEndDate'] + ' ; ';
                        }
                        break;
                    case 'storageDateEndDate':
                        if (typeof realCondition['storageDateStartDate'] == 'undefined') {
                            html += '<span class="highLightSearchText">付款日期：</span> 小于 ' + v + ' ';
                        } else {
                            html += '<span class="highLightSearchText">付款日期：</span>' + realCondition['storageDateStartDate'] + ' - ' + v + ' ; ';
                        }
                        break;
                    case 'assetsType':
                        html += '<span class="highLightSearchText">设备类型：</span>' + v + ' ; ';
                        break;
                    case 'factorynum':
                        html += '<span class="highLightSearchText">出厂编号：</span>' + v + ' ; ';
                        break;
                    case 'remark':
                        html += '<span class="highLightSearchText">设备备注：</span>' + v + ' ; ';
                        break;
                }
            });
            storage.setItem('highSearchStr', html);
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
            return false;
        });

        //设备名称搜索建议
        $("#assets").bsSuggest(
            returnAssets()
        );

        //分类搜索建议
        $("#category").bsSuggest(
            returnCategory('',1)
        );

        //设备编号搜索建议
        $("#assnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='assets']").val(data.assets);
            $("input[name='assorignum']").val(data.assorignum);
        });

        //设备原编号搜索建议
        $("#assorignum").bsSuggest(
            returnAssets('assets','assorignum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="assnum"]').val(data.assnum);
            $('input[name="assets"]').val(data.assets);
        });

        //点击取消键
        $("#cancel").click(function(){
            var storage = window.localStorage;
            storage.removeItem('highSearch');
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
        });

        //过长增加提示
        $(".xm-select").click(function () {
            var title = $(this).parent().next().find('dd');
            $.each(title, function (k, v) {
                if ($(v).attr('class') == ' ') {
                    var span = $(this).find('span').html();
                    if (span.length > 6) {
                        $(this).find('span').attr('title', span);
                    }
                }
            })
        });

        /*
         /选择品牌名称
         */
        $("#dic_brand").bsSuggest(
            returnDicBrand()
        );
    });
    exports('controller/highSearch/highSearch', {});
});
