<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <!-- Title and other stuffs -->
  <title>旅之沙提示页面</title>
  <meta name="keywords" content="旅之沙提示页面" />
  <meta name="description" content="旅之沙提示页面" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="">
  <!-- Stylesheets -->
  <link href="{$Think.const.SITE_STYLE_URL}/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" href="{$Think.const.SITE_STYLE_URL}/font-awesome.css">
  <link href="{$Think.const.SITE_STYLE_URL}/style.css" rel="stylesheet">
  
  <!-- HTML5 Support for IE -->
  <!--[if lt IE 9]>
  <script src="{$Think.const.SITE_JAVASCRIPT_URL}/html5shim.js"></script>
  <![endif]-->

  <!-- Favicon -->
  <link rel="shortcut icon" href="{$Think.const.SITE_IMAGE_URL}/favicon/favicon.png">
</head>

<body>

<!-- Form area -->
<div class="error-page">
  <div class="container" style="margin-top: 30%">
    <div class="row">
      <div class="col-md-12">
        <!-- Widget starts -->
            <div class="widget">
              <!-- Widget head -->
              <div class="widget-head">
                <h4 class="text-primary" style="margin-right: 10px;font-size: 14px;cursor: pointer"><span class="glyphicon glyphicon-exclamation-sign"></span> 旅之沙温馨小tips</h4>
              </div>

              <div class="widget-content">
                <div class="padd error">
                  <h2 style="margin-top: 20px;margin-bottom: 30px;"><?php echo($error); ?></h2>
                  页面自动 <a id="href" href="<?php echo($jumpUrl); ?>"> 跳转 </a> 等待时间： <b id="wait" style="color: #2d78f4"><?php echo($waitSecond); ?></b>   <a style="float: right;margin-right: 20px" href="<?php echo($jumpUrl); ?>">手动跳转</a>
                </div>
              </div>
            </div>  
      </div>
    </div>
  </div> 
</div>
<!-- JS -->
<script src="{$Think.const.SITE_JAVASCRIPT_URL}/jquery.js"></script>
<script src="{$Think.const.SITE_JAVASCRIPT_URL}/bootstrap.js"></script>
</body>
</html>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>