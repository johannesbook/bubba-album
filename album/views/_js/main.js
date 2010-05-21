//define a single reference for an empty function
if (typeof Function.empty == 'undefined')
    Function.empty = function(){};

//stub out firebug console object
//        will allow console statements to be left in place
if (typeof console == 'undefined')
    console = {
        "log": Function.empty,
        "debug": Function.empty,
        "info": Function.empty,
        "warn": Function.empty,
        "error": Function.empty,
        "assert": Function.empty,
        "dir": Function.empty,
        "dirxml": Function.empty,
        "trace": Function.empty,
        "group": Function.empty,
        "groupCollapsed": Function.empty,
        "groupEnd": Function.empty,
        "time": Function.empty,
        "timeEnd": Function.empty,
        "profile": Function.empty,
        "profileEnd": Function.empty,
        "count": Function.empty
    };

// TODO remove usage
function cursor_wait() {
	$.throbber.show();
}		
function cursor_ready() {
	$.throbber.hide();
}		

(
	function($) {
		$.dialog = function(message, header, buttons, override_options ) {

			if(!buttons) {
				buttons = {};
			}

			var options = {
				closeText: '',
				bgiframe: true,
				resizable: false,
				modal: true,
				buttons: buttons,
				position: ['center', 200],
				beforeclose: function(event, ui) { $.throbber.hide(); }
			}
			if( override_options != undefined ) {
				$.extend( options, override_options );
			}

			var div = $('<div/>').hide().appendTo('body');

			div.attr('title', header);
			div.html(message);
			div.dialog( options );
			return div;
		};
/*
 * Usage:
 * $.confirm( 
 * 		message, // html message to be shown
 *		"<?=t("Title")?>", {
 *		 // button label : callback,
 *			<?=t('button_label_continue')?>: function() { // continue button
 *				$(this).dialog('close');
 *				// continue execution here
 *			},
 *			<?=t('button_label_cancel')?>: function() { // cancel button
 *				$(this).dialog('close');
 * 				// eventual cancel logic heoverride_re
 *			}
 *			 // , ... more buttons if wanted
 *		}
 *	);
 *
 */
		$.confirm = function( message, header, buttons, override_options ) {
			if(!buttons) {
				buttons = {
					'Continue': function() {
						$(this).dialog('close');
					},
					'Cancel': function() {
						$(this).dialog('close');
					}
				}
			}
			var options = {dialogClass:'ui-dialog-confirm', close: function(){$(this).remove()}};
			$.extend( options, override_options );
			message = $("<div/>",{html:message});
			message.prepend($('<h2/>',{html:header}));
			return $.dialog( message, '', buttons, options );
		};

		$.alert = function( message, header, button_label, callback, override_options ) {
			if(!button_label) {
				button_label = "Ok";
			}
			var buttons = {};
			buttons[button_label] = function() {
				$(this).dialog('close');
				if( $.isFunction( callback ) ) {
					callback.apply( this, [] );
				}
			};
			var options = {dialogClass:'ui-dialog-alert', close: function(){$(this).remove()} };
			$.extend( options, override_options );
			message = $("<div/>",{html:message});
			message.prepend($('<h2/>',{html:header}));
			return $.dialog( message, '', buttons, options );
		};

	}
)(jQuery);

jQuery.extend({
	'message': function(str){
		if( typeof messages[str] != "undefined" ) {
			var args = Array.prototype.slice.call(arguments);
			args.shift(); // str
			return $.vsprintf(messages[str], args);
		} else {
			if( typeof console != "undefined" ) {
				console.warn("message '%s' was not defined", str);
			}
			return str;
		}
	}
}
);

