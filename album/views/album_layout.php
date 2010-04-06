<?='<?xml version="1.0" encoding="utf8"?>'?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/jquery.ui.theme.default.css" />
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/admin.css" />
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/album.css" />
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery-ui.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.galleriffic.min.js"></script>
</head>
<body>
<div id="wrapper" class="fn-page-<?=$this->uri->segment(2)?>">
	<div id="content_wrapper">
	<div id="header">
	<div id="fn-header-left">
	<span>
		<?=anchor("album/index",'Albums')?>
	</span>
	<?if(isset($parent)):?>
	<span> | <?=anchor("album/section/$parent",'Parent album')?></span>
	<?endif?>
	</div>
	<h1><?=$title?></h1>
	<?if(!isset($hide_header_right) || !$hide_header_right):?>
	<div id="fn-header-right">
	<span> <?=anchor( (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .'/admin/album','Manage albums')?> | </span>
		<?if($this->auth->has_session()):?>
		<?=anchor("album/logout",'Log out')?>
		<?else:?>
		<?=anchor("album/login",'Log in')?>
		<?endif?>
	</div>
	<?endif?>
	</div>

	<div id="content">
	<?=$content_for_layout?>
	</div>
	</div>
</div>
</body>
</html>
