;(function () {

var Popup = function (_options) {

	var me = this,
		popover_data = {}
		;

	this.deferred = new jQuery.Deferred,

	this.get_cookie = function (name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === ' ')
				c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) === 0)
				return c.substring(nameEQ.length, c.length);
		}
		return null;
	};

	this.create_cookie = function (name, value, days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			var expires = "; expires=" + date.toGMTString();
		}
		else
			var expires = "";

		var cookie_name = me.popover_data && me.popover_data.popover_id
			? name + '_' + me.popover_data.popover_id
			: name
		;
		document.cookie = cookie_name + "=" + value + expires + "; path=/";
	};

	this.remove_message_box_forever = function () {
		var expiry = me.popover_data.expiry || 365;
		me.remove_message_box();
		me.create_cookie('popover_never_view', 'hidealways', expiry);
		return false;
	};

	this.remove_message_box = function () {
		var _cbk = me.popover_data.multi_open ? 'hide' : 'remove';
		jQuery('#darkbackground')[_cbk]();
		jQuery(me.popover_name)[_cbk]();
		jQuery(document).trigger('popover-closed');
		return false;
	};

	this.set_popup_size = function () {
		var data = me.popover_data;
		var $msg = jQuery('#message'),
			$win = jQuery(window),
			$box = jQuery(me.popover_name)
		;
		if (
			(data.usejs && 'yes' === data.usejs)
			||
			(data.size && data.size.usejs)
		) {
			if (!$box.is(":visible")) {
				if ((data.usejs && 'yes' === data.usejs) || (data.position && data.position.usejs)) $box.css({top: $win.height()});
				$box.show();
				$msg = jQuery('#message');
			}
			$box
				.width($msg.width())
				.height($msg.height())
			;
		}

		if (
			(data.usejs && 'yes' === data.usejs)
			||
			(data.position && data.position.usejs)
		) {
			if (!$box.is(":visible")) {
				$box.css({top: $win.height()});
				$box.show();
				$msg = jQuery('#message');
			}
			$box.css({
				'top': (jQuery(window).height() - $msg.height()) / 2,
				'left': (jQuery(window).width() - $msg.width()) / 2
			});
		}
		if (!$box.is(":visible")) $box.show();
	};

	this.set_up_message_box = function () {
		var data = me.popover_data;
		me.popover_name = '#' + data['name'];

		me.set_popup_size(data);
		jQuery(window).off("resize.popover").on("resize.popover", function () {
			me.set_popup_size(data);
		});

		jQuery(me.popover_name).css('visibility', 'visible');
		jQuery('#darkbackground').css('visibility', 'visible');

		jQuery('#clearforever').off("click", me.remove_message_box_forever).on("click", me.remove_message_box_forever);
		if (me.popover_data && me.popover_data.close_hide) jQuery('#closebox').off("click", me.remove_message_box_forever).on("click", me.remove_message_box_forever);
		else jQuery('#closebox').off("click", me.remove_message_box).on("click", me.remove_message_box);

		jQuery('#message').hover(function() {
			jQuery('.claimbutton').removeClass('hide');
		}, function() {
			jQuery('.claimbutton').addClass('hide');
		});

		jQuery(document).trigger('popover-displayed', [me.popover_data, me]);
	};

	this.load_message_box = function () {
		var data = me.popover_data;

		if (data['html'] === '') return false;

		jQuery('<style type="text/css">' + data['style'] + '</style>').appendTo('head');
		jQuery(data['html']).appendTo('body');

		jQuery('#' + data['name']).css("visibility", "hidden");
		jQuery('#darkbackground').css("visibility", "hidden");
	};

	this.on_success = function (data) {
		me.popover_data = data;
		if (data['name'] !== 'nopoover') me.load_message_box();
	};

	this.async_load_popover = function () {

		var thefrom = window.location;
		var thereferrer = document.referrer;
		// Check if we are forcing a popover - if not then set it to a default value of 0
		if (typeof force_popover === 'undefined') {
			force_popover = 0;
		}

		return jQuery.ajax({
			url: _options.ajaxurl,
			dataType: 'jsonp',
			jsonpCallback: 'po_data',
			data: {
				'action': 'inc_popup',
				'do': _options['do'],
				thefrom: thefrom.toString(),
				thereferrer: thereferrer.toString(),
				po_id: force_popover.toString()
			},
			success: function(data) {
				me.on_success(data);
			}
		});
		return false;
	};

	this.init = function () {
		var def = new jQuery.Deferred;
		me.deferred.done(me.show);

		def.done(function () {
			jQuery(document).trigger("popover-init", [me.deferred, me.popover_data]);
			setTimeout(function () {
				me.popover_name = '#' + me.popover_data.name;
				jQuery(me.popover_name).hide();

				if (me.popover_data.wait_for_event) return false;
				if ("pending" === me.deferred.state()) me.deferred.resolve();
			}, 500);
		});
		if (!_options.popover) {
			me.async_load_popover().done(function () {
				def.resolve();
			});
		} else {
			me.popover_data = _options.popover;
			def.resolve();
		}
		return def;
	};

	this.reinit = function () {
		me.deferred = new jQuery.Deferred;
		me.deferred.done(me.show);
		jQuery(document).trigger("popover-init", [me.deferred, me.popover_data]);
		setTimeout(function () {
			me.popover_name = '#' + me.popover_data.name;
			jQuery(me.popover_name).hide();

			if (me.popover_data.wait_for_event) return false;
			if ("pending" === me.deferred.state()) me.deferred.resolve();
		}, 500);
	};

	this.show = function () {
		window.setTimeout(function () {
			me.set_up_message_box();
			if (me.popover_data.multi_open) jQuery(document).on('popover-closed', me.reinit);
		}, me.popover_data.delay);
	};

	return {
		init: this.init
	};
};


// Let's initialize
jQuery(function () {
	var pop = new Popup(_popover_data);
	pop.init();
});

})();