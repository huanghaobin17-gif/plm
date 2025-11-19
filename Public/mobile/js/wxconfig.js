/**
 * Created by Administrator on 2017/8/25.
 */
wx.ready(function () {
    // 9.1.2 扫描二维码并返回结果
    document.querySelector('#scanQRCode1').onclick = function () {
        wx.scanQRCode({
            needResult: 1,
            scanType: ["qrCode","barCode"],
            desc: 'scanQRCode desc',
            success: function (res) {
                var assnum = res.resultStr;
                if(assnum.indexOf("ODE_") > 0){
                    assnum = res.resultStr.substr(9);
                }
                console.log(assnum);
//                    $.router.load('/index.php/Home/Repair/Repair/addRepair/assid/1');
                var url = "/index.php/Home/Repair/Repair/checkScanQRCode/assnum/"+assnum;
                $.ajax({
                    type: "GET",
                    url: url,
                    //返回数据的格式
                    //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
                    beforeSend: function () {
                        $.showIndicator();
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        $.hideIndicator();
                        console.log(data.status);
                        console.log(data.url);
                        if (data.status == 1) {
                            //alert('已查找到编号为'+data.msg+'的设备');
                            console.log(data.url);
                            $.router.load(data.url);
                        } else {
                            //$.toast(data.msg);
                            //alert(data.msg);
                            $.router.load(data.url);
                        }
                    },
                    //调用出错执行的函数
                    error: function () {
                        $.hideIndicator();
                        //请求出错处理
                        $.toast("服务器繁忙");
                    }
                });
            }
        });
    };
});
