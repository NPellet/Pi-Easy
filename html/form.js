// JavaScript file

if(!PiCom)
	PiCom = {};
	
PiCom.form = {};
PiCom.form._IMPL = {};

PiCom.form._IMPL.defaults = {
	url: null,
	dom: null,
	hideSections: [],
	hideFields: [],
	onFieldClick: null,
	onFieldChange: null,
	onFormSubmit: null,
	useXsslt: true
}


PiCom.form.create = function(options) {
	
	var form = {};
		
	if(options.dom == null && url != null)
		$.get(url, {}, function(content, xhr) {
			form.dom = xhr.documentXML;
			PiCom.form._IMPL.init(form);
		});
	else if(options.dom != null) {
		form.dom = options.dom;
		PiCom.form._IMPL.init(form);
	}

	return form;
}

PiCom.form._IMPL.init = function(form) {
	
	form.fields = [];
	if(form.options.useXslt)
		form.dom = formxslt.init(form.dom);
	
	form.fields = [];
	$(form.dom).find(".field").each(function() {
		
		var field = {};
		var fieldtype = $(this).data('type');
		
		if(PiCom.form._IMPL.types[fieldtype] !== undefined)
			field.impl = PiCom.form._IMPL.types[fieldtype];
		
		field.validation = [];
		$(this).find('validation').each(function() {
			field.validation.push({
				type: $(this).data('type'),
				value: $(this).data('value'),
				message: $(this).text()
			});
		});
		
		field.el = $(this.find('.element'));
		
		form.fields.put(field);
	});
}


PiCom.form._IMPL.getFormValidation = function() {
	
	var error = false;
	for(var i in this.fields) {
		if(!PiCom.form._IMPL.getFieldValidation.call(this, this.fields[i]))
			error = true;
	}
	
	if(!error)
		PiCom.form._IMPL.performAction.call(this);
}

PiCom.form._IMPL.getFieldValidation = function(field) {
	
	var error = PiCom.form._IMPL.getError.call(this, field);
	if(!error)
		field.dom.removeClass("FieldError");
	else {
		field.dom.addClass("FieldError");
		field.error.text(error);
		return false;	
	}

	return true;
}

PiCom.form._IMPL.getFieldError = function(field) {
	
	for(var i = 0; i < field.validation.length; i++) {
		if(field.validaton[i].type == 'null' && field.el.val())
			return field.validation[i].message;
		
		if(field.validation[i].type == 'regex' && ! new Regex(field.validation[i].value).match(field.el.val())) 
			return field.validation[i].message;
			
		if(field.validation[i].type == 'format' && ! new Regex(field.impl.format).match(field.el.val()))
			return field.validation[i].message;
	}

	return false;
}

PiCom.form._IMPL.performAction = function() {
	
	var form = this;
	var actions = this.dom.find('action');
	for(var i = 0; i < actions.length; i++) {
		
		var action = $(actions[i]);
		var actionType = action.attr("type");
		
		switch(actionType) {
			
			case "email":
			
				var emails = [],
					subject,
					template,
					success,
					error;
			
				var params = action.children('param');
				for(var j = 0; j < params.length; j++) {
					var param = $(params[i]);
					var paramName = param.attr("name");
					
					switch(paramName) {
						
						case 'email':
							emails.push(param.attr("value"));
						break;
						
						case 'subject':
							subject = param.text();
						break;
						
						case 'template':
							email = param.text();
							
							for(var i = 0; i < form.fields.length; i++) {
								var name = form.fields[i].el.attr('name');
								var value = form.fields[i].el.val();
								email.replace("%" + name, value);
							}
							
							email = email.replace("")
						break;
						
						case 'success':
							success = param.text();
						break;
						
						case 'error':
							error = param.text();
						break;
					}
				}
			
				$.ajax({
					url: './formimpl.php',
					data: { type: 'email', emails: emails, subject: subject, email: email },
					type: "put",
					success: function(data) {
						if(success != null)
							form.dom.find('.success').html(success);
					},
					
					error: function(data) {
						if(error != null)
							form.dom.find('.success').html(error);
					}
				});
			
			break;
			
			
			
			
		}
		
		
		
	}
	
} 
