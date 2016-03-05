/**
 * JZED.js -- https://github.com/zedshaw/jzed
 * ( with mods )
 */

function $get(name, inChildren) {
	if(inChildren) {
		return inChildren.querySelector(name);
	} else {
		return document.querySelector(name);
	}
}

function $all(name, inChildren) {
	if(inChildren) {
		return inChildren.querySelectorAll(name);
	} else {
		return document.querySelectorAll(name);
	}
}

function $id(name, inChildren) {
	if(inChildren) {
		return inChildren.children.namedItem(name);
	} else {
		return document.getElementById(name);
	}
}

function $class(name) {
	return document.getElementsByClassName(name);
}

function $name(name) {
	return document.getElementsByTagName(name);
}

function $filter(nodes, fn) {
	return Array.prototype.filter.call(nodes, fn);
}

function $each(arr, cb) {
	if(arr == null) {
		return;
	} else if (Array.prototype.forEach && arr.forEach === Array.prototype.forEach) {
		arr.forEach(cb);
	} else {
		for(var i = 0; i < arr.length; i++) {
			(function() {
				cb(arr[i], i, arr);
			})();
		}
	}
}

function $next(node) {
	return node.nextElementSibling;
}

function $previous(node) {
	return node.previousElementSibling;
}

function $siblings(node) {
	return $filter(node.parentNode.children, function (child) { return child !== node; });
}

function $style() {
	$each(arguments, function(val) {
		if(val[0].length != null) {
			$each(val[0], function(element) {
				element.className = val[1];
			});
		} else {
			val[0].className = val[1];
		}
	});
}

function $toggle(node, className) {
	node.classList.toggle(className);
}

function $new(tag, id, html) {
	var new_tag = document.createElement(tag);
	new_tag.id = id;
	new_tag.innerHTML = html;
	return new_tag;
}

function $insert(node, tag) {
	node.parentNode.insertBefore( tag, node.nextSibling );
}

function $after(node, html) {
	if(node.tagName) {
		node.parentNode.insertBefore( html, node.nextSibling );
		return;
	}
	node.insertAdjacentHTML('afterend', html);
}

function $before(node, html) {
	if(node.tagName) {
		node.parentNode.insertBefore( node, html );
		return;
	}
	node.insertAdjacentHTML('beforebegin', html);
}

function $append(parent, child) {
	parent.appendChild(child);
}

function $prepend(parent, node) {
	parent.insertBefore(node, parent.firstChild);
}

function $remove(parent, child) {
	parent.removeChild(child);
}

function $clone(node) {
	return node.cloneNode(true);
}

function $contains(node, child) {
	return node !== child && node.contains(child);
}

function $has(node, selector) {
	return node.querySelector(selector) !== null;
}

function $empty(node) {
	return node.innerHTML == '';
}

function $style_of(node, ruleName) {
	return getComputedStyle(node)[ruleName]
}

function $attribute_of(node, name) {
	return node.getAttribute(name);
}

function $attribute(node, name, value) {
	return node.setAttribute(name, value);
}

function $attribute_del(node, name) {
	return node.removeAttribute(name);
}

function $html_of(node) {
	return node.innerHTML;
}

function $html(node, newhtml) {
	node.innerHTML = newhtml;
}

function $has_class(node, className) {
  return node.classList.contains(className);
}

function $outer_html(node) {
	return node.outerHTML;
}

function $replace_with(node, newhtml) {
	node.outerHTML = newhtml;
}

function $matches(node, selector) {
	if(node.matches) {
		return node.matches(selector);
	} else if(node.matchesSelector) {
		return node.matchesSelector(selector);
	} else if(node.mozMatchesSelector) {
		return node.mozMatchesSelector(selector);
	} else if(node.webkitMatchesSelector) {
		return node.webkitMatchesSelector(selector);
	} else if(node.oMatchesSelector) {
		return node.oMatchesSelector(selector);
	} else if(node.msMatchesSelector) {
		return node.msMatchesSelector(selector);
	} else {
		return null;
	}
}

function $offset(node) {
	return {left: node.offsetLeft, top: node.offsetTop};
}

function $offset_parent(node) {
	return node.offsetParent || node;
}

function $parent(node) {
	return node.parentNode;
}

function $text_of(node) {
	return node.textContent;
}

function $text(node, newtext) {
	node.textContent = newtext;
}

function $boot(cb) {
	document.addEventListener("DOMContentLoaded", cb);
}

function $off(node, eventName,  eventHandler) {
	node.removeEventListener(eventName, eventHandler);
}

function $on(node, eventName, eventHandler) {
	if (node.addEventListener) {
		node.addEventListener(eventName, eventHandler);
	} else if (node.attachEvent) {
		node.attachEvent('on'+name,handler); 
	}
}

function $http(settings, data) {
	var req = new XMLHttpRequest();
	req.withCredentials = settings.credentials;
	req.setRequestHeader("X_REQUESTED_WITH", "XMLHttpRequest");
	
	req.onreadystatechange = function() {
		if(req.readyState == 4) {
			if (req.status == 200){
				settings.good(req);
			} else {
				settings.error(req);
			}
		}
	}
	
	// Progress checkers for upload/download
	if(settings.up) {
		$on( req.upload, 'progress', settings.up);
	}
	if(settings.down) {
		$on( req, 'progress', settings.down);
	}
	
	req.timeout = settings.limit || 10000;
	if(settings.timeout) {
		req.ontimeout = function() { 
			settings.timeout(req);
		}
	}
	req.open(settings.method, settings.url, true);
	req.send(data || null);
}

function $cancel(e) {
	if (e.preventDefault) {
		e.preventDefault();
	}
	return false;
}

function $now() {
	return Date.now();
}

function $parse(htmlString) {
	var tmp = document.implementation.createHTMLDocument()
	tmp.body.innerHTML = htmlString
	return tmp.body.children;
}

function $json(jsonString) {
	return JSON.parse(jsonString);
}
