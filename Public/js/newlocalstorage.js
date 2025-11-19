//检测浏览器是否支持localStorage
if(typeof localStorage == 'undefined'){
    //创建localStorage
    var localStorageClass = function(){
        this.options = {
            expires : 60*24*3600,
            domain : "swe_ling@163.com"
        }
    };
    localStorageClass.prototype = {
        //初实化。添加过期时间
        init:function(){
            var date = new Date();
            date.setTime(date.getTime() + 60*24*3600);
            this.setItem('expires',date.toGMTString());
        },
        //内部函数 参数说明(key) 检查key是否存在
        findItem:function(key){
            var bool = document.cookie.indexOf(key);
            if( bool < 0 ){
                return true;
            }else{
                return false;
            }
        },
        //得到元素值 获取元素值 若不存在则返回 null
        getItem:function(key){
            var i = this.findItem(key);
            if(!i){
                var array = document.cookie.split(';')
                for(var j=0;j<array.length;j++){
                    var arraySplit = array[j];
                    if(arraySplit.indexOf(key) > -1){
                        var getValue = array[j].split('=');
                        //将 getValue[0] trim删除两端空格
                        getValue[0] = getValue[0].replace(/^\s\s*/, '').replace(/\s\s*$/, '')
                        if(getValue[0]==key){
                            return getValue[1];
                        }else{
                            return 'null';
                        }
                    }
                }
            }
        },
        //重新设置元素
        setItem:function(key,value){
            var i = this.findItem(key)
            document.cookie=key+'='+value;
        },
        //清除cookie 参数一个或多一
        clear:function(){
            for(var cl =0 ;cl<arguments.length;cl++){
                var date = new Date();
                date.setTime(date.getTime() - 100);
                document.cookie =arguments[cl] +"=a; expires=" + date.toGMTString();
            }
        }
    };
    var localStorage = new localStorageClass();
    localStorage.init();
}