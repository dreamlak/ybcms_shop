(function($) {
	// 倒计时
	$.fn.countdown = function(options) {

		var defaults = {
			// 间隔时间，单位：毫秒
			time: 0,
			// 更新时间，默认为1000毫秒
			updateTime: 1000,
			// 显示模板
			htmlTemplate: "%{d} 天 %{h} 小时 %{m} 分 %{s} 秒",
			minus: false,
			onChange: null,
			onComplete: null,
			leadingZero: false
		};
		var opts = {};
		var rDate = /(%\{d\}|%\{h\}|%\{m\}|%\{s\})/g;
		var rDays = /%\{d\}/;
		var rHours = /%\{h\}/;
		var rMins = /%\{m\}/;
		var rSecs = /%\{s\}/;
		var complete = false;
		var template;
		var floor = Math.floor;
		var onChange = null;
		var onComplete = null;

		var now = new Date();

		$.extend(opts, defaults, options);

		template = opts.htmlTemplate;
		return this.each(function() {

			var interval = opts.time - (new Date().getTime() - now.getTime());

			var $this = $(this);
			var timer;
			var msPerDay = 864E5; // 24 * 60 * 60 * 1000
			var timeLeft = interval;
			var e_daysLeft = timeLeft / msPerDay;
			var daysLeft = floor(e_daysLeft);
			var e_hrsLeft = (e_daysLeft - daysLeft) * 24; // Gets remainder
			// and * 24
			var hrsLeft = floor(e_hrsLeft);
			var minsLeft = floor((e_hrsLeft - hrsLeft) * 60);
			var e_minsleft = (e_hrsLeft - hrsLeft) * 60; // Gets remainder
			// and * 60
			var secLeft = floor((e_minsleft - minsLeft) * 60);
			var time = "";

			if (opts.onChange) {
				$this.bind("change", opts.onChange);
			}

			if (opts.onComplete) {
				$this.bind("complete", opts.onComplete);
			}

			if (opts.leadingZero) {

				if (daysLeft < 10) {
					daysLeft = "0" + daysLeft;
				}

				if (hrsLeft < 10) {
					hrsLeft = "0" + hrsLeft;
				}

				if (minsLeft < 10) {
					minsLeft = "0" + minsLeft;
				}

				if (secLeft < 10) {
					secLeft = "0" + secLeft;
				}
			}

			// Set initial time
			if (interval >= 0 || opts.minus) {
				time = template.replace(rDays, daysLeft).replace(rHours, hrsLeft).replace(rMins, minsLeft).replace(rSecs, secLeft);
			} else {
				time = template.replace(rDate, "00");
				complete = true;
			}

			timer = window.setInterval(function() {

				var interval = opts.time - (new Date().getTime() - now.getTime());

				var TodaysDate = new Date();
				var CountdownDate = new Date(opts.date);
				var msPerDay = 864E5; // 24 * 60 * 60 * 1000
				var timeLeft = interval;
				var e_daysLeft = timeLeft / msPerDay;
				var daysLeft = floor(e_daysLeft);
				var e_hrsLeft = (e_daysLeft - daysLeft) * 24; // Gets
				// remainder and
				// * 24
				var hrsLeft = floor(e_hrsLeft);
				var minsLeft = floor((e_hrsLeft - hrsLeft) * 60);
				var e_minsleft = (e_hrsLeft - hrsLeft) * 60; // Gets
				// remainder and
				// * 60
				var secLeft = floor((e_minsleft - minsLeft) * 60);
				var time = "";

				if (opts.leadingZero) {

					if (daysLeft < 10) {
						daysLeft = "0" + daysLeft;
					}

					if (hrsLeft < 10) {
						hrsLeft = "0" + hrsLeft;
					}

					if (minsLeft < 10) {
						minsLeft = "0" + minsLeft;
					}

					if (secLeft < 10) {
						secLeft = "0" + secLeft;
					}
				}

				if (interval >= 0 || opts.minus) {
					time = template.replace(rDays, daysLeft).replace(rHours, hrsLeft).replace(rMins, minsLeft).replace(rSecs, secLeft);
				} else {
					time = template.replace(rDate, "00");
					complete = true;
				}

				$this.html(time);

				$this.trigger('change', [timer]);

				if (complete) {

					$this.trigger('complete');
					clearInterval(timer);
				}

			}, opts.updateTime);

			$this.html(time);

			if (complete) {
				$this.trigger('complete');
				clearInterval(timer);
			}
		});
	};
})(jQuery);