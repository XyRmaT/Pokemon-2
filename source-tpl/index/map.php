{template index/header}

<script src="./source/plugin/pokemon_n/source-tpl/js/jquery.hotkeys-0.7.9.js" type="text/javascript"></script>

<br class="cl">

<div class="map">
	<div class="map-corrector"></div>
	<div class="map-tile">
		<!--{loop $onlineTrainer $val}-->
			<div class="char" id="$val[nat_id]" style="left:{$val[x]}px;top:{$val[y]}px;" title="$val[username]"></div>
		<!--{/loop}-->
	</div>
	<div class="map-overlay"></div>
	<div class="map-battle">
		<div id="btl-report"></div>
		<div id="sbj-oppo"></div>
		<div id="sbj-self"></div>
		<div id="obj-moves"></div>
		<div id="obj-miscbtn">
			<button id="pokemon">精灵</button><br>
			<button id="item">道具</button><br>
			<button id="run">逃跑</button>
		</div>
	</div>
	<div id="layer-await"></div>
	<div id="layer-item"></div>
	<div id="layer-pokemon"></div>
</div>

<script>
	$(function() {

		var processing = false, 
			reacttime = (new Date()).getTime(), 
			stepcount = 0, 
			avltile = $tilejs
		
		/*var txt = '';
		for(i in avltile) {
			txt += '[';
			for(var j = 29; j >= 0; j--) {
				var checked = false;
				for(k in avltile[i]) {
					if(avltile[i][k] === j) {
						checked = true;
						break;
					}
				}
				txt += (checked === true ? '1' : '0') + ', ';
			}
			txt += '], \n';
		}
		console.log(txt);*/
		
		$.fn.extend({
			_move: function(direction, steplimit, x, y) {
				if(this.queue().length > 0 && typeof x === 'undefined') return false;
				var coord = (typeof x === 'undefined') ? this.getpos() : {'x': x, 'y': y},
					find = false,
					posy = {up: '-21', down: '0', right: '-42', left: '-63'}, 
					frames = ['0', '-16', '-32', '0'], 
					correct = {
						up: [16, 11, 5, 0], 
						down: [-16, -11, -5, 0], 
						right: [-16, -11, -5, 0], 
						left: [16, 11, 5, 0]
					}, 
					hrz = (direction === 'left' || direction === 'right') ? true : false, 
					o = this, 
					save = 0, 
					ncoord = coord;
					
				switch(direction) {
					case 'up': ncoord.y -= 1; break;
					case 'down': ncoord.y += 1; break;
					case 'left': ncoord.x -= 1; break;
					case 'right': ncoord.x += 1; break;
				}

				if(typeof avltile[ncoord.x] === 'undefined') return false;

				if(avltile[ncoord.x][ncoord.y] === 0) find = true;
				
				$('.char').css('z-index', 2);
				
				for(var i = 0; i < 4; i++) {
					(function(i) {
						o.queue(function() {
							o.animate({
								backgroundPositionX: frames[i] + 'px', 
								backgroundPositionY: posy[direction] + 'px'
							}, {
								step: function(now, fx) {
									$(fx.elem).css("background-position", frames[i] + "px "+posy[direction]+"px");
								},
								duration: 1
							});
							o.dequeue();
						});
					})(i);
					if(find === false) return false;
					
					/* Use extra queues to delay the walking speed, coz im shit at js and couldn't think of any good ideas */
					(function(i) {
						o.queue(function() {
							o.animate({
								'left': ncoord.x * 16 + (hrz === true ? correct[direction][i] : 0),
								'top': ncoord.y * 16 + (hrz === false ? correct[direction][i] : 0)
							}, 50);
							o.dequeue();
						});
					})(i);
				};
				if(steplimit === true) {
					o.queue(function() {
						stepcount += 1;
						o.dequeue();
					});
				}
			}, 
			getpos: function() {
				return this.coord(this.offset());
			}, 
			coord: function(pos, posb) {
				var offset = $('.map-tile').offset();
				return {
					x: Math.floor(((typeof pos === 'object' ? pos.left : pos) - offset.left) / 16), 
					y: Math.floor(((typeof pos === 'object' ? pos.top : posb) - offset.top) / 16)
				};
			}, 
			moveto: function(x, y) {
				if(this.queue().length > 0 || DISABLE.MAPMOVE === true) return;
				
				var pos = this.getpos(), 
					paths = avltile.getPath(pos.x, pos.y, x, y), 
					o = this;
					
				if(paths.length > 0) {
					for(i in paths) {
						i = parseInt(i);
						if(typeof paths[i + 1] === 'undefined') break;
						var now = paths[i], 
							next = paths[i + 1], 
							direction = (now[0] === next[0]) ? (now[1] > next[1] ? 'up' : 'down') : ((now[1] === next[1]) ? (now[0] > next[0] ? 'left' : 'right') : '');
						if(direction !== '') o._move(direction, false, now[0], now[1]);
					}
				}
			}, 
			moves: function(direction) {
				if(DISABLE.MAPMOVE === true) return;
				var me = this;
				reacttime = (new Date()).getTime();
				if(stepcount >= 10) {
					DISABLE.AJAXLOAD = true;
					$('#layer-await').queue(function() {
						ajax('?index=map&process=walk&x=' + Math.floor(parseInt(me.css('left')) / 16) + '&y=' + Math.floor(parseInt(me.css('top')) / 16), function() {
							$('#layer-await').clearQueue();
							stepcount = 0;
							DISABLE.AJAXLOAD = false;
						});
					});
				}
				this._move(direction, true);
			}
		});
		Astar.call(avltile, avltile);
		
		/*p = function(tid, username, x, y) {
			if($('.char#t' + tid).length < 1)
				$('.map-tile').append('<div class="char" id="t' + tid + '" style="left:0px;top:176px;" title="' + username + '"></div>');
			$('.char#t' + tid).moveto(x, y);
		};*/
		
		p = function(data) {
			if(!data) return;
			var tids = [], 
				newtids = [];
			$('.char').each(function(i) {
				var tid = $(this).attr('id').substring(1).toString();
				if(tid === 'e') return;
				tids[tid] = tid;
			});
			$.each(data, function() {
				if(!tids[this[0]]) {
					$('.map-tile').append('<div class="char" id="t' + this[0] + '" style="left:0px;top:176px;" title="' + this[1] + '"></div>');
				}
				$('.char#t' + this[0]).moveto(this[2], this[3]);
				newtids[this[0]] = 1;
			});
			for(i in tids) {
				if(!newtids[tids[i]]) {
					$('.char#t' + tids[i]).fadeOut(666, function() {
						$(this).remove();
					});
				}
			};
		};
			
		/* Set the hotkey */
		$.hotkey('up', function() { $('#me').moves('up'); });
		$.hotkey('down', function() { $('#me').moves('down'); });
		$.hotkey('left', function() { $('#me').moves('left'); });
		$.hotkey('right', function() { $('#me').moves('right'); });
		
		
		setInterval(function() {
			if(processing === true || (new Date()).getTime() - reacttime >= 60000 || DISABLE.MAPMOVE === true) return;
			processing = true;
			ajax('?index=map&process=update', function() {
				processing = false;
			});
		}, interval);
		
		/*var txt = '';
		for(i in stock) {
			txt += i + ': [';
			stock[i] = stock[i].sort(function(a, b){
				return a - b;
			});
			for(j in stock[i]) {
				txt += stock[i][j] + ', ';
			}
			txt += '], \n';
		}
		console.log(txt);*/

		initbattle = function() {

			DISABLE.MAPMOVE = true;
			STATE.BATTLE = true;
			var img = new Image();
			img.src = '{ROOT_IMAGE}/battle-bg/bg-1.jpg';
			
			initbattlelayer();
			$('.map-battle, .map-overlay').show();
			$('.map-overlay')
				.animate({ backgroundColor: 'transparent' }, 1)
				.animate({ backgroundColor: '#000' }, 255)
				.animate({ backgroundColor: 'transparent' }, 255)
				.animate({ backgroundColor: '#000' }, 255)
				.animate({ backgroundColor: 'transparent' }, 255)
				.animate({ backgroundColor: '#000' }, 255)
				.queue(function() {
					$('.map-battle')
						.css({'background-image': 'url({ROOT_IMAGE}/battle-bg/bg-1.jpg)', opacity: 0})
						.animate({'opacity': 1 }, 255);
					$(this).dequeue();
				});
			$('#obj-miscbtn').find('#item, #pokemon').removeAttr('disabled');
			$('#obj-miscbtn #run').html('逃跑');
		};
		
		initbattlelayer = function(action) {
			if(action === 'END') {
				STATE.BATTLE = false;
				$('.map-battle')
					.animate({'opacity': 0}, 255)
					.queue(function() {
						$(this).css('z-index', 0);
						$('.map-overlay').css('z-index', 0);
						$(this).dequeue();
						$('.map-battle, .map-overlay').hide();
					});
			} else {
				$('.map-battle').css({'z-index': 7, 'opacity': 0});
				$('.map-overlay').css('z-index', 6);
			}
		};
		
		updatehpbar = function(target, hp, maxhp) {
		
			var ele	= $('#sbj-' + target);

			/*
				There is a bug on animating width with percentage, 
				Jquery will treat percent as pixel
			*/
			ele.find('.hp').animate({width: Math.ceil(149 * hp / maxhp)}, 1000);
			ele.find('.value').html(hp + '/' + maxhp);
			
		};
		
		updatebattlefield = function(i) {
			if(i.report)
				$('#btl-report').append(i.report).animate({
					scrollTop: $('#btl-report').prop('scrollHeight')
				});
			if(typeof i.oppohp !== 'undefined') updatehpbar('oppo', i.oppohp, i.oppomaxhp);
			if(typeof i.selfhp !== 'undefined') updatehpbar('self', i.selfhp, i.selfmaxhp);
			$('#sbj-oppo .status').replaceWith(i.oppostatus);
			$('#sbj-self .status').replaceWith(i.selfstatus);
			if(i.selfmove) {
				var tmp = '';
				for(m in i.selfmove)
					tmp += '<div data-move_id="' + i.selfmove[m][0] + '"' + ((i.selfmove[m][1] <= 0) ? ' class="disabled"' : '') + '>' + i.selfmove[m][2] + ' <em>' + i.selfmove[m][1] + '/' + i.selfmove[m][3] + '</em></div>';
				$('#obj-moves').html(tmp);
			}
			if(i.end) {
				DISABLE.BATTLEEND = true;
				$('#obj-miscbtn').find('#item, #pokemon').attr('disabled', true);
				$('#obj-miscbtn #run').html('离开');
			}
			DISABLE.AJAXLOAD = false;
		};
		
	});
</script>

{template index/footer}