var FbCheckBox = new Class({
	Extends: FbElementList,
	initialize: function (element, options) {
		this.plugin = 'fabrikcheckbox';
		this.parent(element, options);
		this._getSubElements();
		this.watchAdd();
	},
	
	watchAddToggle : function () {
		var c = this.getContainer();
		var d = c.getElement('div.addoption');
		var a = c.getElement('.toggle-addoption');
		if (this.mySlider) {
			//copied in repeating group so need to remove old slider html first
			var clone = d.clone();
			var fe = c.getElement('.fabrikElement');
			d.getParent().destroy();
			fe.adopt(clone);
			d = c.getElement('div.addoption');
			d.setStyle('margin', 0);
		}
		this.mySlider = new Fx.Slide(d, {
			duration : 500
		});
		this.mySlider.hide();
		a.addEvent('click', function (e) {
			e.stop();
			this.mySlider.toggle();
		}.bind(this));
	},
	
	getValue: function () {
		if (!this.options.editable) {
			return this.options.value;
		}
		var ret = [];
		if (!this.options.editable) {
			return this.options.value;
		}
		this._getSubElements().each(function (el) {
			if (el.checked) {
				ret.push(el.get('value'));
			}
		});
		return ret;
	},
	
	numChecked: function () {
		return this._getSubElements().filter(function (c) {
			return c.checked;
		}).length;
	},
	
	update: function (val) {
		if (typeOf(val) === 'string') {
			//val = val.split(this.options.splitter);
			val = val === '' ? [] : JSON.decode(val);
		}
		if (!this.options.editable) {
			this.element.innerHTML = '';
			if (val === '') {
				return;
			}
			var h = $H(this.options.data);
			val.each(function (v) {
				this.element.innerHTML += h.get(v) + "<br />";	
			}.bind(this));
			return;
		}
		this._getSubElements();
		this.subElements.each(function (el) {
			var chx = false;
			val.each(function (v) {
				if (v === el.value) {
					chx = true;
				}
			}.bind(this));
			el.checked = chx;
		}.bind(this));
	},
	
	cloned: function () {
		if (this.options.allowadd === true && this.options.editable !== false) {
			this.watchAddToggle();
			this.watchAdd();
		}
	}

});