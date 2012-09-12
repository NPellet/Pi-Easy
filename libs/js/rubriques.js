// JavaScript Document

function prepareRubriques() {
	
	var fixHelper = function(e, ui) {
	    ui.children().each(function() {
	        $(this).width($(this).width());
	    });
	    return ui;
	};

	$("#HandleRubriques tr").sortable({
		handle: '.Title',
		connectWith: '#HandleRubriques tr',
		placeholder: 'ui-state-highlight',
		helper: fixHelper
	});
	
	$("#RemoveRubrique").data('onSelect', function(button, mode) {

		if(mode == false) {
			$('#HandleRubriques .Rubrique').overlay('destroy');
			return;
		}

		$('#HandleRubriques .Rubrique').bind('click', function() {
			$(this).addClass('Remove');
			$(this).overlay({
				color: 'Red',
				message: 'Cette rubrique sera supprimée',
				mode: 'div',
				zIndex: 1001
			});
		});
	}).data('onValidate', function() {
		return;
	});
	
	$("#EditRubrique").data('onSelect', function(button, mode) {
		
		if(mode == false) {
			$("#HandleRubriques .Rubrique .Content .Edit").fadeOut('slow', function() {
				$(this).remove();
			});
			return;
		}

		$("#HandleRubriques").find('.Rubrique').not('.NoRubrique').each(function() {
			
			var content = '<div class="Edit"><h4>Nom de la rubrique :</h3>';
			for(var i in $.moduleLangs)
				content += '<label>' + i + '&nbsp;</label><input type="text" rel="' + i + '" value="'+ ($(this).data('lang-' + i) !== undefined ?  $(this).data('lang-' + i) : 'Rubrique sans nom') + '" /><div class="Spacer"></div>';
			content += '</div>';
			content = $(content);
			$(content).css({
				width: $(this).children('.Content').width(),
				height: $(this).children('.Content').height(),
			});
			
			$(this).children('.Content').prepend(content);
		});
	}).data('onValidate', function(button) {
			
			$("#HandleRubriques").find('.Rubrique').not('.NoRubrique').each(function() {
				
				var rub = this;
				$(this).children('.Content').children('.Edit').find('input[type=text]').each(function(i) {
					if(i == 0)
						$(rub).children('.Title').text($(this).val());
						
					$(rub).data('lang-' + $(this).attr('rel'), $(this).val());
				});
				
			});
			
			$(button).pieasybutton('select', false);
		});
	
	var entries = $("#HandleRubriques").find('.Rubrique').children('.Content').children('.Entry');
	var rubriques = $("#HandleRubriques").find('.Rubrique').children('.Content');
	
	rubriques.droppable({
		accept: '.Entry',
		drop: function(event, ui) {

			var handle = event.originalEvent.target;
			$(handle)
				.insertBefore($(this).children('.Spacer'))
				.css({
					position: 'static',
					left: 0,
					top: 0
				})
				.css({
					position: 'relative'
				});
		},
		tolerance: 'fit'
	});
	
	entries.draggable({
		
		revert: 'invalid',
		stop: function(event, ui) {
		
		}
	});
}

$(document).ready(function() {
	
	$("#AddRubrique").bind('click', function(button, mode) {
		
		var lastTr = $("table#EditRubriques td#NoRubrique").parent().prev();
		
		var numLastTds = lastTr.children().length;
		var addTr = false;
		if(lastTr.length == 0) {
			addTr = true;
			lastTr = $("table#EditRubriques td#NoRubrique").parent();
		}
		
		if(numLastTds == 3 || addTr) {
			var newTr = $("<tr></tr>");
			
			if(addTr)	lastTr.before(newTr);
			else		lastTr.after(newTr);
			
		} else
			var newTr = lastTr; 
		
		newTr.append('<td><div class="Rubrique"><div class="Title">Rubrique sans nom</div><div class="Content"></div></div></td>');
		prepareRubriques();
	});
	prepareRubriques();
	/*
	$("#EditRubriques").bind('click', function(event) {
		if($(this).hasClass('Disabled'))
			return;
			
		$.colorbox({width: '50%', href: './libs/ajax/editrubriques.php?idModule=' + $.nav.module });

	});*/	
	
	$("#SaveRubriques").bind('click', function() {
		
		var rubriques = {};
		var sort = 0;
		$("#HandleRubriques .Rubrique").each(function() {
			
			sort++;
			
			var remove = false;
			if($(this).hasClass('Remove'))
				remove = true;
			
			var id = $(this).data('rub-id');
			if(id == 0) id = '';
			else if(id == undefined) id = 'new';
			
			var lang = {};
			for(var i in $.moduleLangs)
				lang[i] = $(this).data('lang-' + i) !== undefined ? $(this).data('lang-' + i) : 'Rubrique sans nom';
			
			var content = [];
			
			$(this).children('.Content').children('.Entry').each(function() {
				content.push($(this).data('entry-id'));
			});
			
			rubriques[sort] = {id: id, label: lang, content: content, removed: remove, order: sort};
		});
		
		
		$.get('./libs/ajax/editrubriques.php', {rub: rubriques, idx_module: $.nav.module}, function(data) {
			$("#HandleRubriques").overlay({
				color: 'Green',
				message: 'Les rubriques ont correctement été éditées',
				mode: 'div',
				zIndex: 1002
			});
		});
	});				   
});
