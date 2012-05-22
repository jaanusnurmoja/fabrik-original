/**
 * @author Robert
 */

var Autofill = new Class({
	
	Implements: [Events, Options],
	
	options: {
		'observe': '',
		'trigger': '',
		cnn: 0,
		table: 0,
		map: '',
		editOrig: false,
		fillOnLoad: false,
		confirm: true
	},
	
	initialize: function (options) {
		this.setOptions(options);
		this.attached = [];
		/*if (Browser.ie) {
			this.setUp(Fabrik.blocks['form_' + this.options.formid]);
		} else {
			Fabrik.addEvent('fabrik.form.elements.added', function (form) {
				this.setUp(form);	
			}.bind(this));
		}*/
		
		Fabrik.addEvent('fabrik.form.elements.added', function (form) {
			this.setUp(form);	
		}.bind(this));
		
		Fabrik.addEvent('fabrik.form.element.added', function (form, elId, oEl) {
			if (!this.element) {
				//if we are on the form load then this.element not set so return
				return;
			}
			// a group has been duplicated
			if (oEl.strElement === this.element.strElement) {
				// the element is a clone of our observable element
				this.element = false;
				this.setUp(form);
			}
		}.bind(this));
	},
	
	/**
	 * get the observable element
	 * @return element object
	 */
	getElement: function () {
		var testE = false;
		var e = this.form.formElements.get(this.options.observe);
		//if its a joined element
		if (!e) {
			var k = Object.keys(this.form.formElements);
			var ii = k.each(function (i) {
				if (i.contains(this.options.observe)) {
					testE = this.form.formElements.get(i);
					if (!this.attached.contains(testE.options.element)) {
						//we havent previously observed this element, add it to this.attached
						// so that in the future we don't re-add it.
						this.attached.push(testE.options.element);
						e = testE;
					}
				}
			}.bind(this));
		}
		return e;
	},
	
	setUp: function (form) {
		try {
			this.form = form;
		} catch (err) {
			//form_x not found (detailed view perhaps)
			return;
		}
		var e = this.getElement();
		if (!e) {
			return false;
		}
		this.element = e;
		var evnt = this.lookUp.bind(this);
		if (this.options.trigger === '') {
			if (!this.element) {
				fconsole('autofill - couldnt find element to observe');
			} else {
				var elEvnt = this.element.getBlurEvent();
				this.form.dispatchEvent('', this.element.options.element, elEvnt, evnt);
			}
		} else {
			this.form.dispatchEvent('', this.options.trigger, 'click', evnt);
		}
		if (this.options.fillOnLoad && form.options.rowid === '0') {
			var t = this.options.trigger === '' ? this.element.strElement : this.options.trigger;
			this.form.dispatchEvent('', t, 'load', evnt);
		}
	},
	
	// perform ajax lookup when the observer element is blurred
	
	lookUp: function () {
		if (this.options.confirm === true) {
			if (!confirm(Joomla.JText._('PLG_FORM_AUTOFILL_DO_UPDATE'))) {
				return;
			}
		}
		Fabrik.loader.start('form_' + this.options.formid, Joomla.JText._('PLG_FORM_AUTOFILL_SEARCHING'));
		
		var v = this.element.getValue();
		var formid = this.options.formid;
		var observe = this.options.observe;
		
		var myAjax = new Request.JSON({ 
			'data': {
				'option': 'com_fabrik',
				'format': 'raw',
				'task': 'plugin.pluginAjax',
				'plugin': 'autofill',
				'method': 'ajax_getAutoFill',
				'g': 'form',
				'v': v, 
				'formid': formid,
				'observe': observe,
				'cnn': this.options.cnn,
				'table': this.options.table,
				'map': this.options.map
			},
			onCancel: function () {
				Fabrik.loader.stop('form_' + this.options.formid);
			}.bind(this),
			
			onError: function (text, error) {
				Fabrik.loader.stop('form_' + this.options.formid);
				fconsole(text + ' ' + error);
			}.bind(this),
			onSuccess: function (json, responseText) {
				Fabrik.loader.stop('form_' + this.options.formid);
				this.updateForm(json);
			}.bind(this)
		}).send();
	},
	
	//update the form from the ajax request returned data
	updateForm: function (json) {
		var repeatNum = this.element.getRepeatNum();
		json = $H(json);
		if (json.length === 0) {
			alert(Joomla.JText._('PLG_FORM_AUTOFILL_NORECORDS_FOUND'));
		}
		json.each(function (val, key) {
			var k2 = key.substr(key.length - 4, 4);
			if (k2 === '_raw') {
				key = key.replace('_raw', '');
				if (repeatNum) {
					key += '_' + repeatNum;
				}
				var el = this.form.formElements.get(key);
				if (typeOf(el) !== 'null') {
					el.update(val);
				}
			}
		}.bind(this));
		if (this.options.editOrig === true) {
			this.form.getForm().getElement('input[name=rowid]').value = json.__pk_val;
		}
	}
	
});