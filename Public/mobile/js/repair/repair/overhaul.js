var savePartsData = {};//定义一个全局变量用于最终保存配件
var saveCompanyData = {};//定义一个全局变量用于最终保存维修商
layui.use(['form', 'formSelects'], function () {
    var form = layui.form, formSelects = layui.formSelects, bodyObj = $("body");

    //初始化下方导航栏菜单
    menuListSpread();

    $("#expect_time").calendar();


    //默认自修
    //var repair_type = 0;
    //默认现场解决
    var scene = 1;

    /*故障类型 多选框初始配置*/
    formSelects.render('isSceneRepairType', selectParams(1));
    formSelects.btns('isSceneRepairType', selectParams(2));
    /**/
    saveselect(repair_type);
    /*故障问题 多选框初始配置*/
    formSelects.render('isSceneRepairProblem', selectParams(1));
    formSelects.btns('isSceneRepairProblem', selectParams(2));
    /**/


    //选择是否现场解决
    form.on('select(filterIs_scene)', function (data) {
        scene = parseInt(data.value);
        if (scene === 1) {
            $('.is_scene').show();
            $('.repair_type_class').show();
            $('.not_scene').hide();
            $('.offer').hide();
            $('.addParts').show();
        } else {
            if (repair_type === 0) {
                $('.offer').hide();
                $('.addParts').show();
            } else if (repair_type === 2) {
                $('.offer').show();
                $('.addParts').hide();
            }
            $('.is_scene').hide();
            $('.not_scene').show();
        }
    });

    $('#zd').click(function () {
        var repid = $('input[name="repid"]').val();
        var departid = $(this).attr('data-did');
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: 'accept',
            data: {departid:departid,repid:repid,action:'getengineer'},
            dataType: "json",
            success: function (data) {
                $('select[name="edit_engineer"]').empty();
                var html = '';
                $.each(data.data, function (k,v) {
                    if (v.username==data.response) {
                        html+='<option value="'+v.username+'" selected="selected">'+v.username+'</option>';
                    }else{
                        html+='<option value="'+v.username+'">'+v.username+'</option>';
                    }
                });
                $('select[name="edit_engineer"]').append(html);
                form.render();
            },
            error: function () {
                layer.msg('网络访问失败', {icon: 2, time: 3000});
            }
        });
        layer.open({
            type: 1,
            content: $('#update_user'),
            title:'维修转单',
            area: ['320px', '420px'],
            success: function () {
                form.on('submit(edit_engineer)', function () {
                    var params = {};
                    params.repid = repid;
                    params.edit_engineer = $('#edit_engineer').val();
                    params.action = 'edit_engineer';
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: 'accept',
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status==1) {
                                layer.msg(data.msg, {icon: 1, time: 2000},function () {
                                    window.location.href='/M/Repair/ordersLists.html?action=overhaulLists';
                                });
                            }else{
                                layer.msg(data.msg, {icon: 2, time: 2000});
                            }


                        }
                    })
                })
            }
        });
        return false;
    });

    //扫码检修
    $(document).on('click', '#scanQRcode_signin', function () {
        wx.getLocation({
            type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
            success: function (res) {
                //var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                //var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                //var speed = res.speed; // 速度，以米/每秒计
                //var accuracy = res.accuracy; // 位置精度
                var latitude = 0;
                var longitude = 0;
                var params = {};
                params.latitude = res.latitude;
                params.longitude = res.longitude;
                var url = admin_name+'/Public/get_gps.html';
                $.ajax({
                    type: "post",
                    data: params,
                    url: url,
                    async: false,
                    //返回数据的格式
                    datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
                    success: function (data) {
                        if(data.status === 1){
                            latitude = data.info['latitude'];
                            longitude = data.info['longitude'];
                        }
                    },
                    //调用出错执行的函数
                    error: function () {
                        //请求出错处理
                        layer.msg('服务器繁忙！', {icon: 2, time: 2000});
                    }
                });

                wx.scanQRCode({
                    needResult: 1,
                    scanType: ["qrCode", "barCode"],
                    desc: 'scanQRCode desc',
                    success: function (res) {
                        var assnum = res.resultStr;
                        if (assnum.indexOf("ODE_") > 0) {
                            assnum = res.resultStr.substr(9);
                        }
                        var params = {};
                        params.assnum = assnum;
                        params.latitude = latitude;
                        params.longitude = longitude;
                        params.action = 'sign_in';
                        var url = mobile_name+'/Repair/accept.html';
                        var res_status = 0;
                        var res_msg = '服务器繁忙！';
                        $.ajax({
                            type: "POST",
                            data: params,
                            url: url,
                            async: false,
                            //返回数据的格式
                            datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
                            success: function (res) {
                                res_status = res.status;
                                res_msg = res.msg;
                            },
                            //调用出错执行的函数
                            error: function () {
                                //请求出错处理
                                layer.msg('服务器繁忙！', {icon: 2, time: 2000});
                            }
                        });
                        if(res_status == 1){
                            layer.msg(res_msg, {icon: 1},function () {
                                $('.wx_sign_in').hide();
                            });
                        }else{
                            layer.msg(res_msg, {icon: 2});
                        }
                    }
                });
            }
        });
        return false;
    });

    //选择维修性质
    form.on('select(repairType)', function (data) {
        repair_type = parseInt(data.value);
        if (repair_type === 0) {
            $('.addParts').show();
            $('.offer').hide();
            $('.factory').hide();
        } else if (repair_type === 2) {
            $('.offer').show();
            $('.addParts').hide();
            $('.factory').hide();
        } else if (repair_type === 1) {
            $('.offer').hide();
            $('.addParts').hide();
            $('.factory').show();
        }
    });
    function saveselect(repair_type){
        if (repair_type === 0) {
            $('.addParts').show();
            $('.offer').hide();
            $('.factory').hide();
        } else if (repair_type === 2) {
            $('.offer').show();
            $('.addParts').hide();
            $('.factory').hide();
        } else if (repair_type === 1) {
            $('.offer').hide();
            $('.addParts').hide();
            $('.factory').show();
        }
    }


    //监听联动变化事件
    formSelects.on('isSceneRepairType', function () {
        setTimeout(function () {
            var parentid = formSelects.value('isSceneRepairType', 'valStr');
            if (!parentid) {
                //local模式
                formSelects.data('isSceneRepairProblem', 'local', {arr: []});
            } else {
                //server模式
                formSelects.data('isSceneRepairProblem', 'server', {
                    url: "accept?action=getRepairProblem&parentid=" + parentid
                });
            }
        }, 100)
    });


    /*配件名称 多选框初始配置及动态选项*/
    formSelects.render('partsSelect', selectParams(1));
    formSelects.btns('partsSelect', selectParams(2));
    formSelects.on('partsSelect', function (id, vals) {
        var partsListObj = $(".partsList"), newPartsHtml = '',
            emptyPartsHtml = '<div class="weui-cells"><p class="emptyNum">暂无配件</p></div>';
        partsListObj.html("");

        $.each(vals, function (k, v) {
            newPartsHtml += changePartsLists(v.name);
        });
        if (vals.length === 0) {
            partsListObj.html(emptyPartsHtml);
        } else {
            partsListObj.html(newPartsHtml);
        }
    }, true);

    //组织配件数据
    function changePartsLists(d) {
        var html = '<div class="weui-cells">';
        html += '<div class="weui-cell">';
        html += '<div class="weui-cell__bd">';
        html += '<p style="font-size: 14px;">' + d + '</p>';
        html += '</div>';
        html += '<div class="weui-cell__ft">';
        html += '<div class="weui-count">';
        html += '<a class="weui-count__btn weui-count__decrease"></a>';
        html += '<input class="weui-count__number" type="number" value="1">';
        html += '<a class="weui-count__btn weui-count__increase"></a>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    //保存勾选的配件
    bodyObj.on("click", ".saveParts", function () {
        var partsListsObj = $(".partsList").children(".weui-cells");
        var partsTableObj = $(".partsTable").find("tbody");
        var savePartsList = "", emptyPartsObj = $(".emptyParts");
        var partsNameData = [], partsNumData = [], partsModelData = [];
        var emptyHtml = '<tr class="emptyParts"><td colspan="3" style="text-align: center;">暂无配件</td></tr>';
        //处理数据
        $.each(partsListsObj, function (k, v) {
            var name = $(v).find(".weui-cell__bd p").html(),
                num = $(v).find(".weui-cell__ft .weui-count__number").val();
            if (!name || !num) {
                return false;
            } else {
                var startStr = name.indexOf("（");
                var partsName = name.substring(0, startStr);
                var model = name.substring(startStr + 6, name.length - 1);
                savePartsList += changeSavePartsList(partsName, num, model);
                partsNameData.push(partsName);
                partsNumData.push(num);
                partsModelData.push(model);
            }
        });

        if (savePartsList === "") {
            closePopup("addPartsDiv");
            partsTableObj.html(emptyHtml);
            return false;
        } else {
            partsTableObj.html(savePartsList);
            emptyPartsObj.remove();
            closePopup("addPartsDiv");
        }
    });

    /**
     * 关闭添加配件弹层 更改页面表格
     * @param name 配件名称
     * @param num 配件数量
     * @param model 规格型号
     * @returns string
     */
    function changeSavePartsList(name, num, model) {
        var html = "<tr class='tr_part'>";
        html += "<td class='parts'>" + name + "</td>";
        html += "<td class='part_model'>" + model + "</td>";
        html += "<td class='sum'>" + num + "</td>";
        html += "</tr>";
        return html;
    }


    /*第三方维修商 多选框初始配置及动态选项*/
    formSelects.render('companySelect', selectParams(1));
    formSelects.btns('companySelect', selectParams(2));

    formSelects.on('companySelect', function (id, vals) {
        var companyOthersObj = $(".companyOthers");
        var newCompanyHtml = '';
        var emptyCompanyHtml = '<div class="weui-cells"><p class="emptyNum">暂无维修商</p></div>';
        companyOthersObj.html("");
        $.each(vals, function (k, v) {
            newCompanyHtml += changeCompanyList(v, k);
        });
        companyOthersObj.html(newCompanyHtml);
        if (vals.length === 0) {
            companyOthersObj.html(emptyCompanyHtml);
        }
        form.render("radio");
    }, true);


    /**
     * 维修周期列表
     * @param value val
     * @param k 第几次循环
     * @returns {string}
     */
    function changeCompanyList(value, k) {
        var companyData={};
        $.each(companyInfo, function (k, v) {
            if (value.value === v.olsid) {
                companyData.salesman_name = v.salesman_name;
                companyData.salesman_phone = v.salesman_phone;
            }
        });
        var html = '<div class="selectCompany">';
        html += '<div class="weui-cells__title" data-offid="" data-olsid="' + value.value + '">' + value.name + '</div>';

        html += '<div class="weui-cells">';

        html += '<div class="weui-cell">';
        html += '<div class="layui-col-xs4">';
        html += '<div class="weui-cell__bd"><span class="rquireCoin"> * </span>联系人：</div>';
        html += '</div>';
        html += '<div class="layui-col-xs9">';
        html += '<div class="weui-cell__ft">';
        html += '<input type="text" name="offer_contacts" value="'+companyData.salesman_name+'" class="layui-input">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '<div class="weui-cell">';
        html += '<div class="layui-col-xs4">';
        html += '<div class="weui-cell__bd"><span class="rquireCoin"> * </span>联系号码：</div>';
        html += '</div>';
        html += '<div class="layui-col-xs9">';
        html += '<div class="weui-cell__ft">';
        html += '<input type="text" name="telphone" value="'+companyData.salesman_phone+'" class="layui-input">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '<div class="weui-cell">';
        html += '<div class="layui-col-xs4">';
        html += '<div class="weui-cell__bd"><span class="rquireCoin"> * </span>发票：</div>';
        html += '</div>';
        html += '<div class="layui-col-xs9">';
        html += '<div class="weui-cell__ft">';
        html += '<input type="radio" name="invoice' + value.value + '" value="专票" title="专票" checked="">';
        html += '<input type="radio" name="invoice' + value.value + '" value="普票" title="普票">';
        html += '<input type="radio" name="invoice' + value.value + '" value="无票" title="无票">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '<div class="weui-cell">';
        html += '<div class="layui-col-xs4">';
        html += '<div class="weui-cell__bd"><span class="rquireCoin"> * </span> 维修周期：</div>';
        html += '</div>';

        html += '<div class="layui-col-xs9">';
        html += '<div class="weui-cell__ft">';
        html += '<div class="priceInputDiv" style="width: 6.2rem;margin-left: 0">';
        html += '<div class="weui-count">';
        html += '<a class="weui-count__btn weui-count__decrease"></a>';
        html += '<input class="weui-count__number" type="number" name="cycle" value="1">天';
        html += '<a class="weui-count__btn weui-count__increase" style="margin-left: 0.625rem;"></a>';
        html += '</div>';
        html += '</div>';

        html += '<div class="priceRadioDiv" style="margin-left: 0.7rem">';
        if (isOpenOffer_formOffer === DO_STATUS) {
            html += '<input type="radio" name="last_decisioin" value="' + value.name + '" title="最终选择">';
        } else {
            html += '<input type="radio" name="proposal" value="' + value.name + '" title="建议选择">';
        }
        html += '</div>';

        html += '</div>';
        html += '</div>';
        html += '</div>';
        if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
            html += '<div class="weui-cell">';
            html += '<div class="layui-col-xs4">';
            html += '<div class="weui-cell__bd"><span class="rquireCoin"> * </span>金额：</div>';
            html += '</div>';
            html += '<div class="layui-col-xs9">';
            html += '<div class="weui-cell__ft">';
            html += '<div class="priceInputDiv">';
            html += '<input type="number" name="price" autocomplete="off" class="layui-input priceInput">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        }
        html += '</div>';
        html += '</div>';
        return html;
    }



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



    //添加配件按钮
    bodyObj.on("click", ".addPartsButton", function () {
        openPopup(".addPartsDiv");
    });
    //添加维修商按钮
    bodyObj.on("click", ".addCompanyButton", function () {
        openPopup(".addCompanyDiv");
    });
    //关闭添加配件、维修商按钮按钮
    bodyObj.on("click", ".weui-popup__modal .weui-icon-cancel", function () {
        var thisClass = $(this).parents(".weui-popup__container"), thisClassName = '';
        if (thisClass.hasClass("addPartsDiv")) {
            thisClassName = 'addPartsDiv';
        }
        closePopup(thisClassName);
    });

    //计数器按钮
    bodyObj.on("click", ".weui-count__decrease", function () {
        var num = $(this).parent().find('.weui-count__number');
        if (parseInt(num.val()) <= 1) {
            $.toptip('禁止操作，无法小于1', 'error');
        } else {
            num.val(parseInt(num.val()) - 1);
        }
    });
    bodyObj.on("click", ".weui-count__increase", function () {
        var num = $(this).parent().find('.weui-count__number');
        num.val(parseInt(num.val()) + 1);
    });


    /*保存勾选的维修商*/
    form.on('submit(saveCompany)', function (data) {
        var params = data.field;
        var companyTableObj = $(".companyTable").find("tbody");
        var newCompanyHtml = '';
        var emptyHtml = '<tr class="emptyCompany"><td colspan="4" style="text-align: center;" >暂无维修商</td></tr>';
        var selectCompanyObj = $('.selectCompany');
        var choice = '';
        if (selectCompanyObj.length === 0) {
            closePopup();
            companyTableObj.html(emptyHtml);
        } else {
            var thisCompanyData = {};
            var error = '', errorStatus = false;
            if (isOpenOffer_formOffer === DO_STATUS) {
                if (!params.last_decisioin) {
                    $.toptip('请选择一家最终维修商', 'error');
                    return false;
                }
                choice = params.last_decisioin;
            } else {
                choice = params.proposal;
            }
            $.each(selectCompanyObj, function (k, v) {
                thisCompanyData[k] = {};
                thisCompanyData[k].companyName = $(v).find('.weui-cells__title').html();
                thisCompanyData[k].olsid = $(v).find('.weui-cells__title').data('olsid');
                thisCompanyData[k].offid = $(v).find('.weui-cells__title').data('offid');
                thisCompanyData[k].cycle = $(v).find('input[name="cycle"]').val();
                thisCompanyData[k].offer_contacts = $(v).find('input[name="offer_contacts"]').val();
                thisCompanyData[k].telphone = $(v).find('input[name="telphone"]').val();
                thisCompanyData[k].invoice = $(v).find('input[name="invoice' + thisCompanyData[k].olsid + '"]:checked').val();
                if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
                    thisCompanyData[k].price = $(v).find('input[name="price"]').val();
                    if (!thisCompanyData[k].price) {
                        errorStatus = true;
                        error = '请补充' + thisCompanyData[k].companyName + '的报价金额';
                        return false;
                    }
                }
                newCompanyHtml += changeSaveCompanyList(thisCompanyData[k], choice)
            });
            if (error) {
                $.toptip(error, 'error');
                return false;
            }
            $('input[name="choice"]').val(choice);
            companyTableObj.html(newCompanyHtml);
            closePopup();
        }
        return false;
    });

    /**
     * 关闭添加维修商弹层 更改页面表格
     * @param result
     * @param choose 选择公司名称
     * @returns {string}
     */
    function changeSaveCompanyList(result, choose) {
        var trueIcon = '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>', chooseHtml = '';
        if (result.companyName === choose) {
            chooseHtml = trueIcon;
        } else {
            chooseHtml = '';
        }
        var html = '<tr class="company_tr" data-companyName="' + result.companyName + '" data-olsid="' + result.olsid + '" data-offid="' + result.offid + '" data-cycle="' + result.cycle + '" data-invoice="' + result.invoice + '" data-offer_contacts="' + result.offer_contacts + '"  data-telphone="' + result.telphone + '"';
        if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
            html += 'data-price="' + result.price + '">';
        } else {
            html += '>'
        }
        html += '<td>' + result.companyName + '</td>';
        html += '<td>' + result.cycle + '天</td>';
        html += '<td>' + result.invoice + '<br>';
        if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
            html += result.price + '元';
        }
        html += '</td>';
        html += '<td>' + chooseHtml + '</td>';
        html += '</tr>';
        return html;
    }


    //超宽屏幕下 添加配件里的select框
    if (screen.width >= 768) {
        $(".addPartsDiv .xm-select-parent").css('width', 'auto');
        $(".addCompanyDiv .xm-select-parent").css('width', 'auto');
    }

    //打开遮罩层
    function openPopup(idDom) {
        $(idDom).popup();
        bodyObj.css("position", "fixed");
        bodyObj.css("overflow", "hidden");
    }

    //关闭遮罩层
    function closePopup(thisDomClass) {
        bodyObj.css("position", "relative");
        bodyObj.css("overflow", "auto");
        $.closePopup();
        if (thisDomClass === 'addPartsDiv') {
            $('html, body').animate({scrollTop: $(".addPartsButton").offset().top}, 100);
        } else {
            $('html, body').animate({scrollTop: $(".addCompanyButton").offset().top}, 100);
        }
    }
    //暂存
    form.on('submit(tmp_save)', function (data) {
        var params = data.field;
        var problem = formSelects.value('isSceneRepairProblem', 'valStr');
        //正则替换字符
        var replaceReg = new RegExp(",", "g");//g,表示全部替换。
        problem = problem.replace(replaceReg, "|");
        params.problem = problem;
        params.tmp_save = 1;
        if (parseInt(params.is_scene) === 1) {
            //现场解决，读取配件信息
            var dispose_detailObj = $("input[name='dispose_detail']");
            if (!$.trim(params.dispose_detail)) {
                $.toptip('处理详情不能为空', 'error');
                dispose_detailObj.addClass("layui-form-danger");
                return false;
            }else{
                dispose_detailObj.removeClass("layui-form-danger");
            }
        } else {
            //非现场解决
            var rType = parseInt($("select[name='repair_type']").val());
            if (rType === 0) {
                //自修，读取配件信息
                params = getPartsInfo(params);
            }
            if (rType === 2) {
                //第三方维修，读取报价记录信息
                params = getOfferCompanyInfo(params,'tmp_save');
            }
        }
        if(params.expect_time){
            var reg=/\//g;
            params.expect_time=params.expect_time.replace(reg,'-');
        }
        //获取上传文件信息
        var fileDataTr = $('.fileDataTr');
        if (fileDataTr.length > 0) {
            params.file_name = '';
            params.save_name = '';
            params.file_type = '';
            params.file_size = '';
            params.file_url = '';
            $.each(fileDataTr, function (key, value) {
                params.file_name += $(value).find('img').data('name')+ '|';
                params.save_name += $(value).find('img').data('save')+ '|';
                params.file_type += $(value).find('img').data('type')+ '|';
                params.file_size += $(value).find('img').data('size')+ '|';
                params.file_url += $(value).find('img').attr('src')+ '|';
            });
        }
        params.action='tmp_save';
        submit(params, overhaulUrl,mobile_name+'/Repair/ordersLists.html?action=overhaulLists');
        return false;
    });

    //最终保存
    form.on('submit(submit)', function (data) {
        var params = data.field;
        var problem = formSelects.value('isSceneRepairProblem', 'valStr');
        //正则替换字符
        var replaceReg = new RegExp(",", "g");//g,表示全部替换。
        problem = problem.replace(replaceReg, "|");
        params.problem = problem;
        if (parseInt(params.is_scene) === 1) {
            //现场解决，读取配件信息
            var dispose_detailObj = $("input[name='dispose_detail']");
            params = getPartsInfo(params);
            if (!$.trim(params.dispose_detail)) {
                $.toptip('处理详情不能为空', 'error');
                dispose_detailObj.addClass("layui-form-danger");
                return false;
            }else{
                dispose_detailObj.removeClass("layui-form-danger");
            }
        } else {
            //非现场解决
            var rType = parseInt($("select[name='repair_type']").val());
            console.log(rType);
            if (rType === 0) {
                //自修，读取配件信息
                params = getPartsInfo(params);
            }
            if (rType === 2) {
                //第三方维修，读取报价记录信息
                params = getOfferCompanyInfo(params,'overhaul');
            }
        }
        if(params.expect_time){
            var reg=/\//g;
            params.expect_time=params.expect_time.replace(reg,'-');
        }

        //获取上传文件信息
        var fileDataTr = $('.fileDataTr');
        if (fileDataTr.length > 0) {
            params.file_name = '';
            params.save_name = '';
            params.file_type = '';
            params.file_size = '';
            params.file_url = '';
            $.each(fileDataTr, function (key, value) {
                params.file_name += $(value).find('img').data('name')+ '|';
                params.save_name += $(value).find('img').data('save')+ '|';
                params.file_type += $(value).find('img').data('type')+ '|';
                params.file_size += $(value).find('img').data('size')+ '|';
                params.file_url += $(value).find('img').attr('src')+ '|';
            });
        }
        params.action='overhaul';
        submit(params, overhaulUrl,mobile_name+'/Repair/ordersLists.html?action=overhaulLists');
        return false;
    });


    //获取所有配件信息 - 自修
    function getPartsInfo(params) {
        var partname = '';
        var model = '';
        var num = '';
        var partid = '';
        //var partsStatus = '';
        $('.tr_part').each(function () {
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


    function getOfferCompanyInfo(params,type) {
        var companyName = '';
        var offid = '';
        var olsid = '';
        var invoice = '';
        var telphone = '';
        var offer_contacts = '';
        var totalPrice = '';
        var cycle = '';
        var company_tr = $('.company_tr');
        if (company_tr.length === 0&&type =="overhaul") {
            $.toptip('无报价公司!', 'error');
            return false;
        }
        company_tr.each(function () {
            companyName += $(this).data('companyname') + '|';
            olsid += $(this).data('olsid') + '|';
            offid += $(this).data('offid') + '|';
            cycle += $(this).data('cycle') + '|';
            invoice += $(this).data('invoice') + '|';
            offer_contacts += $(this).data('offer_contacts') + '|';
            telphone += $(this).data('telphone') + '|';
            if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
                totalPrice += $(this).data('price') + '|';
            }
        });
        if (isOpenOffer_formOffer !== NOT_DO_STATUS) {
            params.totalPrice = totalPrice;
        }
        params.olsid = olsid;
        params.companyName = companyName;
        params.offid = offid;
        params.invoice = invoice;
        params.cycle = cycle;
        params.telphone = telphone;
        params.contracts = offer_contacts;

        if (isOpenOffer_formOffer === DO_STATUS) {
            params.last_decisioin = $('input[name="choice"]').val();
            if (!params.last_decisioin&&type == "overhaul") {
                $.toptip('请选择最终报价厂家!', 'error');
                return false;
            }
        } else {
            params.proposal = $('input[name="choice"]').val();
        }
        return params;
    }

    //监听建议选择按钮
    form.on('radio(suggestRadio)', function () {
        $(".priceRadioDiv .layui-form-radio").each(function (k, v) {
            if ($(v).hasClass("layui-form-radioed")) {
                $(v).parents(".weui-cell").next().show();
            } else {
                $(v).parents(".weui-cell").next().hide();
            }
        });
    });



    var filename = '';
    var add_picObj = $('.add_pic'), add_pic = add_picObj[0];
    var fileElemObj = $('#fileElem'), fileElem = fileElemObj[0];

    function changeAddPic() {
        add_picObj = $('.add_pic');
        add_pic = add_picObj[0];
        fileElemObj = $('#fileElem');
        fileElem = fileElemObj[0];
        add_pic.addEventListener("click", function (e) {
            if (fileElem) {
                fileElem.click();
            }
            e.preventDefault(); // prevent navigation to "#"
        }, false);
        $("#expect_time").calendar({
            minDate:now_date
        });
    }

    //表单置顶
    bodyObj.on("click", ".upFormButton", function () {
        var formDivObj = $(".formDiv"), emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        /*动画区域*/
        var moveAnimatedDivObj = $(".moveAnimatedDiv");
        moveAnimatedDivObj.removeClass("animated fadeIn");
        setTimeout(function () {
            moveAnimatedDivObj.addClass("animated fadeIn");
        }, 100);
        emptyDivObj.addClass("animated slideInUp");
        $('html, body').animate({scrollTop: '0px'},100);
        localStorage.setItem("overhaul_upFormButton",'top');
        $('select[name="is_scene"]').val(scene);
        $('select[name="repair_type"]').val(repair_type);
        changeAddPic();
        form.render("select");
    });
    //表单置底
    bodyObj.on("click", ".downFormButton", function () {
        var formDivObj = $(".formDiv"), emptyDivObj = $(".emptyDiv");
        formDivObj.append(emptyDivObj.html());
        formDivObj.show();
        emptyDivObj.html("");
        emptyDivObj.hide();
        $(".downFormButton").hide();
        $(".upFormButton").show();
        /*动画区域*/
        var moveAnimatedDivObj = $(".moveAnimatedDiv");
        moveAnimatedDivObj.removeClass("animated fadeIn");
        setTimeout(function () {
            moveAnimatedDivObj.addClass("animated fadeIn");
        }, 100);
        formDivObj.addClass("animated slideInDown");
        $('html, body').animate({scrollTop: $(document).height()},100);
        localStorage.setItem("overhaul_upFormButton",'bottom');
        $('select[name="is_scene"]').val(scene);
        $('select[name="repair_type"]').val(repair_type);
        changeAddPic();
        form.render("select");
    });

    var overhaul_upFormButton = localStorage.getItem("overhaul_upFormButton");
    if(overhaul_upFormButton == 'top'){
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        changeAddPic();
        form.render();
    }else{
        changeAddPic();
    }








    bodyObj.on('change','#fileElem',function () {
        var file = this.files[0];
        filename = file['name'];
        var reader = new FileReader();
        reader.onload = function () {
            // 通过 reader.result 来访问生成的 DataURL
            var url = reader.result;
            setImageURL(url);
            layer.load(2);
            setTimeout(function () {
                uploadImage();
            }, 500);
        };
        reader.readAsDataURL(file);
    });

    var image = new Image();
    var targetWidth = targetHeight = 1000;
    image.onload = function () {
        // 图片原始尺寸
        var originWidth = this.width;
        var originHeight = this.height;
        // 最大尺寸限制，可通过国设置宽高来实现图片压缩程度
        var maxWidth = 1600,
            maxHeight = 1600;
        // 目标尺寸
        targetWidth = originWidth;
        targetHeight = originHeight;
        // 图片尺寸超过400x400的限制
        if (originWidth > maxWidth || originHeight > maxHeight) {
            if (originWidth / originHeight > maxWidth / maxHeight) {
                // 更宽，按照宽度限定尺寸
                targetWidth = maxWidth;
                targetHeight = Math.round(maxWidth * (originHeight / originWidth));
            } else {
                targetHeight = maxHeight;
                targetWidth = Math.round(maxHeight * (originWidth / originHeight));
            }
        }
    };
    function setImageURL(url) {
        var notFileDataTr = $('.notFileDataTr');
        if (notFileDataTr.length > 0) {
            notFileDataTr.remove();
        }
        var addFileTbody = $('.addFileTbody');
        image.src = url;
        var html = '<tr class="fileDataTr">';
        html += '<td class="fileName"><img src="' + image.src + '" style="display: none;"/></td>';
        html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
        addFileTbody.append(html);
    }

    function uploadImage() {
        var canvas = document.getElementById("myCanvas");
        var ctx = canvas.getContext("2d");
        ctx.imageSmoothingEnabled = true;
        img = $('.fileDataTr:last').find('img')[0];
        $("#myCanvas").attr('width', targetWidth);
        $("#myCanvas").attr('height', targetHeight);
        // 设置画布的实际渲染大小，只是简单的对画布进行缩放
        canvas.style.width = canvas.width;
        canvas.style.height = canvas.height;

        // 以实际渲染倍率来放大画布
        //canvas.width = canvas.width * ratio;
        //canvas.height = canvas.height * ratio;
        // 清除画布
        ctx.clearRect(0, 0, targetWidth, targetHeight);
        // 图片压缩
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        /*第一个参数是创建的img对象；第二个参数是左上角坐标，后面两个是画布区域宽高*/
        //压缩后的图片base64 url
        /*canvas.toDataURL(mimeType, qualityArgument),mimeType 默认值是'image/jpeg';
         * qualityArgument表示导出的图片质量，只要导出为jpg和webp格式的时候此参数才有效果，默认值是0.92*/
        var data = canvas.toDataURL('image/jpeg', 0.99);
        data = data.split(',')[1];
        data = window.atob(data);
        var ia = new Uint8Array(data.length);
        for (var i = 0; i < data.length; i++) {
            ia[i] = data.charCodeAt(i);
        }

        // canvas.toDataURL 返回的默认格式就是 image/png
        var blob = new Blob([ia], {type: "image/png"});
        var fd = new FormData();

        fd.append('file', blob);
        fd.append('action', 'upload');
        fd.append('zm', 'canvas');
        fd.append('filename', filename);
        fd.append('i', i);
        $.ajax({
            url: overhaulUrl,
            type: "POST",
            data: fd,
            //beforeSend:beforeSend,
            processData: false,  //tell jQuery not to process the data
            contentType: false,  //tell jQuery not to set contentType
            success: function (res) {
                if (res.status === 1) {
                    $.toptip(res.msg, 'success');
                    setTimeout(function(){//两秒后跳转
                        $('.fileDataTr:last').remove();
                        var addFileTbody = $('.addFileTbody');
                        var html = '<tr class="fileDataTr">';
                        html += '<td class="fileName"><img src="'+res.file_url+'" data-save="'+res.save_name+'" data-name="'+res.file_name+'" data-type="'+res.file_type+'" data-size="'+res.file_size+'" style="display: "/>'+res.file_name+'</td>';
                        html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
                        addFileTbody.append(html);
                    },1000);
                } else {
                    $.toptip(res.msg, 'error');
                }
            },
            complete: complete
        });
    }

    //移除文件
    $(document).on('click', '.del_file', function () {
        var thisTr = $(this).parents('tr');
        var addFileTbody = $('.addFileTbody');
        thisTr.remove();
        if (addFileTbody.find('tr').length === 0) {
            addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
        }
        $.toptip('移除成功', 'success');
        return false;
    });

});
