;(function ($) {

var Popup = function (_options) {

	var me = {};

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
		return false;
	};

	this.create_cookie = function (name, value, days) {
		var expires = "";
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toGMTString();
		}

		var cookie_name = me.popover_data && me.popover_data.popover_id
			? name + '_' + me.popover_data.popover_id
			: name
		;
		document.cookie = cookie_name + "=" + value + expires + "; path=/";
	};

	var remove_message_box_forever = function () {
		var expiry = me.popover_data.expiry || 365;
		remove_message_box();
		me.create_cookie('popover_never_view', 'hidealways', expiry);
		return false;
	};

	var remove_message_box = function () {
		var _cbk = me.popover_data.multi_open ? 'remove' : 'remove';
		$(me.popover_name).closest('.darkbackground')[_cbk]();
		$(me.popover_name)[_cbk]();
		$(document).trigger('popover-closed');
		return false;
	};

	var set_popup_size = function () {
		var data = me.popover_data;
		var $win = $(window),
			$box = $(me.popover_name),
			$msg = $box.find('.message')
		;
		if (
			(data.usejs && 'yes' === data.usejs)
			||
			(data.size && data.size.usejs)
		) {
			if (!$box.is(":visible")) {
				if ((data.usejs && 'yes' === data.usejs) || (data.position && data.position.usejs)) $box.css({top: $win.height()});
				$box.show();
				$msg = $('#message');
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
				$msg = $box.find('.message');
			}
			$box.css({
				'top': ($(window).height() - $msg.height()) / 2,
				'left': ($(window).width() - $msg.width()) / 2
			});
		}

		if (!$box.is(":visible")) $box.show();
	};

	var set_up_message_box = function () {
		var data = me.popover_data;
		me.popover_name = '#' + data['name'];

		set_popup_size(data);
		$(window).off("resize.popover_" + data['name']).on("resize.popover_" + data['name'], function () {
			set_popup_size(data);
		});

		var $box = $(me.popover_name);

		$box.css('visibility', 'visible');
		$box.closest('.darkbackground').css('visibility', 'visible');

		$box.find('.clearforever').off("click", remove_message_box_forever).on("click", remove_message_box_forever);
		if (me.popover_data && me.popover_data.close_hide) $box.find('.closebox').off("click", remove_message_box_forever).on("click", remove_message_box_forever);
		else $box.find('.closebox').off("click", remove_message_box).on("click", remove_message_box);

		$box.find('.message').hover(function() {
			$box.find('.claimbutton').removeClass('hide');
		}, function() {
			$box.find('.claimbutton').addClass('hide');
		});

		$(document).trigger('popover-displayed', [me.popover_data, me]);
	};


	var load_message_box = function () {
		var data = me.popover_data;

		// No need to reinject if we're already set up
		if ($('#' + data['name']).length) return false;

		if ('' === data['html']) return false;
		$('<style type="text/css">' + data['style'] + '</style>').appendTo('head');
		$(data['html']).appendTo('body');
		
		$('#' + data['name']).css("visibility", "hidden");
		$('#' + data['name']).closest('.darkbackground').css("visibility", "hidden");
	};

	var init = function (data) {
		me = this;

		me.deferred = new $.Deferred();
		me.deferred.done(show);

		if (data) me.popover_data = data;
		$(document).trigger("popover-init", [me.deferred, me.popover_data]);
		setTimeout(function () {
			me.popover_name = '#' + me.popover_data.name;
			$(me.popover_name).hide();

			if (me.popover_data.wait_for_event) return false;
			if ("pending" === me.deferred.state()) me.deferred.resolve();
		}, 500);
	};

	var show = function () {
		window.setTimeout(function () {
			load_message_box();
			set_up_message_box();
			if (me.popover_data.multi_open) $(document).on('popover-closed', function () {
				init(me.popover_data);
			});
		}, me.popover_data.delay);
	};
	return { init: init };
};

var PopupLoader = function (_options) {
	var me = this;

	this.on_success = function (data) {
		me.popover_data = data;
	};

	var async_load_popover = function () {
		var thefrom = window.location;
		var thereferrer = document.referrer;
		// Check if we are forcing a popover - if not then set it to a default value of 0
		if (typeof force_popover === 'undefined') {
			force_popover = 0;
		}

		return $.ajax({
			url: _options.endpoint,
			dataType: 'jsonp',
			jsonpCallback: 'po_a',
			data: {
				popoverajaxaction: _options.action,
				action: _options.action,
				thefrom: thefrom.toString(),
				thereferrer: thereferrer.toString(),
				active_popover: force_popover.toString()
			},
			success: me.on_success
		});
	};

	var spawn_all_popover_instances = function () {
		if (typeof {} === typeof me.popover_data && me.popover_data.popovers && typeof [] === typeof me.popover_data.popovers) {
			$.each(me.popover_data.popovers, function () {
				spawn_popover_instance(this);
			});
		} else spawn_popover_instance(me.popover_data);
	};

	var spawn_popover_instance = function (data) {
		var pop = new Popup(_options);
		pop.init(data);
	};

	this.init = function () {
		var def = new $.Deferred();

		def.done(function () {
			spawn_all_popover_instances();
		});
		if (!_options.popover) {
			async_load_popover().done(function () {
				def.resolve();
			});
		} else {
			me.popover_data = _options.popover;
			def.resolve();
		}
		return def;
	};
};


// Let's initialize
$(function () {
	var pop = new PopupLoader(_popover_data);
	pop.init();
});

})(jQuery);