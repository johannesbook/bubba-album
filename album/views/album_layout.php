<?='<?xml version="1.0" encoding="utf8"?>'?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$this->config->item("name")?> - photo album</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/jquery.ui.theme.default.css" />
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/admin.css" />
<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/album.css" />
<? /*<link rel="stylesheet" type="text/css" href="<?=$this->config->item("base_url")?>/views/_css/jquery.ui.throbber.css" /> */?>

<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery-ui.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.ui.throbber.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.validate.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.pubsub.js"></script>


<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/main.js"></script>
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.iCheckbox.js" type="text/javascript"></script>

<!-- Configuration -->
<script>
config = <?=json_encode(
	array(
		'prefix' => site_url(),
		'userinfo' => $userinfo,
		'has_access' => $has_access,
		'name'		=> $this->config->item('name'),
	)
)?>;
manager_mode = <?=json_encode((bool)$this->session->userdata('manager_mode'))?>;
section_stack = [];
</script>

<!-- Internationalization -->
<script type="text/javascript" src="<?=$this->config->item("base_url")?>/views/_js/jquery.sprintf.js"></script>
<?global $langcode?>
<?if(file_exists(APPPATH."i18n/$langcode/messages.js")):?>
<script type="text/javascript" src="<?=$this->config->item("base_url")."/i18n/$langcode/messages.js"?>"></script>
<?else :?>
<script type="text/javascript" src="<?=$this->config->item("base_url")."/i18n/en/messages.js"?>"></script>
<?endif?>

<script>
jQuery.validator.setDefaults({ 
	errorPlacement: function(label, element) {
		label.insertAfter( element );
		label.position({
			'my': 'left bottom',
			'at': 'right center',
			'of': element,
			'offset': "-20 -20"
		});
	},
	invalidHandler: function() {
		$(this).closest('ui-dialog').children('.ui-dialog-buttonpane').find('.ui-button').button('enable');
	}	
});	
function postlogin_callback() {
	var self = this;
	var serial = $("#fn-login-dialog-form").serialize();
	$("#fn-login-dialog-button").attr('disabled','disabled');
	$("#fn-login-dialog-button").addClass("ui-state-disabled");
	$("#fn-login-error").children().hide();
	$.post(config.prefix+'/login/json',
	serial,
		function(data){
			if(!data.success) {
				$("#fn-login-error-pwd").show();
				$("#fn-login-password").select();
				$("#fn-login-dialog-button").removeAttr('disabled');
				$("#fn-login-dialog-button").removeClass("ui-state-disabled");
			} else {
				$(self).dialog('close');
				$(self).dialog('destroy');
				config.userinfo = data.userinfo;
				var old_has_access = config.has_access;
				config.has_access = data.has_access;
				update_topnav_status();
				if( config.has_access && old_has_access ) {
					update_manager_mode();
				} else {
					window.location.reload();
				}
				$.event.trigger('auth_changed');
			}
		},"json");
}
function postlogout_callback( event, ui ) {
	var self = this;
	$("#fn-logout-dialog-button").attr('disabled','disabled');
	$("#fn-logout-dialog-button").addClass("ui-state-disabled");
	$.post(
		config.prefix+'/logout/json',
		{},
		function(data){
			$(self).dialog('close');
			$(self).dialog('destroy');
			config.userinfo = data.userinfo;
			if(!config.has_access) {
					window.location.reload();
			}
			update_topnav_status();
			update_manager_mode();
			$.event.trigger('auth_changed');
		},"json"
	);
}

function update_topnav_status() {
	var topnav_status = $('#topnav_status');
	if( !config.userinfo ) {
		return;
	}
	if( config.userinfo.logged_in ) {
		if( config.userinfo.groups['bubba'] ) {
			topnav_status.html($.message("topnav-authorized-bubba", config.userinfo.realname));
		} else if(config.userinfo.groups['album']) {
			topnav_status.html($.message("topnav-authorized-album", config.userinfo.realname));
		} else {
			topnav_status.html($.message("topnav-authorized", config.userinfo.realname));
		}
		$('#fn-topnav-logout div:first').removeClass("ui-icon-login").addClass("ui-icon-logout");
		$('#s-topnav-logout').text($.message('topnav-logout'));
	} else {
		$('#fn-topnav-logout div:first').removeClass("ui-icon-logout").addClass("ui-icon-login");
		$('#s-topnav-logout').text($.message('topnav-login'));
		topnav_status.html($.message("topnav-not-authorized"));
	}
}

function dialog_loginclose_callback() {
	$("#fn-login-error").children().hide();
}

function dialog_login(e) {
	var self = this;

	$.dialog(
		$("#div-login-dialog").show(),
		"",
		[
			{
				'label': $.message("login-dialog-continue"),
				'callback': postlogin_callback,
				options: { 'id': 'fn-login-dialog-button', 'class' : 'ui-element-width-100' }
			}
		],
		{
			dialogClass : "ui-login-dialog",
			draggable: false,
			close : dialog_loginclose_callback
		}
	);
	$('#fn-login-username').focus();

return false;
}

function dialog_logout() {
	
	var buttons = [
        {
            'label': $.message("logout-dialog-button-logout"),
			'callback': postlogout_callback,
			options: { 'id': 'fn-logout-dialog-button', 'class' : 'ui-element-width-100' }
		}
	];
	$.confirm( 
			$.message("logout-dialog-message"),
			$.message("logout-dialog-title"),
			buttons
	);
}

$(function(){
	$.each( $.browser, function( key, value ) {
		if( value && key !== 'version' ) {
			$('body').addClass(key);
		}
	});

	$("#fn-topnav-help").mouseover(function(e) {		$("#s-topnav-help").show();	});	
	$("#fn-topnav-help").mouseout(function(e) {		$("#s-topnav-help").hide();	});	
	$("#fn-topnav-home").mouseover(function(e) {		$("#s-topnav-home").show();	});	
	$("#fn-topnav-home").mouseout(function(e) {		$("#s-topnav-home").hide();	});	
	$("#fn-topnav-logout").mouseover(function(e) {		$("#s-topnav-logout").show();	});	
	$("#fn-topnav-logout").mouseout(function(e) {		$("#s-topnav-logout").hide();	});
	update_topnav_status();
	
	$("#fn-topnav-help").click( function() {
		if(!$(".ui-help-box").is(":visible")) {
			if( config.userinfo.logged_in ) {
				if( manager_mode ) {
					prefix = 'manager';
				} else {
					prefix = 'user';
				}
				if( section_stack.length == 0 ) {
					section = 'main';
				} else {
					section = section_stack[section_stack.length - 1];
				}
				entry = prefix + "::" + section;

			} else {
				entry = 'anon::main';
			}
			content = $('#fn-help-dialog').clone().appendTo('body');
			content
				.find('.ui-help-dialog-content')
				.html($.message("help-info::" + entry));

			$.dialog(
				content.show(),
				$.message('help-box-header'),
				{},
				{
					'modal' : false, 
					dialogClass : "ui-help-box", 
					position : ['right','top']
				}
			);
		};
	});

	$('#fn-topnav-logout').click(function(event) {
		if( config.userinfo && config.userinfo.logged_in ) {
			dialog_logout();
		} else {
			dialog_login();
		}
	});
	$('#fn-topnav-home').click(function(){ window.location.href = "/admin" });
	$("#fn-login-dialog-form input").keypress(function(e) {
		if( e.which == $.ui.keyCode.ENTER ) {
			$("#fn-login-dialog-button").trigger('click');
			return false;
		}
		return true;
	});	
	

/*
	// show dialog if the user does not have access
	if(!config.has_access && config.userinfo.username) {
		$("#fn-login-error-noaccess").show();
		dialog_login();
	}
*/			
});
</script>
<?if($has_access && $head):?>
<?=$head?>
<?endif?>
</head>
<body>
<div id="bg-right"></div>
<div id="wrapper" class="fn-page-<?=$this->uri->segment(2)?>">
    <table id="wrapper">	    
    
		<tr>
		<td id="topnav">
		<div id="topnav-content">
		<div id="topnav-content-inner">
				<span id="topnav_status">
	
			<?if($has_access):?>
			<?if ($userinfo['logged_in']): ?>
			<?if(isset($userinfo['groups']['bubba'])):?>
				<?=t("topnav-authorized-bubba",$userinfo['realname'])?>
			<?elseif(isset($userinfo['groups']['bubba'])):?>
	            <?=t("topnav-authorized-album",$userinfo['realname'])?>
			<?else :?>
	            <?=t("topnav-authorized",$userinfo['realname'])?>
			<?endif?>
			<?else :?>
	            <?=t("topnav-not-authorized")?>
			<?endif?>
			<?else :?>
	            <?=t("topnav-access-denied")?>
			<?endif?>
        </span>
            <button id="fn-topnav-logout" class="ui-button" role="button" aria-disabled="false"><div class="ui-icons ui-icon-logout"></div><div id="s-topnav-logout" class="ui-button-text"><?=t("topnav-logout")?></div></button>
            <button id="fn-topnav-home" class="ui-button" role="button" aria-disabled="false"><div class="ui-icons ui-icon-home"></div><div id="s-topnav-home" class="ui-button-text"><?=t("topnav-home")?></div></button>
            <!--button id="fn-topnav-settings" class="ui-button" role="button" aria-disabled="false"><div class="ui-icons ui-icon-settings"></div><div id="s-topnav-settings" class="ui-button-text"><?=t("topnav-settings")?></div></button-->
            <button id="fn-topnav-help" class="ui-button" role="button" aria-disabled="false"><div class="ui-icons ui-icon-help"></div><div id="s-topnav-help" class="ui-button-text"><?=t("topnav-help")?></div></button>
		</div>
		</div>
		</td> 	<!-- topnav --> 
		<td id="empty-header"></td>
        </tr>   
    
		<tr>
		<td id="content_wrapper">	
            <div id="header">		
                <? 
                if(isB3()) {
                	$logo = "B3_logo.png";
                } else {
                	$logo = "logo.png";
                }
                ?>
				<a href="#" id="a_logo" onclick="location.href='<?=$this->config->item("base_url")?>';"><img id="img_logo" src="<?=$this->config->item("base_url").'/views'?>/_img/<?=$logo?>" alt="<?=$this->config->item('name')?> <?=t('photo album')?>" title="<?=$this->config->item('name')?> <?=t('photo album')?>" /></a>

            </div>	<!-- header -->		
            <div id="content">
			<?/*if($has_access):*/?>
				<?=$content_for_layout?>
			<?/*endif*/?>
            </div>	<!-- content -->
            
    		<div id="update_status" class="ui-corner-all ui-state-highlight ui-helper-hidden"></div>
        </td>	<!-- content_wrapper -->

		</tr>
	</table> <!-- wrapper -->

<div id="layout-templates" class="ui-helper-hidden">

<div id="div-login-dialog">
<form class="ui-form-login-dialog" id="fn-login-dialog-form">
	<h2 class="ui-text-center"><?=t('login-dialog-header')?></h2>
	<table>
		<tr>
			<td>
				<label for="fn-login-username"><?=t("Username")?>:</label><br>
				<input
					id="fn-login-username"
					type="text" 
					name="username"
					class="ui-input-text"
				/>
			</td>
		</tr>
		<tr>
			<td>
				<label for="fn-login-password"><?=t("Password")?>:</label><br>
				<input
					id="fn-login-password"
					type="password" 
					name="password"
					class="ui-input-text"
				/>
			</td>
		</tr>
	</table>
	<div id="fn-login-error">
		<div id="fn-login-error-pwd" class="ui-state-error-text ui-helper-hidden ui-login-dialog-error ui-text-center">
			<?=t('login-error-pwd')?>
		</div>
		<div id="fn-login-error-noaccess" class="ui-state-error-text ui-helper-hidden ui-login-dialog-error ui-text-center">
		<?=t('login-error-noaccess',$userinfo['username'])?>
		</div>		
	</div>

</form>
</div>
	<div id="fn-help-dialog" class="ui-help-dialog ui-helper-hidden">
		<div class="ui-help-dialog-content"></div>
		<div class='help-box-further-info'></div>
		<div class='help-box-external-links'>
			<div class='help-box-external-link'>
				<a target='_blank' href='/manual/'><?=t('help-box-manual-link')?></a> |
				<a target='_blank' href='http://forum.excito.net/index.php'><?=t('help-box-forum-link')?></a> | 
				<a target='_blank' href='http://www.excito.com'><?=t('help-box-excito-link')?></a></div>
			</div>
		</div>				
	</div>

</div>
</body>
</html>
