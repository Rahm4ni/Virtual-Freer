{* smarty *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$title}</title>
<meta http-equiv="Content-Language" content="Persian" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="keywords" content="{$config.config_site_keyword}" />
<link rel="stylesheet" type="text/css" href="statics/css/style.css" media="screen" />
<link rel="stylesheet" type="text/css" href="statics/css/dd.css" />
<script type="text/javascript" src="statics/js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="statics/js/jquery.dd.js"></script>
<script type="text/javascript" src="statics/js/jquery.showMessage.js"></script>
{if $index==1}
<script type="text/javascript" src="statics/js/main.js"></script>
{/if}
</head>
<body>
<div id="wrap">
    <div id="header">
     <h1><a href="index.php">{$config.config_site_title}</a></h1>
     <h2>{$config.config_site_description}</h2>
    </div>
<div id="content">
