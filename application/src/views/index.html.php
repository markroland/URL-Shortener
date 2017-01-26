<!DOCTYPE HTML>
<html>
<head>
    <meta charset=utf-8>
    <!--[if !IE]><!-->
    <script type="text/javascript">window.location.href="<?php echo $destination_url; ?>";</script>
    <noscript><meta http-equiv="refresh" content="0;url=<?php echo $destination_url; ?>" /></noscript>
    <!--<![endif]-->
</head>
<body>
    <!--[if !IE]><!-->
    <noscript><a href="<?php echo $destination_url; ?>" id="forwarding_url">Click here to continue to <?php echo $destination_url; ?></a></noscript>
    <!--<![endif]-->

    <!--[if IE]>
    <a href="<?php echo $destination_url; ?>" id="forwarding_url">Click here to continue to <?php echo $destination_url; ?></a>
    <script type="text/javascript">
        document.getElementById('forwarding_url').click()
    </script>
    <![endif]-->
</body>
</html>