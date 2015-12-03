</div>

<br class="cl">

<div id="layer-alert"></div>

<div id="footer">
	目前时间：<!--{echo date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);}-->MU: <!--{echo Kit::Memory(memory_get_usage(TRUE))}--><br>
	<!--{if $user['uid'] == 8}-->Processed in 0 second(s), 0 queries. <br><!--{/if}-->
	请使用现代浏览器（如<a href="http://www.google.com/chrome" target="_blank">谷歌浏览器</a>）访问以取得最佳效果。<br>
	Copyright &copy; 2013-{YEAR} PokeUniv (Pet). Version $system[version].
</div>

<script>
	<!--{if 1==2 && empty($user['uid'])}-->
		$(function() {
		
			$('body').append('<div id="layer-unlogged" class="h">登陆页暂未制作完毕，<a href="member.php?mod=logging&action=login" target="_blank" class="forum"><b>点我进入论坛登陆</b></a>！</div>');
			
			$('#layer-unlogged').dialog({
				modal: true, 
				title: '欢迎！', 
				buttons: {
					'我先看看': function() {
						$(this).dialog('close');
					}
				}, 
				resizable: false
			});
			
		});
	<!--{/if}-->
	var Queuenum	= 0;
		Queue		= function(target, fn) {
			target.queue(fn);
			Queuenum++;
		}, 
		Dequeue		= function(target, all) {
			if(all === true) {
				Queuenum -= target.queue().length;
				target.clearQueue();
			} else {
				target.dequeue();
			}
		}, 
		Url			= {
			cache: '{ROOT_CACHE}/image/'
		};
	
	$(function() {

		makenav('.menu li');
		$('[title]').tooltip({
			position: {
				my: 'left center', 
				at: 'right center'
			}, 
			show: false, 
			hide: false, 
			content: function() {
				return $(this).attr('title');
			}
		});
		<!--{if $index === 'shop'}-->
			$('.menub li').click(function(e) {
				e.preventDefault();
				ajax('?index=shop&type=' + $(this).find('a').attr('href'));
			});
			$('.shop-list button').live('click', function() {
				var quantity = prompt('您要购买多少个' + $(this).data('name') + '呢？');
				if(quantity === null) return false;
				else if(isNaN(quantity) || quantity <= 0) {
					alert('数量有误噢～');
					return false;
				}
				ajax('?index=shop&process=itembuy&quantity=' + quantity + '&item_id=' + $(this).data('itemid'));
			});
		<!--{elseif $index === 'pkmcenter'}-->
			$('#pc-boxctnr').unselectable();
			makenav('.pc-nav .sec');
			<!--{if $_GET['section'] === 'box'}-->
				boxlist = {l: {}};
				$('.pc-box').droppable({
					accept: '.pc-box li', 
					drop: function(e, ui) {
						var from = ui.draggable, 
							tmp = $(this);
						if(from.parent().parent().data('bid') === tmp.data('bid') || $('li', this).length >= $(this).data('limit')) return false;
						$('ul', this).append(from.removeAttr('style'));

						var pkm_id = from.data('pkm_id');
						boxlist.l[pkm_id] = tmp.data('bid');
					}
				});
				$('.pc-box li')
					.draggable({
						helper: 'clone', 
						revert: 'invalid', 
						start: correctpos
					});
				$('#pc-boxsave').on('click', function() {
					ajax('?index=pc&process=boxmove&' + $.param(boxlist));
				});
			<!--{elseif $_GET['section'] === 'trade'}-->
				makenav('.pc-tradenav');
				scrollfixed('.pc-nav');
				var form = $('#pc-trade');
				form.submit(function(e) {
					e.preventDefault();
				});
				<!--{if $_GET['part'] === 'search'}-->
					$('.sub-search', form).on('click', function() {
						$('button', form).attr('disabled', true);
						ajax('?index=pc&process=tradesearch&' + form.serialize(), function() {
							$('button', form).removeAttr('disabled');
						});
					});
					BindEvents = function() {
						$('.sub-trade').off().on('click', function() {
							ajax('?index=pc&process=traderequest');
						});
						$('.sub-trade').off().on('click', function() {
							var opid = $(this).parent().data('pkm_id');
							$('#layer-traderequest').dialog({
								modal: true, 
								title: '交换请求', 
								buttons: {
									'就你了': function() {
										ajax('?index=pc&process=traderequest&pkm_id=' + $('.pmchoose li:not(.h)', this).data('pkm_id') + '&opid=' + opid);
										$(this).dialog('destroy');
									}, 
									'不对': function() {
										$(this).dialog('close');
									}
								}, 
								resizable: false
							});
						});
					
						$('.pmchoose .arrow').off().on('click', function() {
							var index		= parseInt($('.pmtarget li:not(.h)').data('index')) + 1, 
								total		= $('.pmtarget li').length, 
								direction	= $(this).data('direction') === 'left' ? 1 : 2, 
								targetIndex = (direction === 1 && index === 1 || direction === 2 && index === total) ? (direction === 1 ? total : 1) : index - 1 * (direction === 1 ? 1 : -1);
							if(total < 2) return;
							$('.pmtarget li')
								.addClass('h')
								.filter('[data-index=' + (targetIndex - 1) + ']').removeClass('h');
						});
						$('.mp li:not(.cur)', form).on('click', function() {
							var parent = $(this).parent();
							ajax('?index=pc&process=tradesearch&' + parent.data('urlpart') + '&pagenum=' + $(this).data('pagenum'), function() {
								$('button', form).removeAttr('disabled');
							});
						});
					};
				<!--{else}-->
					$('.pc-tradelist button').on('click', function() {
						var action = $(this).attr('class').substring(4), 
							parent = $(this).parent();
						ajax('?index=pc&process=trade' + action + '&tradeid=' + parent.data('tradeid'), function(i) {
							if(i.succeed) {
								var grandparent = parent.parent();
								parent.remove();
								if($('li', grandparent).length < 1) {
									grandparent.next('.no').show();
									grandparent.remove();
								}
							}
						});
					});
				
				<!--{/if}-->
			<!--{else}-->
				scrollfixed('.pc-nav');
				$('.pc-info li').live('click', function() {
					if($(this).hasClass('cur')) {
						$(this).removeClass('cur').find('input').attr('checked', false);
					} else {
						$(this).addClass('cur').find('input').attr('checked', true);
					}
					var length = $('.pc-info li.cur').length;
					if($(this).hasClass('heal'))
						$('#pmcount').html(length);
					$('#pc-heal button').attr('disabled', (length <= 0) ? true : false);
				});
				$('#pc-heal').submit(function(e) {
					e.preventDefault();
					ajax($(this).attr('action') + '&' + $(this).serialize(), function() {
						$('#pmcount').html('0');
						$('#pc-heal button').attr('disabled', true);
					});
				});
			<!--{/if}-->
		<!--{elseif $index === 'memcp'}-->
			$('#my td.nav').on('click', function(e) {
				e.preventDefault();
				if($(this).hasClass('current')) return;
				$('#my td.current').removeClass('current');
				$(this).addClass('current');
				ajax($('a', this).attr('href'));
			});
			BindEvents = function() {
				$('#pm-grid').sortable({
					update: function() {
						var tmp = $(this), 
							uri = $('#pm-grid input:hidden').map(function() {
								return 'order[]=' + $(this).val();
							}).get().join('&');
						Queue(tmp, function() {
							Dequeue(tmp, true);
							ajax('?index=my&process=pmreorder&' + uri);
						});
					}
				});
				$('#pm-grid .txt-c').off().on('click', function() {
					var tmp = $(this).parent();
					$('#info-' + tmp.data('pkm_id')).dialog({
						modal: true, 
						title: tmp.data('nickname') + '的详细数据', 
						resizable: false, 
						width: '425px'
					});
				});
				$('.pmabandon').off().on('click', function() {
					var pkm_id = $(this).parent().parent().data('pkm_id');
					$('#layer-abandon').dialog({
						modal: true, 
						title: '抛弃精灵', 
						buttons: {
							'永别了！': function() {
								ajax('?index=my&process=pmabandon&pkm_id=' + pkm_id);
								$(this).dialog('close');
							}, 
							'我反悔了！': function() {
								$(this).dialog('close');
							}
						}, 
						resizable: false
					});
				});
				$('.pmnickname')
					.off()
					.on('click', function() {
						var tmp = $(this).parent().parent();
						var input = $('#layer-nickname [name=nickname]');
						input.val(tmp.data('nickname'));
						$('#layer-nickname').dialog({
							modal: true, 
							title: '更改昵称', 
							buttons: {
								'确定': function() {
									if(input.val() === '' || input.val().length > 6) {
										input.addClass('ui-state-error');
									} else {
										ajax('?index=my&process=pmnickname&pkm_id=' + tmp.data('pkm_id') + '&nickname=' + encodeURI(input.val()));
										$(this).dialog('close');
									}
								}, 
								'不改了': function() {
									$(this).dialog('close');
								}
							}, 
							resizable: false
						});
					});
				$('#layer-nickname [name=nickname]').off().on('focus', function() {
					$(this).removeClass('ui-state-error');
				}).on('blur', function() {
					if($(this).val() === '' || $(this).val().length > 6) {
						$(this).addClass('ui-state-error');
					}
				});
				
				$('.pmchoose .arrow').off().on('click', function() {
					var index		= parseInt($('.pmtarget li:not(.h)').data('index')) + 1, 
						total		= $('.pmtarget li').length, 
						direction	= $(this).data('direction') === 'left' ? 1 : 2, 
						targetIndex = (direction === 1 && index === 1 || direction === 2 && index === total) ? (direction === 1 ? total : 1) : index - 1 * (direction === 1 ? 1 : -1);
					if(total < 2) return;
					$('.pmtarget li')
						.addClass('h')
						.filter('[data-index=' + (targetIndex - 1) + ']').removeClass('h');
				});
				$('.pmmove').click(function() {
					var parent	= $(this).parent().parent(), 
						tmp		= $(this), 
						pkm_id		= parent.data('pkm_id');
					$('#learnmove').html($('.moves_new', parent).html());
					$('#layer-moves').dialog({
						modal: true, 
						title: '学习技能', 
						buttons: {
							'学习这个技能！': function() {
								ajax('?index=my&process=pmmove&pkm_id=' + pkm_id + '&' + $('#learnmove').serialize(), function(i) {
									if(!i.learnmove) return;
									if(i.learnmove !== '') {
										$('.moves_new', parent).html(i.learnmove);
									} else {
										$('.moves_new', parent).remove();
										tmp.remove();
									}
								});
								$(this).dialog('destroy');
							}, 
							'我再想想吧': function() {
								$(this).dialog('close');
							}
						}, 
						resizable: false
					});
				});
				/* achievement */
				$('#my-achv li').off().on('click', function() {
					var tmp = $(this);
					if(tmp.hasClass('done')) return;
					ajax('?index=my&process=achvcheck&achv_id=' + tmp.data('achv_id'), function(i) {
						if(i.succeed) {
							tmp.addClass('done');
							tmp.find('.achieved').html('完成');
						}
					});
				});
				/* inbox */
				$('#my-inbox .mp li:not(.cur)').off().on('click', function() {
					ajax('?index=my&section=inbox&pagenum=' + $(this).data('pagenum'));
				});
				$('#my-inbox .del').off().on('click', function() {
					var confirmed = confirm('确定？');
					if(!confirmed) return;
					ajax('?index=my&process=inboxdel&msg_id=' + $(this).data('msg_id'));
				});
				/* inventory */
				var f = function() {
					$('#my-invtp .item [data-item_id]').off().on('click', function() {
						if(!confirm('确定要卸掉携带道具？')) return;
						var upd = item[$(this).data('item_id')];
						if(upd.quantity < 1) $('#my-invt').append($('<li>' + $(this)[0].outerHTML + '</li>').fadeIn());
						upd.quantity++;
						f();
						ajax('?index=my&process=pmitem&item_id=0&pkm_id=' + $(this).parent().parent().data('pkm_id'));
						$(this).fadeOut(function() {
							$(this).remove();
						});
					});
					return $('#my-invt img').draggable({
						revert: 'invalid', 
						helper: 'clone', 
						zIndex: 10000, 
						start: function() {
							$('#my-invt li').tooltip('disable');
						}, 
						stop: function() {
							$('#my-invt li').tooltip('enable');
						}
					}).on('click', function() {
						var item_id = $(this).data('item_id');
						$('#layer-useitem').find('.name').html(item[item_id].name);
						$('#layer-useitem').dialog({
							modal: true, 
							title: '使用道具', 
							buttons: {
								'就决定是你了！': function() {
									ajax('?index=my&process=itemuse&item_id=' + item_id + '&pkm_id=' + $('.pmtarget li:not(.h)', this).data('pkm_id'));
									$(this).dialog('destroy');
								}, 
								'手滑=。=': function() {
									$(this).dialog('close');
								}
							}, 
							resizable: false
						});
					}).parent().tooltip({
						items: 'li', 
						position: {
							my: 'left center', 
							at: 'right center'
						}, 
						show: false, 
						hide: false, 
						content: function() {
							var i = item[$(this).find('img').data('item_id')];
							return '名字：' + i.name + '<br>数量：' + i.quantity + '<br>描述：' + i.description;
						}
					});
				};
				$('#my-invtp li').droppable({
					drop: function(e, u) {
						var obj = u.draggable, 
							dest = $(this).find('span.item'), 
							item_id = obj.data('item_id');
						if(dest.find('img[data-item_id]').length > 0) {
							var upd = item[dest.find('img').data('item_id')];
							if(upd.quantity < 1) $('#my-invt').append($('<li>' + dest.find('img')[0].outerHTML + '</li>').fadeIn());
							upd.quantity++;
						}
						dest.hide().html(obj[0].outerHTML).fadeIn();
						if(item[item_id].quantity - 1 <= 0) obj.parent().fadeOut(function() { $(this).remove(); });
						item[item_id].quantity--;
						u.helper.remove();
						f();
						ajax('?index=my&process=pmitem&item_id=' + item_id + '&pkm_id=' + dest.parent().data('pkm_id'));
					}
				});
				initiateItemList = function(type) {
					var appendTxt = '';
					if(typeof item !== 'object') return;
					for(i in item) {
						var obj = item[i];
						if(!type || type > 0 && obj.type == type)
							appendTxt += '<li><img src="' + obj.itemimgpath + '" data-item_id="' + obj.item_id + '"></li>';
					}
					$('#my-invt').fadeOut(function() {
						$(this).empty().append(appendTxt).fadeIn(f);
					});
				};
				$('#my-invt').prev().find('.star:not(.not)').css('cursor', 'pointer').on('click', function() {
					$('.star:not(.not)').removeClass('on').addClass('off');
					$(this).removeClass('off').addClass('on');
					initiateItemList($(this).data('type'));
				});
			};
			BindEvents();
			<!--{if $_GET['section'] === 'inventory'}-->initiateItemList();<!--{/if}-->
			makenav('#my-nav li');
		<!--{elseif $index === 'starter'}-->
			$('img').undraggable();
			$('.st-info img').click(function() {
				var tmp = $(this);
				$('#layer-confirm .name').html(tmp.data('name'));
				$('#layer-confirm').dialog({
					modal: true, 
					title: '选择初始精灵', 
					buttons: {
						'就决定是你了！': function() {
							ajax('?index=starter&process=obtain&sid=' + tmp.data('sid'));
							$(this).dialog('close');
						}, 
						'让我再想想……': function() {
							$(this).dialog('close');
						}
					}, 
					resizable: false
				});
			});
		<!--{elseif $index === 'daycare'}-->
			BindEvents = function() {
			
				$('.dc-pm button').off().on('click', function() {
					$('#layer-savedaycare').dialog({
						modal: true, 
						title: '寄存精灵', 
						buttons: {
							'过一点时间再来看你': function() {
								ajax('?index=daycare&process=pmsave&pkm_id=' + $('.pmchoose li:not(.h)', this).data('pkm_id'));
								$(this).dialog('destroy');
							}, 
							'不对': function() {
								$(this).dialog('close');
							}
						}, 
						resizable: false
					});
				});
			
				$('.pmchoose .arrow').off().on('click', function() {
					var index		= parseInt($('.pmtarget li:not(.h)').data('index')) + 1, 
						total		= $('.pmtarget li').length, 
						direction	= $(this).data('direction') === 'left' ? 1 : 2, 
						targetIndex = (direction === 1 && index === 1 || direction === 2 && index === total) ? (direction === 1 ? total : 1) : index - 1 * (direction === 1 ? 1 : -1);
					if(total < 2) return;
					$('.pmtarget li')
						.addClass('h')
						.filter('[data-index=' + (targetIndex - 1) + ']').removeClass('h');
				});
				
				$('.dc-pm .move_id img').off().on('click', function() {
					var egg = $(this).hasClass('egg') ? 1 : 0, 
						tmp = $(this);
					$(egg === 1 ? '#layer-getegg' : '#layer-getback').dialog({
						modal: true, 
						title: '确认', 
						buttons: {
							'是的': function() {
								ajax('?index=daycare&process=' + (egg === 1 ? 'egg' : 'pm') + 'take' + (egg === 1 ? '' : '&pkm_id=' + tmp.data('pkm_id')));
								$(this).dialog('destroy');
							}, 
							'再说吧': function() {
								$(this).dialog('close');
							}
						}, 
						resizable: false
					});
				});
				
			};
		<!--{elseif $index === 'map'}-->
			BindEvents = function() {
				$('.map-battle :not(#btl-report)').undraggable().unselectable();
				$('#obj-moves > div:not(.disabled)').off().on('click', function() {
					if(DISABLE.BATTLEEND === true || DISABLE.AJAXLOAD === true) return;
					DISABLE.AJAXLOAD = true;
					ajax('?index=battle&process=usemove&move_id=' + $(this).data('move_id'), function(i) {
						if(i.battle) updatebattlefield(i.battle);
					});
				});
				$('#obj-miscbtn #run').off().on('click', function() {
					DISABLE.MAPMOVE		= false;
					DISABLE.BATTLEEND	= false;
					DISABLE.AJAXLOAD	= false;
					initbattlelayer('END');
					$('#sbj-oppo, #sbj-self').removeAttr('style').empty();
					$('#obj-moves, #btl-report').empty();
				});
				$('#obj-miscbtn #item').off().on('click', function() {
					$('#layer-item').dialog({
						modal: true, 
						title: '背包', 
						resizable: false
					});
				});
				$('#obj-miscbtn #pokemon').off().on('click', function() {
					$('#layer-pokemon').dialog({
						modal: true, 
						title: '更换精灵', 
						resizable: false
					});
				});
				$('#layer-item li')
					.off()
					.on('click', function() {
						$('#layer-item').dialog('close');
						ajax('?index=battle&process=useitem&item_id=' + $(this).data('item_id'), function(i) {
							if(i.battle) updatebattlefield(i.battle)
						});
					});
				$('#layer-pokemon li')
					.off()
					.on('click', function() {
						$('#layer-pokemon').dialog('close');
						ajax('?index=battle&process=swappm&pkm_id=' + $(this).data('pkm_id'), function(i) {
							updatebattlefield(i.battle)
						});
					});
			};
		<!--{elseif $index === 'ranking'}-->
		<!--{elseif $index === 'shelter'}-->
			$('.sht-description, .sht-main')
				.unselectable()
				.find('img').click(function() {
					var tmp = $(this);
					$('#layer-claim').dialog({
						modal: true, 
						title: '领养' + ['精灵蛋', '精灵'][$(this).parent().hasClass('egg') ? 0 : 1], 
						buttons: {
							'我会好好照顾它的！': function() {
								ajax('?index=shelter&process=claim&pkm_id=' + tmp.data('pkm_id'), function(i) {
									if(i.succeed) tmp.remove();
								});
								$(this).dialog('close');
							}, 
							'我反悔了！': function() {
								$(this).dialog('close');
							}
						}, 
						resizable: false
					});
				});
			
		<!--{/if}-->
		
		BindEvents();

	});
	
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', 'UA-42789438-2', 'pokeuniv.com');
	ga('send', 'pageview');

</script>
{$synclogin}
</html>