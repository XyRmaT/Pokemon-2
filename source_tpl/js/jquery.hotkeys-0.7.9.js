$(function() {
	$.extend({
		hotkey: function (key, callback, n, hold) {
			var codes = {
				backspace: 8,
				tab: 9,
				enter: 13,
				shift: 16,
				ctrl: 17,
				alt: 18,
				esc: 27,
				space: 32,
				pageup: 33,
				pagedown: 34,
				end: 35,
				home: 36,
				left: 37,
				up: 38,
				right: 39,
				down: 40,
				insert: 45,
				"delete": 46,
				"0": 48,
				"1": 49,
				"2": 50,
				"3": 51,
				"4": 52,
				"5": 53,
				"6": 54,
				"7": 55,
				"8": 56,
				"9": 57,
				a: 65,
				b: 66,
				c: 67,
				d: 68,
				e: 69,
				f: 70,
				g: 71,
				h: 72,
				i: 73,
				j: 74,
				k: 75,
				l: 76,
				m: 77,
				n: 78,
				o: 79,
				p: 80,
				q: 81,
				r: 82,
				s: 83,
				t: 84,
				u: 85,
				v: 86,
				w: 87,
				x: 88,
				y: 89,
				z: 90,
				command: 91
			}, i;
			if (typeof n == "undefined")
				n = "";
			if (typeof key == "object") {
				for (i in key)
					$.hotkey(key[i], callback, n);
				return;
			}
			if(typeof hold === 'undefined') {
				hold = true;
			}
			if (callback === false || callback == "unbind")
				$(document).unbind("keydown." + key + n).unbind("keyup." + key + n);
			else if (typeof codes[key] == "undefined")
				console.log("unrecognized key: " + key);
			else
				$(document).bind("keydown." + key + n, key, function (e) {
					if (e.which == codes[key]) {
						var target = $(e.target);
						if (target.is(":input") || target.is("select"))
							return;
						else {
							e.preventDefault();
							if(hold === true) callback.call(this, e);
							return false;
						}
					}
				});
				if(hold === false) {
					$(document).bind("keyup." + key + n, key, function (e) {
						if (e.which == codes[key]) {
							var target = $(e.target);
							if (target.is(":input") || target.is("select"))
								return;
							else {
								e.preventDefault();
								callback.call(this, e);
								return false;
							}
						}
					});
				}
		}
	});
});