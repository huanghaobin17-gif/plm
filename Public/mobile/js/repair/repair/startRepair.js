layui.use(['form','formSelects', 'laydate'], function () {
    var form = layui.form,laydate,bodyObj = $("body"),formSelects = layui.formSelects;

    //初始化下方导航栏菜单
    menuListSpread();

    // //跟进时间选择器
    // $("#followTime").datetimePicker({
    //     title: '限定年月'
    // });
    
    $("#service_date").calendar();
    //跟进时间选择器
    $("#followTime").calendar();
    //修改跟进时间选择器
    $("#editFollowTime").calendar();

    var assist_engineer=-1;
    var assist_engineer_tel='';


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
        html += '<p>' + d + '</p>';
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

        var not_check=$('.not_check');
        if(not_check.length>0){
            $.each(not_check,function (k,v) {
                savePartsList+='<tr class="not_check">'+$(v).html()+'</tr>';
            });

        }
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
            bodyObj.find('#endRepair').attr('class', 'layui-btn');
            bodyObj.find('#endRepair').removeAttr('disabled');
            bodyObj.find('#keepStartRepair').html('暂存不提交');
            partsTableObj.html(emptyHtml);
            closePopup("addPartsDiv");
            return false;
        } else {
            bodyObj.find('#keepStartRepair').html('新申请了配件,请暂存等待出库');
            bodyObj.find('#endRepair').attr('class', 'layui-btn layui-btn-disabled');
            bodyObj.find('#endRepair').attr('disabled','true');
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







    //添加跟进
    bodyObj.on("click",".addFollowButton", function(){
        openPopup(".addFollowDiv");
    });


    //添加配件按钮
    bodyObj.on("click", ".addPartsButton", function () {
        openPopup(".addPartsDiv");
    });

    //关闭添加配件、维修跟进按钮
    bodyObj.on("click", ".weui-popup__modal .weui-icon-cancel", function () {
        var thisClass = $(this).parents(".weui-popup__container"), thisClassName = '';
        if (thisClass.hasClass("addPartsDiv")) {
            thisClassName = 'addPartsDiv';
        }
        closePopup(thisClassName);
    });



    //保存维修跟进
    bodyObj.on("click",".saveFollowButton", function() {
        var followTime = $("#followTime").val(),
            detail = $("#followDetail").val(),
            emptyFollowObj = $(".emptyFollow"),
            followTableObj = $(".followTable").find("tbody");
        var newFollowHtml = '<tr class="clickEditFollow"><td class="saveFollowTime" style="text-align: center;color: #2a7ae2;">'+followTime+'</td><td class="saveFollowDetail" style="word-wrap: break-word;color: #2a7ae2;">'+detail+'</td></tr>';
        emptyFollowObj.remove();
        followTableObj.append(newFollowHtml);
        closePopup();
        var newFollowTr = followTableObj.find("tr");
        setTimeout(function(){
            newFollowTr.each(function(k,v){
                $(v).attr("data-index",k);
            })
        },500)
    });

    //修改或删除维修跟进
    bodyObj.on("click",".editFollowButton", function() {
        var followTime = $("#editFollowTime").val(),
            dataIndex = $("input[name='dataIndex']").val(),
            detail = $("#editFollowDetail").val(),
            thisFollowTablbeObj = $(".followTable").find("tbody tr"),
            newFollowHtml = '<td class="saveFollowTime" style="text-align: center;color: #2a7ae2;">'+followTime+'</td><td class="saveFollowDetail" style="word-wrap: break-word;color: #2a7ae2;">'+detail+'</td>';
        thisFollowTablbeObj.each(function(k,v){
            if ($(v).attr("data-index") == dataIndex){
                $(v).html(newFollowHtml);
            }
        });
        closePopup();
    });

    bodyObj.on("click",".clickEditFollow", function(){
        var index = $(this).attr("data-index"),
            followTime = $("#editFollowTime"),
            detail = $("#editFollowDetail"),
            thisFollowTablbeObj = $(".followTable").find("tbody tr");
        thisFollowTablbeObj.each(function(k,v){
            if ($(v).attr("data-index") == index){
                followTime.val($(v).find(".saveFollowTime").html());
                detail.val($(v).find(".saveFollowDetail").html());
            }
        });
        openPopup(".editFollowDiv");
        $("input[name='dataIndex']").val(index);
    });

    bodyObj.on("click",".deleteFollowButton", function(){
        var dataIndex = $("input[name='dataIndex']").val(),
            thisFollowTablbeObj = $(".followTable").find("tbody tr");
        if (thisFollowTablbeObj.length > 1){
            thisFollowTablbeObj.each(function(k,v){
                if ($(v).attr("data-index") == dataIndex){
                    $(v).remove();
                }
            });
        }else {
            thisFollowTablbeObj.each(function(k,v){
                if ($(v).attr("data-index") == dataIndex){
                    $(v).addClass("emptyFollow");
                    $(v).removeAttr("data-index");
                    $(v).removeClass("clickEditFollow");
                    $(v).html('<td colspan="2" style="text-align:center;">暂无跟进信息</td>');
                }
            });
        }
        closePopup();
    });




    //其他费用验证
    var historyOtherPirce=parseFloat(bodyObj.find('input[name="other_price"]').val());
    bodyObj.find('input[name="other_price"]').change(function () {
        var value=parseFloat($(this).val());
        if (!check_price($(this).val())) {
            $.toptip('请输入合理的费用!', 'error');
            $(this).addClass('border-color-red');
            return false;
        }else{
            $(this).removeClass('border-color-red');
            var other_price=parseFloat(bodyObj.find('input[name="actual_price"]').val())-historyOtherPirce;
            bodyObj.find('input[name="actual_price"]').val(other_price+value);
            historyOtherPirce=value;
        }
    });

    //联系号码验证
    bodyObj.find('input[name="username_tel"]').change(function () {
        if($(this).val()){
            if (!checkTel($(this).val())) {
                $.toptip('请输入合理的联系号码!', 'error');
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
    bodyObj.find('input[name="assist_engineer_tel"]').change(function () {
        if($(this).val()){
            if (!checkTel($(this).val())) {
                $.toptip('请输入合理的联系号码!', 'error');
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
        if($('.border-color-red').length>0){
            $.toptip('请处理标红的异常项!!', 'error');
            return false;
        }
        var params = data.field;
        params.overEngineer = 1;
        params = getPartsInfo(params);
        params = getFollowInfo(params);
        params = getFileInfo(params);
        submit(params, startRepairUrl,mobile_name+'/Repair/getRepairLists.html');
        return false;
    });

    //保存维信息
    form.on('submit(keepStartRepair)', function (data) {
        if($('.border-color-red').length>0){
            $.toptip('请处理标红的异常项!!', 'error');
            return false;
        }
        var params = data.field;
        params = getPartsInfo(params);
        params = getFollowInfo(params);
        params = getFileInfo(params);
        submit(params, startRepairUrl,mobile_name+'/Repair/getRepairLists.html');
        return false;
    });


    //获取配件信息
    function getPartsInfo(params) {
        var partname = '';
        var model = '';
        var num = '';
        var partid = '';
        $('.tr_part').each(function () {
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
        });
        params.partname = partname;
        params.model = model;
        params.num = num;
        params.partid = partid;
        return params;
    }

    //获取维修跟进信息
    function getFollowInfo(params) {
        var target = $('.clickEditFollow');
        var followdate = [], remark = [], nextdate = [];
        $.each(target, function () {
            var f = $.trim($(this).find('.saveFollowTime').html());
            if (f) {
                followdate.push(f);
                //处理详情
                var r = $.trim($(this).find('.saveFollowDetail').html());
                r = r ? r : '';
                remark.push(r);
                //下一次跟进时间
            }
        });
        params.followdate = followdate;
        params.remark = remark;
        params.nextdate = nextdate;

        return params;
    }

    //获取上传文件信息
    function getFileInfo(params) {
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
        params.delfileid=delfileid.join(",");
        return params;
    }



    //选择协助工程师 获取号码
    form.on('select(assist_engineer)', function (data) {
        var usernameval = data.value;
        assist_engineer=usernameval;
        if (parseInt(usernameval) !== -1) {
            $.each(userJson, function (index, data) {
                if (usernameval === data.username) {
                    $('#assist_engineer_tel').val(data.telephone);
                    assist_engineer_tel=data.telephone;
                }
            });
        } else {
            $('#assist_engineer_tel').val('');
            assist_engineer_tel='';

        }
        form.render();
    });

    //进页面优化第三方样式
    $(".companyDiv .layui-card-body:first").css("margin-top",0);


    //语音播放
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

    $(".gray").click(function () {
        var result = [];
        $(this).parent().find(".imageUrl").each(function (k, v) {
            result.push($(v).val());
        });

        var thisPhotos = $.photoBrowser({items: result});
        thisPhotos.open();
    });

    /**
     * 打开遮罩层
     * @param dom 选择器
     */
    function openPopup(dom){
        $(dom).popup();
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
            $('html, body').animate({scrollTop: $(".addFollowButton").offset().top}, 100);
        }
    }



    var filename = '';
    var add_picObj = $('.add_pic'), add_pic = add_picObj[0];
    var fileElemObj = $('#fileElem'), fileElem = fileElemObj[0];

    function changeAddPic() { 
        add_picObj = $('.add_pic');
        add_pic = add_picObj[0];
        if (add_pic===undefined) {
            return false;
        }
        fileElemObj = $('#fileElem');
        fileElem = fileElemObj[0];
        add_pic.addEventListener("click", function (e) {
            if (fileElem) {
                fileElem.click();
            }
            e.preventDefault(); // prevent navigation to "#"
        }, false);
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
        $('input[name="assist_engineer_tel"]').val(assist_engineer_tel);
        $('select[name="assist_engineer"]').val(assist_engineer);
        localStorage.setItem("startRepair_upFormButton",'top');
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
        localStorage.setItem("startRepair_upFormButton",'bottom');
        $('input[name="assist_engineer_tel"]').val(assist_engineer_tel);
        $('select[name="assist_engineer"]').val(assist_engineer);
        changeAddPic();
        form.render("select");
    });

    var startRepair_upFormButton = localStorage.getItem("startRepair_upFormButton");
    if(startRepair_upFormButton == 'top'){
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $('input[name="assist_engineer_tel"]').val(assist_engineer_tel);
        $('select[name="assist_engineer"]').val(assist_engineer);
        $(".upFormButton").hide();
        $(".downFormButton").show();
        changeAddPic();
        form.render();
    }else{
        changeAddPic();
    }



    fileElemObj.change(function () {
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
            url: startRepairUrl,
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

    //被移除的相关文件ID
    var delfileid = [];
    //移除文件
    $(document).on('click', '.del_file', function () {
        var thisTr = $(this).parents('tr');
        var addFileTbody = $('.addFileTbody');
        var params = {};
        params.action = 'deleteFile';
        params.file_id = thisTr.find('img').data('file_id');

        if (parseInt(params.file_id) > 0) {
            delfileid.push(params.file_id);
        }
        thisTr.remove();
        if (addFileTbody.find('tr').length === 0) {
            addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
        }
        $.toptip('移除成功', 'success');
        return false;

    });

    //语音播放
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


});