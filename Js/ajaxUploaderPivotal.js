jQuery(document).ready(function(){
	
/**
 * image pasting into canvas
 * 
 * @param {string} canvas_id - canvas id
 * @param {boolean} autoresize - if canvas will be resized
 */
function CLIPBOARD_CLASS(canvas_id, autoresize) {
var _self = this;
var canvas = document.getElementById(canvas_id);
var ctxA = document.getElementById(canvas_id).getContext("2d");
var ctrl_pressed = false;
var command_pressed = false;
var reading_dom = false;
var text_top = 15;
var pasteCatcher;
var paste_mode;

// handlers
document.addEventListener('keydown', function (e) {
	_self.on_keyboard_action(e);
}, false); //firefox fix
document.addEventListener('keyup', function (e) {
	_self.on_keyboardup_action(e);
}, false); //firefox fix
document.addEventListener('paste', function (e) {
	_self.paste_auto(e);
}, false); //official paste handler

// constructor - prepare
this.init = function () {
	// if using auto
	if (window.Clipboard)
		return true;

	// create an observer instance
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			if (paste_mode == 'auto' || ctrl_pressed == false || mutation.type != 'childList')
				return true;

			// if paste handle failed - capture pasted object manually
			if(mutation.addedNodes.length == 1) {
				if (mutation.addedNodes[0].src != undefined) {
					// image
					_self.paste_createImage(mutation.addedNodes[0].src);
				}
				// register cleanup after some time.
				setTimeout(function () {
					// pasteCatcher.innerHTML = '';
					document.getElementById("paste_ff").innerHTML = '';
				}, 20);
			}
		});
	});
	var target = document.getElementById('paste_ff');
	var config = { attributes: true, childList: true, characterData: true };
	observer.observe(target, config);
}();
// default paste action
this.paste_auto = function (e) {
	paste_mode = '';
	document.getElementById("paste_ff").innerHTML = '';
	var plain_text_used = false;
	if (e.clipboardData) {
		var items = e.clipboardData.items;
		if (items) {
			paste_mode = 'auto';
			// access data directly
			for (var i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1) {
					// image
					var blob = items[i].getAsFile();
					var URLObj = window.URL || window.webkitURL;
					var source = URLObj.createObjectURL(blob);
					this.paste_createImage(source);
				}
			}
			e.preventDefault();
		}
		else {
			// wait for DOMSubtreeModified event
			// https://bugzilla.mozilla.org/show_bug.cgi?id=891247
		}
	}
};
// on keyboard press
this.on_keyboard_action = function (event) {
	k = event.keyCode;
	// ctrl
	if (k == 17 || event.metaKey || event.ctrlKey) {
		if (ctrl_pressed == false)
			ctrl_pressed = true;
	}
	// v
	if (k == 86) {
		if (document.activeElement != undefined && document.activeElement.type == 'text') {
			// let user paste into some input
			return false;
		}
		
		if (ctrl_pressed == true && !window.Clipboard)
			document.getElementById("paste_ff").focus();
	}
};
// on keybord release
this.on_keyboardup_action = function (event) {
	// ctrl
	if (event.ctrlKey == false && ctrl_pressed == true) {
		ctrl_pressed = false;
	}
	// command
	else if(event.metaKey == false && command_pressed == true){
		command_pressed = false;
		ctrl_pressed = false;
	}
};
// draw image
this.paste_createImage = function (source) {
	var pastedImage = new Image();
	pastedImage.onload = function () {
		if(autoresize == true){
			// resize
			canvas.width = pastedImage.width;
			canvas.height = pastedImage.height;
		}
		else{
			// clear canvas
			ctxA.clearRect(0, 0, canvas.width, canvas.height);
		}
		ctxA.drawImage(pastedImage, 0, 0);
	};
	pastedImage.src = source;
};
}


});