var albummanager_obj, filemanager_obj;

var update_manager_access = function() {
	manager_access = config.userinfo && config.userinfo.groups && 'bubba' in config.userinfo.groups;
	if (!manager_access && manager_mode) {
		manager_mode = ! manager_mode;
		update_manager_mode();
	}
	$('.ui-manager-access').button(manager_access ? 'enable': 'disable');
}
var colorbox_title = function() {
	var title = $(this).attr('title');
	var view_url = $(this).attr('href').replace('medium', 'view');
	var download_url = $(this).attr('href').replace('medium', 'download');
	var nodes = $([]);
	nodes = nodes.add($('<a/>', { 'href': view_url, 'target': '_blank', 'text': 'view original', 'id': 'cboxViewOriginal'}));
	nodes = nodes.add($('<span/>', { 'text': title, 'id': 'cboxTitleTitle'}));
	nodes = nodes.add($('<a/>', { 'href': download_url, 'target': '_blank', 'text': 'download original', 'id': 'cboxDownloadOriginal'}));
	
	return nodes;
}



var update_manager_mode_no_reload = function() {
	if (!manager_mode) {
		$('.ui-album-public').hide();
		$("a[rel='fn-image'], #albumtable tbody tr").removeClass('ui-albummanager-state-selected');
		$("a[rel='fn-image']").unbind('click.album').colorbox({
				'photo': true,
				'slideshow': true,
				'slideshowAuto': false,
				'title': colorbox_title,
				'open': false
			}
		);
	} else {
		$('.ui-album-public').show();
		$("a[rel='fn-image']").colorbox('destroy').bind('click.album', function() {
				return false;
			}
		);
	}
	$(".ui-album-managerbar, .ui-album-public").toggleClass('ui-helper-hidden', ! manager_mode);
	$("a[rel='fn-image'], #albumtable tbody tr").toggleClass('ui-album-manager-mode', manager_mode);
}
var update_manager_mode = function() {
	albummanager_obj.albummanager('setManagerMode', manager_mode);
	albummanager_obj.albummanager('reload', function() {
			update_manager_mode_no_reload();
			if( manager_access ) {
				$.post( config.prefix + '/users/set_manager_mode', { 'manager_mode': manager_mode }, function(){}, 'json' );
			}

		}
	);
}

copymove_yesbutton = null;
copymove_isactive = false;

copymove_callback = function(type) {

	var panel = $("#fn-albummanager-information-panel");
	var action = $("#fn-albummanager-action-panel");
	var main_toolbar = albummanager_obj.prev().children(".ui-albummanager-buttonbar");
	var albummanager = $("#albummanager");
	var albums = albummanager_obj.albummanager('getSelectedAlbums');
	var images = albummanager_obj.albummanager('getSelectedImages');

	var speed = 750;

	copymove_isactive = true;
	albummanager_obj.albummanager('disableButtons');

	action.empty();
	panel.empty();

	var button_no = $("<button/>", {
			'text': $.message("albummanager-" + type + "-no")
		}
	).appendTo(action).button({
			text: false,
			icons: {
				primary: 'ui-icon-close'
			}
		}
	).click(function() {
			action.hide('drop', {
					direction: 'right'
				},
				speed);
			albummanager_obj.albummanager('disableButtons', false);
			panel.hide('drop', {
					direction: 'down'
				},
				speed);
			copymove_yesbutton = null;
			copymove_isactive = false;
		}
	);

	copymove_yesbutton = $("<button/>", {
			id: 'fn-albummanager-button-copymove',
			text: $.message("albummanager-" + type + "-yes")
		}
	).appendTo(action).button({
			text: false,
			icons: {
				primary: 'ui-icon-check ui-albummanager-buttonbar-last'
			}
		}
	).click(function() {
			action.hide('drop', {
					direction: 'right'
				},
				speed
			);
			$.throbber.show();
			$.post(config.prefix + "/" + type + "/json", {
					'albums': albums,
					'images': images,
					'path': albummanager_obj.albummanager('option', 'root')
				},
				function(data) {
					$.throbber.hide();

					albummanager_obj.albummanager('disableButtons', false);

					panel.hide('drop', {
							direction: 'down'
						},
						speed);

					if (!data.error) {
						update_manager_mode();
					}
					copymove_yesbutton = null;
					copymove_isactive = false;
					$(window).unbind('resize.albummanager-action');
				},
				'json'
			);
		}
	);

	action.buttonset();

	panel.css({
			top: - 30,
			left: 0
		}
	);
	$(window).bind('resize.albummanager-action', function() {
			action.css({
					top: 0,
					left: main_toolbar.position().left - action.width()
				}
			);
			panel.css({
					width: albummanager_obj.innerWidth() - (panel.outerWidth(true) - panel.innerWidth())
				}
			);
		}).triggerHandler('resize.albummanager-action');

	panel.html($.message("albummanager-" + type + "-notice", albums.length, images.length));
	panel.show('drop', {
			direction: 'down'
		},
		speed
	);
	action.show('drop', {
			direction: 'right'
		},
		speed
	);
};

var dialog_options = {
	'create': {
		'width': 800,
		'height': 600,
		'minWidth': 800,
		'minHeight': 600,
		'resizable': true
	},
	'users': {
		'width': 800,
		'height': 600
	},	
	'modify': {
		'width': 600,
		'height': 400
	},	
	'perm': {
		'width': 600,
		'height': 400
	},		
	'add': {
		'width': 800,
		'height': 600,
		'minWidth': 800,
		'minHeight': 600,
		'resizable': true
	}
};

var dialog_buttons = {
	'create': [{
			'label': $.message("next"),
			options: {
				'class': 'ui-next-button ui-element-width-50'
			}
		},
		{
			'label': $.message("back"),
			options: {
				'class': 'ui-prev-button ui-element-width-50'
			}
		}

	],
	'users': []
};

var dialog_onclose = {
	'perm': function() {
		$('#fn-albummanager-perm-dialog .ui-album-usertable tbody').empty();
		$('#fn-albummanager-perm-public').attr('checked', null).trigger('change');
		$('#fn-albummanager-perm-recursive').attr('checked', 'checked');
	},
	'users': function() {
		$("#fn-albummanager-users-dialog .ui-album-usertable tbody").empty();
		$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
		$('#fn-albummanager-users-dialog-buttons button').button('disable');
	}
};
var dialog_pre_open_callbacks = {
	'modify': function() {
		var metadata = albummanager_obj.albummanager('getFirstSelectedMetadata');
		$("#fn-albummanager-modify-name").val(metadata.name);
		$("#fn-albummanager-modify-caption").val(metadata.caption);
	},
	'users': function() {
						$.throbber.show();
						$.post(config.prefix + "/users/list_users", {},
							function(data) {
								var table = $("#fn-albummanager-users-dialog .ui-album-usertable tbody");
								var checked = $('input:checkbox:checked', table).map(function() {
										return $(this).val();
									}
								).get();
								table.empty();
								$.each(data.users, function() {
										var row = $('<tr/>');
										table.append(row);
										row.append($('<td/>', {
													'data': { 'username': this.username, 'realname': this.realname },
													'html': $.message("album-users-entry", this.realname, this.username),
													'class': 'ui-albummanager-clickable',
													'mousedown': function(event) {
														if( $('#fn-albummanager-users-dialog').hasClass('ui-albummanager-edit-mode') ) {
															return;
														}
														$(this).parent().siblings().children('td').removeClass("ui-albummanager-state-selected");
														$(this).toggleClass("ui-albummanager-state-selected");
														if( $(this).hasClass("ui-albummanager-state-selected") ) {
															$('#fn-albummanager-users-dialog-button-edit, #fn-albummanager-users-dialog-button-delete')
															.button('enable');
														} else {
															$('#fn-albummanager-users-dialog-button-edit, #fn-albummanager-users-dialog-button-delete')
															.button('disable');
														}
													}
												}
											)
										);
									}
								);
								$('#fn-albummanager-users-dialog-button-add').button('enable');
								$.throbber.hide();
							},
							'json');
	},
	'perm': function() {
		var albums = albummanager_obj.albummanager('getSelectedAlbums');
		$.throbber.show();
		$.post( config.prefix + "/perm/show_access", { 'albums': albums }, function( data ) {
				var table = $("#fn-albummanager-perm-dialog .ui-album-usertable tbody");
				$('#fn-albummanager-perm-public').attr('checked', data.public);
				table.empty();
				$.each(data.users, function() {
						var row = $('<tr/>');
						var input_element = $('<input/>', {
								'name': 'users',
								'type': 'checkbox',
								'value': this.username,
								'checked': this.access
							}
						);
						table.append(row);
						row.append($('<td/>', {
									'html': this.realname + " (" + this.username + ")"
								}
							)
						);
						row.append($('<td/>', {
									'html': input_element
								}
							)
						);
					}
				);
				$.throbber.hide();
			}, 
			'json'
		);

	}
};
var dialog_callbacks = {
	'default_close': function() {
		$(this).dialog('close');
	},
	'create': function() {

	},
	'users': function() {

	},
	'modify': function() {
		$.throbber.show();
		var self = this;
		var name = $("#fn-albummanager-modify-name").val();
		var caption = $("#fn-albummanager-modify-caption").val();
		var metadata = albummanager_obj.albummanager('getFirstSelectedMetadata');

		var params = {
			'name': name,
			'caption': caption,
			'id': metadata.id,
			'type': metadata.type
		}
		$.post(config.prefix + "/modify/json", params, function(data) {
				$.throbber.hide();
				$(self).dialog('close');
				if (!data.error) {
					update_manager_mode();
				}
			},
			'json'
		);
		
	},
	'perm': function() {
		$.throbber.show();
		var self = this;
		var public = $("#fn-albummanager-perm-public").is(":checked");
		var recursive = $("#fn-albummanager-perm-recursive").is(":checked");
		var users = $('#fn-albummanager-perm-dialog .ui-album-usertable input[name=users]:checked').map(function() {
				return $(this).val();
			}
		).get();
		var albums = albummanager_obj.albummanager('getSelectedAlbums');

		var params = {
			'public': public,
			'recursive': recursive,
			'users': users,
			'albums': albums
		}
		$.post(config.prefix + "/perm/update_access", params, function(data) {
				$.throbber.hide();
				$(self).dialog('close');
				if (!data.error) {
					albummanager_obj.albummanager('reload');
				}
			},
			'json'
		);
		
	},	
	'add': function() {
		$.throbber.show();
		var self = this;
		var files = filemanager_obj.filemanager('getSelected');
		var album = albummanager_obj.albummanager('option', 'root');

		var params = {
			'files': files,
			'album': album
		}
		$.post(config.prefix + "/add/json", params, function(data) {
				$.throbber.hide();
				$(self).dialog('close');
				if (!data.error) {
					update_manager_mode();
				}
			},
			'json'
		);
	},
	'delete': function() {
		$.throbber.show();
		var self = this;

		var albums = albummanager_obj.albummanager('getSelectedAlbums');
		var images = albummanager_obj.albummanager('getSelectedImages');

		var params = {
			'images': images,
			'albums': albums
		}
		$.post(config.prefix + "/delete/json", params, function(data) {
				$.throbber.hide();
				$(self).dialog('close');
				if (!data.error) {
					update_manager_mode();
				}
			},
			'json'
		);
	},
};

var dialogs = {};
var buttons = [{
		'id': 'fn-albummanager-button-create',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'manager': true,
		'type': 'ui-icons ui-album-icons ui-album-icon-create',
		'alt': 'Create album',
		'callback': function() {
			$("#fn-albummanager-create").formwizard('reset');
			dialogs["create"].find(".fn-placeholder-filemanager").append(filemanager_obj.parent());
			filemanager_obj.filemanager('option', 'root', '/pictures').filemanager('reload', function() {

					dialogs["create"].dialog("open");
					$("#fn-albummanager-create-name").select();
					dialogs['create'].dialog('widget').find('.ui-dialog-buttonpane .ui-prev-button').button('disable');
					$("#fn-albummanager-create-public").removeAttr('checked').trigger('change');

				}
			);

		}
	},
	{
		'id': 'fn-albummanager-button-add',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'manager': true,
		'type': 'ui-icons ui-album-icons ui-album-icon-add',
		'alt': 'Add images',
		'callback': function() {
			dialogs["add"].find(".fn-placeholder-filemanager").append(filemanager_obj.parent());
			filemanager_obj.filemanager('option', 'root', '/pictures').filemanager('reload', function() {
					dialogs["add"].dialog("open");
				}
			);

		}
	},
	{
		'id': 'fn-albummanager-button-move',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'manager': true,
		'type': 'ui-icons ui-icon-move',
		'alt': 'Move',
		'callback': function() {
			copymove_callback.apply(this, ['move']);
		}
	},

	{
		'id': 'fn-albummanager-button-modify',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'type': 'ui-icons ui-icon-pencil',
		'alt': 'Rename',
		'manager': true,
		'callback': function() {
			dialogs["modify"].dialog("open");

		}
	},
	{
		'id': 'fn-albummanager-button-perm',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'type': 'ui-icons ui-icon-unlocked',
		'alt': 'Permissions',
		'manager': true,
		'callback': function() {
			dialogs["perm"].dialog("open");

		}
	},	
	{
		'id': 'fn-albummanager-button-users',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'manager': true,
		'type': 'ui-icons ui-album-icons ui-album-icon-person',
		'alt': 'Manage users',
		'callback': function() {
			dialogs["users"].dialog("open");
		}
	},	
	{
		'id': 'fn-albummanager-button-delete',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'manager': true,
		'type': 'ui-icons ui-icon-trash ui-albummanager-buttonbar-last',
		'alt': 'Delete',
		'callback': function() {
			dialogs["delete"].dialog("open");
		}
	},
	{
		'id': 'fn-albummanager-button-manage',
		'class': 'ui-manager-access',
		'disabled': ! manager_access,
		'type': 'ui-icons ui-album-icons ui-album-icon-manage',
		'alt': 'Manager mode',
		'callback': function() {
			manager_mode = ! manager_mode;
			albummanager_obj.albummanager('setManagerMode', manager_mode);
			update_manager_mode();

		}
	},
	{
		'id': 'fn-albummanager-button-slideshow',
		'disabled': false,
		'type': 'ui-icons ui-album-icons ui-album-icon-slideshow',
		'alt': 'Run slideshow',
		'callback': function() {
			$("a[rel='fn-image']").colorbox('destroy').colorbox({
					'photo': true,
					'slideshow': true,
					'slideshowAuto': true,
					'title': colorbox_title,
					'open': true
				}
			);
			$(window).one('cbox_closed', function() {
					$("a[rel='fn-image']").colorbox('destroy').colorbox({
							'photo': true,
							'slideshow': true,
							'slideshowAuto': false,
							'title': colorbox_title,
							'open': false
						}
					);
				}
			);
		}
	}];

var filemanager_after_open_dir_callback = function() {}

var after_open_dir_callback = function() {
}
var after_draw_images_callback = function() {
	if (manager_mode) {
		$("a[rel='fn-image']").bind('click.album', function() {
				return false;
			}
		);
	} else {
		$('a[rel=fn-image]').colorbox({
				'photo': true,
				'slideshowSpeed': 1000,
				'slideshow': true,
				'slideshowAuto': false,
				'title': colorbox_title
			}
		);
	}
	$("a[rel='fn-image'], #albumtable tbody tr").toggleClass('ui-album-manager-mode', manager_mode);
}

var update_toolbar_buttons = function() {
	var album_length = albummanager_obj.albummanager('albumLength');
	var image_length = albummanager_obj.albummanager('imageLength');
	update_toobar_button_callback(album_length, image_length);
}

var update_toobar_button_callback = function(album_count, image_count) {

	var required_exclusive = ['delete', 'modify', 'move'];
	var required_selected = ['delete', 'modify', 'move', 'perm'];
	var required_single = ['modify', 'perm'];
	var required_albums = ['perm'];
	var required_images = [];
	var states = {
		'perm': true,
		'create': true,
		'add': true,
		'move': true,
		'modify': true,
		'delete': true,
		'manage': manager_access,
		'slideshow': true
	};

	if( album_count && image_count ) {
		$.each(required_exclusive, function() {
				states[this] = false
			}
		);		
	}

	if( !album_count ) {
		$.each(required_albums, function() {
				states[this] = false
			}
		);		
	}

	if( !image_count ) {
		$.each(required_images, function() {
				states[this] = false
			}
		);		
	}
	
	if (album_count + image_count == 0) {
		$.each(required_selected, function() {
				states[this] = false
			}
		);
		$.each(required_single, function() {
				states[this] = false
			}
		);
	} else if (album_count + image_count == 1) {
		$.each(required_selected, function() {
				states[this] &= true
			}
		);
		$.each(required_single, function() {
				states[this] &= true
			}
		);
	} else {
		$.each(required_selected, function() {
				states[this] &= true
			}
		);
		$.each(required_single, function() {
				states[this] = false
			}
		);
	}

	$.each(states, function(key, value) {
			var id = "#fn-albummanager-button-" + key;
			$(id).button(value ? 'enable': 'disable').data('is_disabled', ! value);
		}
	);
}

$(function() {
		$(document).bind('auth_changed', function() {
				update_manager_access();
				return false;
			}
		);

		albummanager_obj = $("#albumtable");

		filemanager_obj = $('#fn-filemanager');

		$.each(['create', 'delete', 'add', 'modify', 'perm', 'users'], function(index, value) {

				if (typeof dialog_options[value] == "undefined") {
					dialog_options[value] = {};
				}

				var options = $.extend({},
					dialog_options[value], {
						"autoOpen": false,
						"open": function(event, ui) {
							var current = $("#fn-albummanager-" + value + "");
							current.trigger("reset");
							if (typeof dialog_pre_open_callbacks[value] != "undefined") {
								dialog_pre_open_callbacks[value].apply(this, arguments);
							}
							$(".fn-primary-field", current).focus();
						}
					}
				);
				if (dialog_buttons[value]) {
					var buttons = dialog_buttons[value];
				} else {
					var buttons = [{
							'label': $.message("albummanager-" + value + "-dialog-button-label"),
							'callback': function() {
								dialog_callbacks[value].apply(dialogs[value], arguments)
							},
							options: {
								id: 'fn-' + value + '-dialog-button',
								'class': 'ui-element-width-100'
							}
						}];
				}
				if( dialog_onclose[value] ) {
					options['close'] = dialog_onclose[value];
				}
				dialogs[value] = $.dialog(
					$("#fn-albummanager-" + value + "-dialog"), "", buttons, options);

				$("#fn-albummanager-" + value + "-dialog").submit(function() {
						$(this).closest('.ui-dialog').find('.ui-dialog-buttonpane').children('button.ui-button').button("disable");
						dialog_callbacks[value].apply(dialogs[value]);

						return false;
					}
				);
			}
		);

		$("#fn-albummanager-create-public").bind('change', function(){
				$("#fn-albummanager-create .ui-album-usertable tbody :checkbox").attr( 'disabled', $(this).is(":checked") ? 'disabled': null);
				$("#fn-albummanager-create .ui-album-usertable tbody").toggleClass( 'ui-state-disabled', $(this).is(":checked") );
			}
		);
		$("#fn-albummanager-perm-public").bind('change', function(){
				$("#fn-albummanager-perm-dialog .ui-album-usertable tbody :checkbox").attr( 'disabled', $(this).is(":checked") ? 'disabled': null);
				$("#fn-albummanager-perm-dialog .ui-album-usertable tbody").toggleClass( 'ui-state-disabled', $(this).is(":checked") );
			}
		);		

		var user_table_setup = function() {
			$.throbber.show();
			$.post(config.prefix + "/users/list_users", {},
				function(data) {
					var table = $("#fn-albummanager-create-form-step-2 .ui-album-usertable tbody");
					var checked = $('input:checkbox:checked', table).map(function() {
							return $(this).val();
						}
					).get();
					table.empty();
					$.each(data.users, function() {
							var row = $('<tr/>');
							var input_element = $('<input/>', {
									'name': 'users[]',
									'type': 'checkbox',
									'value': this.username,
									'checked': $.inArray(this.username, checked) != - 1
								}
							);
							table.append(row);
							row.append($('<td/>', {
										'html': this.realname + " (" + this.username + ")"
									}
								)
							);							
							row.append($('<td/>', {
										'html': input_element
									}
								)
							);
						}
					);
					$.throbber.hide();
				},
				'json');

		}

		var buttonpane = dialogs['create'].dialog('widget').children('.ui-dialog-buttonpane');
		$("#fn-albummanager-create").formwizard({
				historyEnabled: !true,
				focusFirstInput: true,
				validationEnabled: true,
				formPluginEnabled: true,
				back: buttonpane.find('.ui-prev-button'),
				next: buttonpane.find('.ui-next-button'),
				showBackOnFirstStep: true,
				afterNext: function(wizardData) {
					if (wizardData.currentStep == "fn-albummanager-create-form-step-2") {
						user_table_setup();
					}
				}
			},
			{
				'rules': {
					'name': {
						'required': true
					}
				}
			},
			{
				'url': config.prefix + "/create/json",
				'type': 'post',
				'dataType': 'json',
				'beforeSubmit': function(arr, $form, options) {
					$.each( filemanager_obj.filemanager('getSelected'), function() {
							arr.push({ 'name': 'files[]', 'value': this });
						}
					);
					arr.push({ 'name': 'album', 'value': albummanager_obj.albummanager('option', 'root') });
					console.log(arr);
					$.throbber.show();
					return true;
				},
				'success': function( data ) {
					$.throbber.hide();
					dialogs['create'].dialog('close');
					if (!data.error) {
						update_manager_mode();
					}					
				}
			}
		);

		$('#fn-albummanager-create-form-step-button-branch-adduser').click(function(){
				var widget = dialogs['create'].dialog('widget');
				var overlay = widget.prev();
				widget.hide();
				overlay.hide();
				dialogs['users'].one('dialogclose', function(){
						overlay.show();
						widget.show();
						user_table_setup();
					}
				);
				dialogs['users'].dialog('open');
			}
		);

		$('#fn-albummanager-users-dialog-button-add').button({
				'text': false,
				'icons': {
					'primary': 'ui-icons ui-icon-plusthick'
				}
			})
		.click(function(){
				$('#fn-albummanager-users-dialog-buttons button').button("disable");
				$('#fn-albummanager-users-dialog .ui-album-usertable .ui-albummanager-state-selected').removeClass('ui-albummanager-state-selected');
				var table = $('#fn-albummanager-users-dialog .ui-album-usertable tbody');
				var form = $('#fn-albummanager-users-add-template').clone();
				var validator = form.validate({
					'rules': {
						'username': {
							'required': true,
							'remote': {
								url: config.prefix + "/users/username_free",
								type: "post"
							}
						},
						'password1': {
							'required': true,
							'minlength': 2
						},
						'password2': {
							'equalTo': $('input[name=password1]', form)
						}
					}
					}
				);
				
				var wrapper = $("<tr><td></td></tr>");
				wrapper.find('td').addClass('ui-albummanager-users-edit').append(form).end().prependTo(table);
				$('#fn-albummanager-users-dialog').addClass('ui-albummanager-edit-mode');

				form.find("#fn-albummanager-users-add-cancel").button().click(function(){
						$('#fn-albummanager-users-dialog-button-add').button("enable");
						$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
						wrapper.remove();
					}
				);
				form.find("#fn-albummanager-users-add-ok").button().click(function(){
						if( ! validator.form() ) {
							return false;
						}
						$.post(
							config.prefix + "/users/add",
							{
								'username': form.find('input[name=username]').val(),
								'realname': form.find('input[name=realname]').val(),
								'password1': form.find('input[name=password1]').val(),
								'password2': form.find('input[name=password2]').val()
							},
							function(data){
								if(data.error){
								}
								$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
								dialog_pre_open_callbacks['users'].apply(dialogs['users']);
							},
							'json'
						);
					}
				);		

				form[0].username.focus();
			}
		);
		$('#fn-albummanager-users-dialog-button-edit').button({
				'text': false,
				'icons': {
					'primary': 'ui-icons ui-icon-pencil'
				}
			})
		.click(function(){
				var selected = $('#fn-albummanager-users-dialog .ui-album-usertable .ui-albummanager-state-selected');
				if( !selected.size() ) {
					return;
				}
				$('#fn-albummanager-users-dialog-buttons button').button("disable");
				var username = selected.data('username');
				var realname = selected.data('realname');
				var form = $('#fn-albummanager-users-edit-template').clone();
				var validator = form.validate({
					'rules': {
						'password1': {
							'required': false,
							'minlength': 2				
						},
						'password2': {
							'equalTo': $('input[name=password1]', form)
						}
					}
					}
				);
				selected.html(form);
				form[0].reset();

				selected.removeClass('ui-albummanager-state-selected').addClass('ui-albummanager-users-edit');
				$('#fn-albummanager-users-dialog').addClass('ui-albummanager-edit-mode');

				var first = form.find("input[username=username]");
				form.find("#fn-albummanager-users-edit-username").val(username);
				$(form[0].realname).val(realname);
				form[0].realname.focus();
				form.find("#fn-albummanager-users-edit-cancel").button().click(function(){
						$('#fn-albummanager-users-dialog-button-add').button("enable");
						selected.removeClass('ui-albummanager-users-edit').html($.message("album-users-entry", realname, username));
						$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
					}
				);
				form.find("#fn-albummanager-users-edit-ok").button().click(function(){
						if( ! validator.form() ) {
							return false;
						}
						$.post(
							config.prefix + "/users/edit",
							{
								'username': username,
								'realname': form.find('input[name=realname]').val(),
								'password1': form.find('input[name=password1]').val(),
								'password2': form.find('input[name=password2]').val()
							},
							function(data){
								if(data.error){
								}
								$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
								dialog_pre_open_callbacks['users'].apply(dialogs['users']);
							},
							'json'
						);
					}
				);				
			}
		);
		
		$('#fn-albummanager-users-dialog-button-delete').button({
				'text': false,
				'icons': {
					'primary': 'ui-icons ui-icon-trash ui-albummanager-buttonbar-last'
				}
			}
		).click(function(){
				$('#fn-albummanager-users-dialog').addClass('ui-albummanager-edit-mode');
				var speed = 400;
				var selected = $('#fn-albummanager-users-dialog .ui-album-usertable .ui-albummanager-state-selected');
				selected.removeClass('ui-albummanager-state-selected');
				if( !selected.size() ) {
					return;
				}
				$('#fn-albummanager-users-dialog-buttons button').button("disable");
				var username = selected.data('username');
				var realname = selected.data('realname');
				var subdialog = $('<div/>', { 'class': 'ui-album-dialog-subdialog ui-corner-bottom'});
				var info = $('<div/>', { 'class':  'ui-subdialog-message'}).appendTo( subdialog );
				info.html($.message('album-users-delete-message', username, realname));
				var action = $('<div/>', { 'class': 'ui-subdialog-action'}).appendTo( subdialog );
				subdialog.hide().appendTo(dialogs['users']);
				var button_no = $("<button/>", {
						'text': $.message("albummanager-del-no")
					}
				).appendTo(action).button({
						text: false,
						icons: {
							primary: 'ui-icon-close'
						}
					}
				).click(function() {
						subdialog.hide( 'slide', { direction: 'up' }, speed, function() { subdialog.remove() } );
						$('#fn-albummanager-users-dialog-button-add').button("enable");
						$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
					}
				);

				copymove_yesbutton = $("<button/>", {
						id: 'fn-albummanager-button-copymove',
						text: $.message("albummanager-del-yes")
					}
				).appendTo(action).button({
						text: false,
						icons: {
							primary: 'ui-icon-check ui-albummanager-buttonbar-last'
						}
					}
				).click(function() {
						$.post(
							config.prefix + "/users/del",
							{
								'username': username
							},
							function(data){
								if(data.error){
								}
								subdialog.hide( 'slide', { direction: 'up' }, speed, function() { subdialog.remove() } );
								$('#fn-albummanager-users-dialog-button-add').button("enable");
								$('#fn-albummanager-users-dialog').removeClass('ui-albummanager-edit-mode');
								dialog_pre_open_callbacks['users'].apply(dialogs['users']);
							},
							'json'
						);						
					}
				);

				action.buttonset();
				subdialog.show( 'slide', { direction: 'up' }, speed );


			}
		);
		
		$('#fn-albummanager-users-dialog-buttons').buttonset();

		albummanager_obj.albummanager({
				root: null,
				managerMode: manager_mode,
				dirPostOpenCallback: after_open_dir_callback,
				afterDrawImagesCallback: after_draw_images_callback,
				dirDoubleClickCallback: update_toolbar_buttons,
				mouseDownCallback: update_toolbar_buttons,
				ajaxSource: config.prefix + "/album/json"
			}
		);
		albummanager_obj.albummanager('setButtons', buttons);

		filemanager_obj.filemanager({
				root: '/pictures',
				animate: false,
				dirPostOpenCallback: filemanager_after_open_dir_callback,
				ajaxSource: config.prefix + "/filemanager/json"

			}
		);
		update_manager_mode_no_reload();
	}
);

