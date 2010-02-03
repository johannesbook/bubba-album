<?='<?xml version="1.0" encoding="utf8"?>'?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$title?></title>
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/album.css" />
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/screen.css" />
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery-1.3.1.min.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.galleriffic.min.js"></script>
</head>
<body>
<div id="main">
	<? if(!preg_match("/Album login/i",$title)) { ?>
		<div id="header">
		<?=anchor("album/index",'Albums')?> |
		<?if(isset($parent)):?>
		<?=anchor("album/section/$parent",'Parent album')?> |
		<?endif?>
		<?if($this->auth->has_session()):?>
		<?=anchor("album/logout",'Log out')?>
		<?else:?>
		<?=anchor("album/login",'Log in')?>
		<?endif?>
		</div>
	<?}?>
	<?=$content_for_layout?>
<div class="clear" >
</div>
</div>
<div id="footer">
</div>
</body>
</html>
