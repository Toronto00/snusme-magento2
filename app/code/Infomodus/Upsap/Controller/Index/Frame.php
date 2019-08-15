<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Upsap\Controller\Index;

class Frame extends \Infomodus\Upsap\Controller\Index
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        header('Access-Control-Allow-Origin: http://www.ups.com, http://ups.com');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT');
        header("Access-Control-Allow-Headers: Authorization, X-Requested-With");
        header('Access-Control-Allow-Credentials: true');
        header('P3P: CP="NON DSP LAW CUR ADM DEV TAI PSA PSD HIS OUR DEL IND UNI PUR COM NAV INT DEM CNT STA POL HEA PRE LOC IVD SAM IVA OTC"');
        header('Access-Control-Max-Age: 1');
        $url = str_replace('&', '&amp;', str_replace('&amp;', '&', $_SERVER['QUERY_STRING']));
        $this->getResponse()
            ->setContent('<!DOCTYPE html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body>
<style type="text/css">
    body {
        margin: 0;
        padding: 0;
    }
</style>
<script type="text/javascript">
    window.onload = function () {
        var el = document.querySelector("iframe");
        el.style.width = window.innerWidth + \'px\';
        el.style.height = window.innerHeight + \'px\';
    }
</script>
<iframe src="//www.ups.com/lsw/invoke?'.$url.'" frameborder="0" width="1080px" height="750px"
        name="dialog_upsap_access_points2"></iframe>
</body>
</html>');
        return;
    }
}