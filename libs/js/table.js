// JavaScript Document

function defaultTrAction(table) {

	if($(table).hasClass('Rubriques')) {

		$(table).find('tr[rel]').bind('click', function(event) {

			event.stopPropagation();
			event.preventDefault();
			var rubId = $(this).attr('rel');
			editRubrique(rubId, langs, rubs[rubId], moduleId);
			
			//$(button).pieasybutton('select', false);
		});
	}
	
	else {
		$(table).find('tr[rel]').bind('click', function() {
			var url = {action: 'edit'};
			url[$.nav.mode] = $(this).attr('rel');
			$.redirect(url);
		});
	}
}

$(document).ready(function() {
						   
	$("table.Data").each(function() {
		
		var table = $(this);
			
		defaultTrAction(table);
			
		var page = 1;
		var sort = '';
		var sortType = 'asc';
		var sortMode = false;
		var rub = $(table).attr('rel');
		var module = $.nav.module;

		var pagination = $(this).next();
		$(pagination).children('a').bind('click', function() {	
			$(this).parent().children('a').removeClass('Selected');
			$(this).addClass('Selected');
			page = $(this).html();
			update();
		});
								  
		$(this).find('th.Sortable').bind('click', function() {
			sort = $(this).attr('rel');	
			var $img = $(this).find('img');
			sortType = $img.hasClass('asc') ? 'desc' : 'asc';
			if($img.length > 0)
				$img.attr('src', './design/' + sortType + '.png').attr('class', sortType);
			else {
				$(this).parent().find('img').remove();
				$(this).append('<img src="./design/' + sortType + '.png" class="' + sortType + '">');
			}
				
			update();
		});
		
		$(document).bind('sortMode', function(event, intOn) {
			sortMode = intOn;
			update(function() {
				
				if(intOn)
					$(table).find('tbody').sortable();	
				else			
					$(table).find('tbody').sortable('destroy');
			});
		});
		
		$(this).bind('update', update);
		
		function update(callback) {
			$.get('./libs/ajax/tableData.php', {p: page, s: sort, st: sortType, r: rub, sm: sortMode, m: module}, function(data) {
				$(table).find('tbody').html(data);
				setTableEvents();
			/*	findElementsToRemove();*/
				
				defaultTrAction(table);
				
				if($.isFunction(callback))
					callback.call(this, table);
				
			});					  
		}
	});	
	
	$.toRemove = [];	
	$(".Remove").buttonRemove('Cette entrée sera supprimée', './libs/ajax/remove.php', $.nav.module);
	
	$("#Field").data('onSelect', function(button, mode) {
										  
		if($(this).hasClass('Disabled'))
			return;

		if(mode == true) {
			$("table tr").bind('click', function(event) {
										 
				if($(button).hasClass('Disabled'))
					return;
			
				event.stopPropagation();
				event.preventDefault();
				var url = {action: $(button).attr('id').toLowerCase()};
					url[$(button).attr('rel')] = $(this).attr('rel');
					url['mode'] = 'field';	
					$.redirect(url);
				});
		}/* else
			$('table tr').unbind('click');*/
	});

	function setTableEvents() {
	
		$(".SortEntries").data('onSelect', function(button, mode) {
											
			if($(this).hasClass('Disabled'))
				return;
		
			if(mode == true) {
					
			/*	if($.cfg['SORT_DIALOG'] !== undefined && $.cfg['SORT_DIALOG'] == '1')
					$.colorbox({href:"./html/infossort.html", width: '50%'});
		*/
				$(document).trigger('sortMode', 1);
				$(".Pages").fadeOut();
			
			} else {
				$(".Pages").fadeIn();
				$(document).trigger('sortMode', 0);
			}
		}).data('onValidate', function(button) {
			
			var i = 1;
			var sort = {};
			$("table.Data tbody").find('tr[rel]').each(function() {
				sort[$(this).attr('rel')] = i;
				i++;
			});
			
			$.get('./libs/ajax/sort.php', {module: $.nav.module, sort: sort}, function(data) {
				$(document).trigger('sortMode', 0);
			});
			
			$(button).pieasybutton('select', false);
			
			/*$.get(url, {extraData: extra, type: $(button).attr('rel'), tosort: $.toSort.join()}, function() {

				$(button).parent().parent().find("table.Data tr").find('.Overlay').parent().parent().overlay('destroy').hide('slow');
				//$("table.Data.Entries tr").find('.Overlay').parent().parent().overlay('destroy').fadeOut();
				//$(button).parent().parent().find("table tr").unbind('click');			
				$(".Button.Remove").pieasybutton('select', false);
				defaultTrAction($(button).parent().parent().find("table.Data"));
			});
			$(".Button.Remove").pieasybutton('select', false);
			*/
		});
	}
	
	setTableEvents();
});


(function($){
 
 	$.fn.pieasybutton = function() {

		var args = arguments;
		return this.each(function() {
			
			var button = this;			
			var select = function(value, delegated) {
				
				$(button).parent().parent().find("table.Data tr[rel]").unbind('click');
		
				class_('Selected', value); 
				if(value == true) {
					
					if($(button).children('a').text() !== 'Valider')
						$(button).data('text', $(button).children('a').text());
				
					if(delegated !== true) {
						$(".Button.Disablable").addClass('Disabled');
					
					//$(".Button").not(this).addClass('Disabled');
					}
					$(button).removeClass('Disabled');
			//		if(validable())
						$(button).children('a').text('Valider');

					$(button).append($('<span class="Cancel"></span>').bind('click', function(event) {
					    event.stopPropagation();
						event.preventDefault();
						select(false);
					}));
					
					if($.isFunction($(button).data('onSelect')))
						$(button).data('onSelect').call(this, button, true, delegated);	

				} else {
					$(".Button").removeClass('Disabled');
					$(button).children('span').remove().end()
					.data('selected', false).removeClass('Selected').children('a').text($(button).data('text'));
					
					if($.isFunction($(button).data('onSelect')))
						$(button).data('onSelect').call(this, button, false, delegated);
					
					defaultTrAction($(button).parent().parent().find("table.Data"));
				}
			}			

		
			var class_ = function(class_, value) {
				if(value == undefined) {
					return $(button).hasClass(class_);
				}
				else
					if(value == true)
						$(button).addClass(class_);
					else
						$(button).removeClass(class_);
			}
			
			var disable = function(value) { return class_('Disabled', value); }
			var disabled = function() { return class_('Disabled'); }
			
			var selected = function(value) { return class_('Selected'); }
			
			var selectable = function() { return class_('Selectable'); }
			var validable = function() { return class_('Validable'); }

			if(args[0] == 'select') {
				return select(args[1], true);
			}
			
			$(button).bind('click', function(event) {
			
				event.stopPropagation();
				if(disabled())
					return;

				if(selected()) {
					
					if(validable()) {
						
						if($.isFunction($(button).data('onValidate')))
							$(button).data('onValidate').call(this, button, true);
							
					}
					select(false);
				} else if(selectable()) {					
					select(true);
				}
			});

		});
	}
	
	$.fn.buttonRemove = function(text, url, extra, selectorr) {
		
		return this.each(function() {
			
			var selector = !selectorr ? "table.Data.Entries tr" : selectorr;
			
			
			$(this).data('onSelect', function(button, mode, delegated) {
		
			if(mode == true) {
				
				if(delegated !== true) {
					$(".Button.Remove").pieasybutton('select', true);
					$.toRemove = [];
					

					//if($(button).parents('#colorbox').length == 0)
				/*		if($.cfg['REMOVE_DIALOG'] !== undefined && $.cfg['REMOVE_DIALOG'] == '1')
							$.colorbox({href:"./html/inforemove.html", width: '50%'});			
					*/
					
					$(button).parent().parent().find(selector).bind('click', function() {
						
						var $tr = $(this);
						var el = $tr.get(0).nodeName.toLowerCase();
						if(el == 'tr')
							el = 'td';
						
						$tr.overlay({
							mode: el,
							color: 'Red',
							message: text,
							onLayerClick: function() {
								$tr.overlay('destroy');
								for(var i = 0; i < $.toRemove.length; i++)
									if($.toRemove[i] == $tr.attr('rel'))
										$.toRemove[i] = undefined;
							}
						});
						$.toRemove.push($(this).attr('rel'));
				
					});
				}
			} else {
				$("table.Data tr").overlay('destroy').unbind('click');
				$.toRemove = [];
				if(delegated !== true)
					$(".Button.Remove").pieasybutton('select', false);
			}
													  
		 }).data('onValidate', function(button) {
			
			$.get(url, {extraData: extra, type: $(button).attr('rel'), toremove: $.toRemove.join()}, function() {

				var wrapper = $(button).parent().parent();
				
				wrapper.find(selector).each(function() {
					var el = $(this);
					
					if(el.find('.Overlay').length == 0)
						return;
						
					var node = el.get(0).nodeName.toLowerCase();
					if(node == 'div')
						el.overlay('destroy').hide('slow');
					else {
						el.overlay('destroy');
						el.hide('slow');
					}
					
				});
				
				$(button).parent().parent().find("table tr, div").unbind('click');
				$(".Button.Remove").pieasybutton('select', false);
				//$("table.Data.Entries tr").find('.Overlay').parent().parent().overlay('destroy').fadeOut();
				defaultTrAction($(button).parent().parent().find("table.Data"));
			});
			$(".Button.Remove").pieasybutton('select', false);
			
		});

								  
		});
		
	}
 }) (jQuery);