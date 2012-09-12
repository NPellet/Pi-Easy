/*
 * Transforms an XML form to an HTML form
 */

var formxslt = new Object();
formxslt.init = function(dom) {
	
	var html = [];
	var dom = $(dom);
	var sections = $(dom).children('<sections>').children();
	
	html = html.concat(['<div class="success"></div><div class="error"></div><form ', 
						XML.util.attrAsAttr(dom, "name"), 
						XML.util.attrAsAttr(dom, "enctype"),
						'><div class="Title">', XML.util.getTitle(dom), '</div>']);
	
	for(var i = 0; i < sections.length; i++)
		html = html.concat(formxslt.parseSection(sections[i]));
	
	var actions = dom.find('actions');
	html = html.concat(XML.util.xmlToString(actions.get(0)));
	html = html.concat(['</form>']);
	
	return html;
}

formxslt.parseSection = function(dom, options) {
		
	var dom = $(dom);
	var html = [];
	var sectionTitle = XML.util.getTitle(dom);
	html = html.concat(['<div class="SubTitle">', XML.util.getTitle(dom), '</div>']);
	html.push('<table cellpadding="0" cellspacing="0">');
	var fields = dom.children('fields').children();
	
	var totalSpan = XML.util.attr(dom, "colspan");
	var currentSpan = 0;
	
	for(var i = 0; i < fields.length; i++) {
		
		if(currentSpan == 0)
			html.push('<tr>');
		
		currentSpan += parseInt(XML.util.attr(fields[i], "colspan"));
		html = html.concat(formxslt.prototype.parsefield(fields[i]);
		
		if(currentSpan >= totalSpan) {
			currentSpan = 0;
			html.push('</tr>');
		}
	}
	html.push('</table>');
	return html;
}


formxslt.parseField = function(field) {
	
	var html = [];
	var field = $(field);
	var fieldDom = [];
	
	switch(XML.util.attr(field, "type")) {
		
		case "email":
		case "string":
			fieldDom = fieldDom.concat(['<input class="el" type="text"', XML.util.attrAsAttr(field, "name"), 'value="', fieldValue[0], '" />']);
		break;
		
		case "text":
			fieldDom = fieldDom.concat(['<textarea class="el"', XML.util.attrAsAttr(field, "name"), '>', fieldValue[0], '</textarea>']);
		break;
		
		case "captcha":
			fieldDom = fieldDom.concat(['<img class="captcha" /><input type="text" ', XML.util.attrAsAttr(field, "name"), '><a class="reloadCaptcha">Nouvelle image</a>']);
		break;
	}
	
	html = html.concat(['<td ', XML.util.attrAsAttr(field, "colspan"), '>', XML.util.getLabel(field), '</td><td>', fieldDom.join("")]);
	var validations = [];
	field.children('validations').children().each(function() {
		validations = validations.concat(["<validation ", XML.util.attrAsData(this, "type"), XML.util.attrAsData(this, "value"), '>', XML.util.getLabel(this), '</validation>']);
	});
	
	html = html.concat(validations);
	html.push('</td>');
	
	return html;
}

/*
 * XML functions
 */

XML = new Object();
XML.util = new Object();
XML.lang = 'fr';

XML.util.attr = function(dom, name) {
	return (name = $(dom).attr(name)) != undefined ? name : "";
} 

XML.util.attrAsData = function(dom, name, forcedname) {
	var val = XML.util.attrAsAttr(fom, name, forcedname);
	if(val == "")
		return "";
	return ['data-', val].join("");
}

XML.util.attrAsAttr = function(dom, name, forcedname) {
	var attrname = name;
	if(forcedname)
		attrname = forcedname;
	var val = XML.util.attr(dom, name);
	if(val == "")
		return "";
		
	return [attrname, '="', val, '" '].join("");
}

XML.util.getTitle = function(dom) {
	return XML.util.getLabel($(dom).children('title'));
}

XML.util.getLabel = function(dom) {
	return $(dom).children('label').children(XML.lang).text();
}

XML.util.xmlToString = function(dom) {
	
	var serialized;
	try {
		serializer = new XMLSerializer();
		serialized = serializer.serializeToString(dom);
	} catch (e) {
		serialized = dom.xml;
	}
	return serialized;
}