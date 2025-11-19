/**
 * Created by 汤圆酱 on 2019/10/30.
 */
layui.use('element', function () {
    var element = layui.element;

    //初始化下方导航栏菜单
    menuListSpread();

    /*加载更多 收起 按钮处理*/
    /*上方table加载更多*/
    $("#moreDetailButton").click(function () {
        $(".moreDetail").show();
        $(this).parent().hide();
        $("#hideDetailButton").parent().show();
    });
    $("#hideDetailButton").click(function () {
        $(".moreDetail").hide();
        $(this).parent().hide();
        $("#moreDetailButton").parent().show();
    });
    /**/
    /**/

    var images = {};
    var  imghtml = '';
    $('#nameplate').on('click', function () {
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
                        isShowProgressTips: 1,
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
        params.assid = $("#assid").val();
        params.type = $("#type").val();
        params.action = 'uploadReport';
        $.ajax({   //后台下载
            type: "POST",
            url: "verify",
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
    var uploadPhotoCarousel = new Swiper('#uploadPhotoCarousel', carouselConfig());
    //创建img的方法
    function creatImg(data) {
            $('#img').css('display','block'); 
            var uploadPhotoCarouselObj = $("#uploadPhotoCarousel");
            var html = '<div class="swiper-slide photoDiv">\n<img src="' + data.path + '" class="photo">\n' +
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
});