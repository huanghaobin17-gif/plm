

layui.define(function(exports){
    $(function () {
        var beforePrint = function() {
            $('.printbtn').hide();
        };

        var afterPrint = function() {
            $('.printbtn').show();
        };
        window.onbeforeprint = beforePrint;
        window.onafterprint = afterPrint;
        $('#print').on('click',function () {
            window.print();
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (mql.matches) {
                        beforePrint();
                    } else {
                        afterPrint();
                    }
                });
            }
        });
    });
    exports('controller/assets/lookup/showAssetLabel', {});
});