<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>Document</title>
    <script src="/Public/js/jquery.min.js"></script>
    <script type="text/javascript" src="/Public/js/layer.js"></script>
</head>
<body>
<?php if(isset($message)) { ?>
    <script>
            $(function(){
            layer.msg('<?php echo $message; ?>',{icon:1});
            setTimeout(function(){
            window.location='<?php echo($jumpUrl); ?>';
            },1000);
            })
    </script>
<?php }else{ ?>
    <script>
        $(function(){
           layer.msg('<?php echo $error; ?>',{icon:2});
            setTimeout(function(){
                window.location='<?php echo($jumpUrl); ?>';
            },1000);
        })
    </script>
<?php } ?>
</body>
</html>