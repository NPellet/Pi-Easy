// JavaScript Document
(function($) {
	
	var methods = {
		
		destroy: function() {
			
			$(this).find('.Overlay').fadeOut(function() {
				$(this).remove();
			});
		},
		
		init: function(options) {
			var self = $(this);
			
			if(options.mode == 'td') {
				var $td = self.children(':first');
				var paddingLeft = $td.css('padding-left');
				var paddingTop = $td.css('padding-top');
				
				var $toPrepend = $td;
			} else if(options.mode == 'div') {
				
				var $toPrepend = self;
				var paddingLeft = 0;
				var paddingTop = 0;
			}
	
			function formatMessage(txt, button) {
				
				if(options.mode == 'td') {
					if(button != '') 
					txt = (button !== undefined ? '<input type="Submit" class="Button" value="' + button + '" />' : '') + txt;
					var message = $('<div class="Overlay Td"><table cellpadding="0" cellspacing="0"><tr><td>' + txt + '</td></tr></table></div>');
					
				} else if(options.mode == 'div') {
					var txt = '<p>' + txt + '</p>' + (button != undefined ? '<p><input type="Submit" class="Button" value="' + button + '" /></p>' : '');
					var message = $('<div class="Overlay Div"><table cellpadding="0" cellspacing="0"><tr><td>' + txt + '</td></tr></table></div>');
				}
				
				$(message).find('input').bind('click', function(event) {
					event.stopPropagation();
					event.preventDefault();
					
					if($.isFunction(options.onButtonClick))
						options.onButtonClick.call(this, event, self, message);
						
				}).end().not('input').bind('click', function(event) {
					event.stopPropagation();
					event.preventDefault();
					
					if($.isFunction(options.onLayerClick))
						options.onLayerClick.call(this, event, self, message);
				});
				
				return message;
			}
				
			var previous = $toPrepend.children('.Overlay:first');
			var zIndex = options.zIndex !== undefined ? options.zIndex :  previous.length > 0 ? parseInt($(previous).css('z-index')) + 1 : 2;
	
			var newHeight = self.height();
			var newWidth = self.width();
		
			var message = formatMessage(options.message, options.button);
	
			$(message).css({ 
				position: 'absolute', 
				height: newHeight + "px", 
				marginLeft: '-' + paddingLeft, 
				marginTop: '-' + paddingTop, 
				opacity: 0, 
				'z-index': zIndex	
			}).animate({ 
				width: newWidth + "px", 
				opacity: 1
			}).addClass(options.color)
			.prependTo($toPrepend);
		}
	}
	
	$.fn.overlay = function(method) {
		var args = arguments;
		return this.each(function() {
			var self = this;
			if (methods[method] !== undefined)
			  methods[method].apply(this, Array.prototype.slice.call(args, 1));
			else if ( typeof method === 'object' || ! method )
			  methods.init.apply( this, args );
		});
	}
	
}) (jQuery);