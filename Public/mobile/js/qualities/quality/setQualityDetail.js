//    定义全局变量
var resultData = {};

layui.use(['form', 'element', 'upload'], function () {
    var form = layui.form, element = layui.element, upload = layui.upload;

    $(".caculater").on('keyup', function () {
        if ($(this).val() == '') {
            $(this).parent().next().find("input").val("");
        } else {
            var num = parseInt($(this).parent().prev().find(".setNumber").html()) - parseInt($(this).val());
            if (num < 0) {
                num = -num;
            }
            $(this).parent().next().find("input").val(num);
        }
    });
    //先更新页面部分需要提前渲染的控件
    form.render();
    //进页面检测是否有暂存数据并对页面输入内容加入验证
    checkStroageData();
    layer.config({
        extend: 'mobileSkin/style.css'
    });
    form.verify({
        lookslike: function (v, dom) {
            if (!$.trim(v)) {
                $(dom).focus();
                return '请填写不符合情况！';
            }
        },
        abnormal_remark: function (v, dom) {
            if (!$.trim(v)) {
                $(dom).focus();
                return '请填写异常处理详情！';
            }
        },
        //------呼吸机验证开始--------
        measured_value: function (v, dom) {
            if (!$.trim(v)) {
                $(dom).focus();
                return '请填写实测值！';
            }
        },
        indication_value: function (v, dom) {
            if (!$.trim(v)) {
                $(dom).focus();
                return '请填写示值！';
            }
        },
        setIE_value: function (v, dom) {
            if (!$.trim(v)) {
                $(dom).focus();
                return '请填写I:E值！';
            }
        },
        //------呼吸机验证结束--------
        numberRange: function (v, dom) {
            var tips = $(dom).attr('placeholder');
            if (v) {
                return checkLegalNumber(v, dom)
            } else {
                $(dom).parents('.layui-colla-content').addClass('layui-show');
                $(dom).focus();
                return tips;
            }
        },
        choose: function (v, dom) {
            var radio_name = $(dom).attr('name'), dtDom = $(dom).parent('dd').prev('dt').children('span');
            var val = $('input:radio[name="' + radio_name + '"]:checked').val();
            if (val == null) {
                var scroll_offset = {}, tips = '', scrollLength = '', radioScroll = '';
                if (dtDom.offset()) {
                    scroll_offset = dtDom.offset();
                    tips = '请选择' + dtDom.html();
                    scrollLength = scroll_offset.top
                } else {
                    scroll_offset = $(dom).parent().offset();
                    tips = '请选择' + $(dom).parent().prev().text();
                    tips = tips.substring(0, tips.length - 1);
                    $(dom).parent().addClass('layui-show');
                    switch (radio_name) {
                        case 'heartRate_result':
                            radioScroll = 400;
                            break;
                        case 'breathRate_result':
                            radioScroll = 200;
                            break;
                    }
                    scrollLength = scroll_offset.top - radioScroll;
                }
                $("body,html").animate({
                    scrollTop: scrollLength
                });
                return tips;
            }
        }
    });
    //单独对外观功能radio操作
    form.on('radio(lookslike)', function (d) {
        changeSurfaceStatus(d.value);
        saveStorageData();
    });
    //除外观功能以外radio操作
    form.on('radio()', function (d) {
        if (d.elem.name != 'lookslike') {
            saveStorageData();
        }
    });
    form.on('submit(confirm)', function (data) {
        //判断是否已上传设备铭牌照片和检测视图照片
        var nameplate_photos = $('.nameplate_pic').find('.photo');
        if (nameplate_photos.length == 0) {
            layer.msg('请上传设备铭牌照！', {icon: 2, time: 1000});
            return false;
        }
        var instrument_photos = $('.instrument_view_pic').find('.photo');
        if (instrument_photos.length == 0) {
            layer.msg('请上传检测视图照！', {icon: 2, time: 1000});
            return false;
        }
        //第一次点击先变为预览状态
        previewStatus();
        return false;
    });
    //最终提交 

    form.on('submit(endStartRepair)', function (data) {
        var params = data.field, reportNumber = $(".stroageNumber").html();
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            async: true,
            beforeSend: beforeSend,
            success: function (data) {
                if (data.status == 1) {
                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                        localStorage.removeItem(reportNumber);
                        //跳转到预览页
                        window.location.href = mobile_name+'/Quality/qualityDetailList.html?action=showQuality&qsid=' + params.qsid;
                    });
                } else {
                    layer.msg(data.msg, {icon: 2, time: 2000});
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2, time: 2000});
            },
            complete: complete
        });
        return false;
    });
    //暂存数据
    form.on('submit(keepStartRepair)', function (data) {
        var params = data.field, reportNumber = $(".stroageNumber").html();
        params['action'] = 'keepquality';
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            async: true,
            beforeSend: beforeSend,
            success: function (data) {
                if (data.status == 1) {
                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                        localStorage.removeItem(reportNumber);
                        //跳转到预览页
                        window.location.href = mobile_name+'/Quality/qualityDetailList.html?action=showQuality&qsid=' + params.qsid;
                    });
                } else {
                    layer.msg(data.msg, {icon: 2, time: 2000});
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2, time: 2000});
            },
            complete: complete
        });
        return false;
    });
    form.on('radio(result)', function (data, e) {
        var targetdiv = $(this).parent().parent().find('div');
        var textarea = $(this).parent().parent().find('textarea');
        if (data.value !== '合格') {
            $(targetdiv).show();
            textarea.attr('lay-verify', 'abnormal_remark');
        } else {
            $(targetdiv).hide();
            textarea.removeAttr('lay-verify');
        }
        form.render('radio');
    });
    $(document).ready(function () {
        $(document).on('blur', '.abnormal_remark', function () {
            var ppid = $(this).data('ppid');
            if ($.trim($(this).val()) !== '') {
                $(this).removeClass('red_border');
            } else {
                var value = $(this).parent('td').prev().find('input:radio[name="result[' + ppid + ']"]:checked').val();
                if (value !== '合格') {
                    $(this).addClass('red_border');
                }
            }
        });

    });

    //返回修改内容
    $(document).on('click', '.editButton', function () {
        //最后一步点击继续修改变回修改状态
        editStatus();
        return false;
    });

    //返回列表
    $(document).on('click', '.backButton', function () {
        window.location.href = mobile_name+'/Quality/qualityDetailList.html?action=planDetailList&plan=' + plans;
        return false;
    });
    var isedit = $("input[name='save_edit']").val();
    if (isedit === 'edit') {
        //列表页点击预览进入
        showDetail();
    }

    //轮播
    var uploadPhotoCarousel = new Swiper('#uploadPhotoCarousel', carouselConfig());
    var checkPhotoCarousel = new Swiper('#checkPhotoCarousel', carouselConfig());

    //点击删除按钮重置轮播
    $(document).on('click', '.photoDelete', function () {
        var index_div = $(this).parent().attr('div-index'),
            numberCarousel = $(this).parents('.swiper-container').attr('id');
        var tar = $(this);
        var id = tar.attr('data-id');
        var params = {};
        params['id'] = id;
        params['action'] = 'delpic';
        layer.confirm('是否删除图片？', {
        btn: ['确定', '取消'] //可以无限个按钮
        }, function(index, layero){
         $.ajax({
            timeout: 5000,
            type: "POST",
            url: 'setQualityDetail',
            data: params,
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
            beforeSend: function () {
                layer.load(2);
            },
            //成功返回之后调用的函数
            success: function (data) {
                if (data.status !== 1) {
                    layer.msg(data.msg, {icon: 2});
                } else {
                    var emptyPhotoDiv = '<div class="swiper-slide"><div class="emptyPhoto">暂无预览图</div></div>';
                    if (numberCarousel == 'uploadPhotoCarousel') {
                        uploadPhotoCarousel.removeSlide(parseInt(index_div));
                        uploadPhotoCarousel.update();
                        var photoDiv = $("#uploadPhotoCarousel").find(".photoDiv");
                        if (photoDiv.length < 1) {
                            uploadPhotoCarousel.appendSlide(emptyPhotoDiv);
                            uploadPhotoCarousel.update();
                        }
                        $.each(photoDiv, function (k, v) {
                            $(v).attr('div-index', k);
                        });
                        uploadPhotoCarousel.startAutoplay();
                    } else {
                        checkPhotoCarousel.removeSlide(parseInt(index_div));
                        checkPhotoCarousel.update();
                        var photoDiv = $("#checkPhotoCarousel").find(".photoDiv");
                        if (photoDiv.length < 1) {
                            checkPhotoCarousel.appendSlide(emptyPhotoDiv);
                            checkPhotoCarousel.update();
                        }
                        $.each(photoDiv, function (k, v) {
                            $(v).attr('div-index', k);
                        });
                        checkPhotoCarousel.startAutoplay();
                    }
                    layer.msg(data.msg, {icon: 1, time: 2000});
                }
            },
            //调用出错执行的函数
            error: function () {
                //请求出错处理
                layer.msg('服务器繁忙', {icon: 5});
            },
            complete: function () {
                layer.closeAll('loading');
            }
        });
          
        }, function(index){
         //按钮【按钮二】的回调
        });
        
    });

    var images = {};
    var pictype = imghtml = '';
    $('#nameplate').on('click', function () {
        pictype = $(this).attr('id');
        wx.chooseImage({
            count: 5, // 默认9
            sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                images.localId = res.localIds;//把图片的路径保存在images[localId]中--图片本地的id信息，用于上传图片到微信浏览器时使用
                ulLoadToWechat(); //把这些图片上传到微信服务器  一张一张的上传
            }
        });
    });

    $('#instrument_view').on('click', function () {
        pictype = $(this).attr('id');
        wx.chooseImage({
            count: 5, // 默认9
            sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                images.localId = res.localIds;//把图片的路径保存在images[localId]中--图片本地的id信息，用于上传图片到微信浏览器时使用
                ulLoadToWechat(); //把这些图片上传到微信服务器  一张一张的上传
            }
        });
    });

    //上传图片到微信
    function ulLoadToWechat() {
        length = images.localId.length; //本次要上传所有图片的数量
        for (var i = 0; i < length; i++) {
            (function(i) {
                setTimeout(function() {
                    wx.uploadImage({
                        localId: images.localId[i], //图片在本地的id
                        success: function (res) {//上传图片到微信成功的回调函数   会返回一个媒体对象  存储了图片在微信的id
                            images.serverId = res.serverId;
                            wxImgDown(res.serverId);
                        },
                        fail: function (res) {
                            layer.msg('服务器繁忙', {icon: 2});
                        }
                    });
                }, i*2000);
            })(i)
        }
    }
    function upimg(id) {

    }

    //下载上传到微信上的图片
    function wxImgDown(mid) {
        var params = {};
        params.mid = mid;
        params.qsid = $("input[name='qsid']").val();
        params.type = pictype;
        $.ajax({   //后台下载
            type: "POST",
            url: admin_name+'/Public/wxImgDown',
            data: params,
            dataType: "json",
            async: true,
            success: function (data) {
                if (data.status == 1) {
                    creatImg(data);
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            },
            erro: function () {
                layer.msg('服务繁忙，请稍后再试！', {icon: 2});
            }
        });
    }

    //创建img的方法
    function creatImg(data) {
        if(pictype == 'nameplate'){
            var uploadPhotoCarouselObj = $("#uploadPhotoCarousel");
            var html = '<div class="swiper-slide photoDiv">\n' +
                '<i class="layui-icon layui-icon-close-fill photoDelete" data-id="' + data.file_id + '"></i>\n' +
                '<img src="' + data.info['file_url'] + '" class="photo">\n' +
                '</div>';
            uploadPhotoCarousel.appendSlide(html);
            var photoDiv = uploadPhotoCarouselObj.find(".photoDiv"),
                emptyPhotoDiv = uploadPhotoCarouselObj.find(".emptyPhoto").parent();
            if (photoDiv.length >= 1) {
                emptyPhotoDiv.remove();
                uploadPhotoCarousel.update();
            }
            uploadPhotoCarousel.update();
            uploadPhotoCarousel.slideTo(photoDiv.length + 1, 1000, false);
            $.each(photoDiv, function (k, v) {
                $(v).attr('div-index', k);
            });
        }
        if(pictype == 'instrument_view'){
            var checkPhotoCarouselObj = $("#checkPhotoCarousel");
            var html = '<div class="swiper-slide photoDiv">\n' +
                        '<i class="layui-icon layui-icon-close-fill photoDelete" data-id="' + data.file_id + '"></i>\n' +
                        '<img src="' + data.info['file_url'] + '" class="photo">\n' +
                        '</div>';
            checkPhotoCarousel.appendSlide(html);
            var photoDiv = checkPhotoCarouselObj.find(".photoDiv"),
                emptyPhotoDiv = checkPhotoCarouselObj.find(".emptyPhoto").parent();
            if (photoDiv.length >= 1) {
                emptyPhotoDiv.remove();
                checkPhotoCarousel.update();
            }
            checkPhotoCarousel.update();
            checkPhotoCarousel.slideTo(photoDiv.length + 1, 1000, false);
            $.each(photoDiv, function (k, v) {
                $(v).attr('div-index', k);
            });
            checkPhotoCarousel.startAutoplay();
        }
    }

});

//设置误差
function wucha(e) {
    //用户填写的测量值
    var input_name = $(e).attr('name');
    input_name = input_name.substr(0, input_name.lastIndexOf("["));
    var user_value = $(e).val();
    user_value = $.trim(user_value);
    if (user_value == '') {
        return false;
    }
    var re = /^(([1-9]\d*)|\d)(\.\d{1,3})?$/;
    if (!re.test(user_value)) {
        layer.msg('请填写合理的测量值！', {icon: 2, time: 2000});

    }
    var real_tolerance = 0;
    //读取系统设置值
    var sys_value = $(e).parent().parent().find('div:first').find('.range').html();

    real_tolerance = sys_value - user_value;
    real_tolerance = Math.abs(real_tolerance);
    //设置误差
    $(e).parent().parent().find('.wc').val(real_tolerance);

    //查找最大的误差值
    var max = $(e).parent().parent().parent().find('.wc');
    var max_value = [];
    $.each(max, function (index, item) {
        if ($(item).val()) {
            max_value.push(parseFloat($(item).val()));
        }
    });
    var max_tolerance = Math.max.apply(null, max_value);
    $('input[name="' + input_name + '_max_output"]').val(max_tolerance);
}

//设置误差
function shizhiwucha(e) {
    //用户填写的测量值
    var input_name = $(e).attr('name');
    input_name = input_name.substr(0, input_name.lastIndexOf("_"));
    var user_value = $(e).val();
    user_value = $.trim(user_value);
    if (user_value == '') {
        return false;
    }
    var re = /^(([1-9]\d*)|\d)(\.\d{1,3})?$/;
    if (!re.test(user_value)) {
        layer.msg('请填写合理的示值！', {icon: 2, time: 2000});

    }
    var real_tolerance = 0;
    //读取系统设置值
    var sys_value = $(e).parent().parent().find('div:first').find('.range').html();

    real_tolerance = sys_value - user_value;
    real_tolerance = Math.abs(real_tolerance);
    //设置示值误差
    $(e).parent().parent().find('.szwc').val(real_tolerance);

    //查找最大的误差值
    var max = $(e).parent().parent().parent().find('.szwc');
    var max_value = [];
    $.each(max, function (index, item) {
        if ($(item).val()) {
            max_value.push(parseFloat($(item).val()));
        }
    });
    var max_tolerance = Math.max.apply(null, max_value);
    $('input[name="' + input_name + '_max_value"]').val(max_tolerance);
}

//完成录入后预览和修改
function showDetail() {
    var editHtmlButton = $(".editHtml").children("button"), confirmHtmlButton = $(".confirmHtml").children("button");
    $("input[type='radio'],input[type='number'],input[type='text'],textarea").attr('disabled', true);
    $("input[type='radio']:checked").each(function (k, v) {
        $(v).attr('disabled', false)
    });
    layui.form.render();

    $('.photoDelete').hide();
    $('#nameplate').hide();
    $('#instrument_view').hide();


    confirmHtmlButton.removeClass("confirm");
    confirmHtmlButton.removeAttr("lay-filter");
    confirmHtmlButton.addClass("backButton");
    confirmHtmlButton.html('返回列表');

    editHtmlButton.html('修改明细');
    editHtmlButton.removeClass('backButton');
    if ($(".no_template").length == 0) {
        editHtmlButton.addClass('editButton');
    } else {
        editHtmlButton.addClass('layui-btn-disabled');
    }
}
//设置平均值
function average(e,name)
{   
    var value = $('input[name="'+name+'[]"]');
    var count = 0;
    var num = 0;
    var Temperature_deviation = 0;
    var max = 0;
    var min = 100;
    $.each(value,function (index,item) {
        if (item.value) {
         count += parseFloat(item.value); 
         num++;
         if (name=="T1") {
            if (parseFloat(item.value)>max) {
                max = parseFloat(item.value);
            }
            if (parseFloat(item.value)<min) {
                min = parseFloat(item.value);
            }
         }
        }
    });
    var ave = (count/num).toFixed(3);
    $('input[name="'+name+'a[]"]').val(ave);
    $('#'+name+'a').html(ave);
    if (name=="T1") {
        if(Math.abs(max-parseFloat(ave))>Math.abs(min-parseFloat(ave)))
        {
            var Volatility=(Math.abs(max-parseFloat(ave))).toFixed(3);
        }else{
            var Volatility=(Math.abs(min-parseFloat(ave))).toFixed(3);
        }
        $('#Volatility').html(Volatility);
        $('input[name="Volatility[]"]').val(Volatility);
        var Temperature_control_deviation = Math.abs(34-parseFloat(ave)).toFixed(3);
        $('#Temperature_control_deviation').html(Temperature_control_deviation);
        $('input[name="Temperature_control_deviation[]"]').val(Temperature_control_deviation);

    }
    if (name=="Tx"||name=="T1") {console.log($('input[name="Txa[]"]').val());console.log($('input[name="T1a[]"]').val());
        Temperature_deviation = parseFloat($('input[name="Txa[]"]').val())-parseFloat($('input[name="T1a[]"]').val());
        $('#Temperature_deviation').html((Math.abs(Temperature_deviation)).toFixed(3));
        $('input[name="Temperature_deviation[]"]').val((Math.abs(Temperature_deviation)).toFixed(3));
    }
    if (name=="T1"||name=="T2"||name=="T3"||name=="T4"||name=="T5") {
        var T1a_ave = parseFloat($('input[name="T1a[]"]').val());
        var T2a_ave = parseFloat($('input[name="T2a[]"]').val());
        var T3a_ave = parseFloat($('input[name="T3a[]"]').val());
        var T4a_ave = parseFloat($('input[name="T4a[]"]').val());
        var T5a_ave = parseFloat($('input[name="T5a[]"]').val());
        var T_ave = [Math.abs(T2a_ave-T1a_ave).toFixed(3),Math.abs(T3a_ave-T1a_ave).toFixed(3),Math.abs(T4a_ave-T1a_ave).toFixed(3),Math.abs(T5a_ave-T1a_ave).toFixed(3)];
        T_ave.sort();
        $('#Temperature_uniformity').html(T_ave[3]);
        $('input[name="Temperature_uniformity[]"]').val(T_ave[3]);
    }

}