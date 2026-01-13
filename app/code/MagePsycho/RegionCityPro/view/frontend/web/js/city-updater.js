var _0xca54 = ["jquery", "mage/template", "underscore", "select2", "jquery/ui", "mage/validation", "Magento_Checkout/js/region-updater", "use strict", "mage.cityUpdater", "</option>", "regionListId", "options", "regionInputId", "currentCityOption", "currentCity", "cityTmpl", "cityTemplate", ":visible", "is", "val", "option:selected", "find", "change", "setOption", "target", "", "text", "cityInputId", "proxy", "on", "cityListId", "focusout", "length", "option", "children", "undefined", "countryListId", "label", "siblings", "parent", "div.field", "parents", "cityJson", "each", "option[value=\"", "\"]", "selected", "attr", "filter", "isCityRequired", "disabled", "removeAttr", "required-entry", "addClass", "required", "data-validate", "required-entry validate-select", "removeClass", "show", "hide", "for", "id", "prop", "defaultvalue", "defaultCityId", "remove", "isCountrySearchable", "100%", "isRegionSearchable", "data", "destroy", "isCitySearchable", "\\$&", "replace", "name", "code", "span", "isSelected", "append", "clearError", "function", "call", "form", "closest", "element", "validator", "compact", "apply", "validation", "[generated]", "mage-error", "widget", "cityUpdater", "mage"];
define([_0xca54[0], _0xca54[1], _0xca54[2], _0xca54[3], _0xca54[4], _0xca54[5], _0xca54[6]], function (_0x829ex1, _0x829ex2, _0x829ex3, _0x829ex4) {
  _0xca54[7];
  _0x829ex1[_0xca54[91]](_0xca54[8], {
    options: {
      cityTemplate: "<option value=\"<%- data.value %>\" <% if (data.isSelected) { %>selected=\"selected\"<% } %>><%- data.title %>" + _0xca54[9],
      isCityRequired: true,
      currentCity: null
    },
    _create: function () {
      var _0x829ex5 = _0x829ex1(this[_0xca54[11]][_0xca54[10]]);
      this[_0xca54[13]] = this[_0xca54[11]][_0xca54[14]];
      this[_0xca54[15]] = _0x829ex2(this[_0xca54[11]][_0xca54[16]]);
      if (_0x829ex1(_0x829ex5)[_0xca54[18]](_0xca54[17])) {
        this._updateCity(_0x829ex1(_0x829ex5)[_0xca54[21]](_0xca54[20])[_0xca54[19]]());
      } else {
        this._updateCity(null);
      }
      ;
      _0x829ex1(this[_0xca54[11]][_0xca54[30]])[_0xca54[29]](_0xca54[22], _0x829ex1[_0xca54[28]](function (_0x829ex7) {
        this[_0xca54[23]] = false;
        this[_0xca54[13]] = _0x829ex1(_0x829ex7[_0xca54[24]])[_0xca54[19]]();
        if (_0x829ex1(_0x829ex7[_0xca54[24]])[_0xca54[19]]() != _0xca54[25]) {
          _0x829ex1(this[_0xca54[11]][_0xca54[27]])[_0xca54[19]](_0x829ex1(_0x829ex7[_0xca54[24]])[_0xca54[21]](_0xca54[20])[_0xca54[26]]());
        }
      }, this));
      _0x829ex1(this[_0xca54[11]][_0xca54[27]])[_0xca54[29]](_0xca54[31], _0x829ex1[_0xca54[28]](function () {
        this[_0xca54[23]] = true;
      }, this));
      this._bindCountryElement();
      this._bindRegionElement();
    },
    _bindCountryElement: function () {
      this._bindCountrySelect2();
      _0x829ex1(this[_0xca54[11]][_0xca54[36]])[_0xca54[29]](_0xca54[22], _0x829ex1[_0xca54[28]](function (_0x829ex7) {
        if (_0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[34]](_0xca54[33])[_0xca54[32]] > 1) {
          this._bindRegionSelect2();
        } else {
          this._destroyRegionSelect2();
        }
        ;
        if (_0x829ex1(this[_0xca54[11]][_0xca54[10]]) !== _0xca54[35] && _0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[21]](_0xca54[20])[_0xca54[19]]() != _0xca54[25]) {
          this._updateCity(_0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[21]](_0xca54[20])[_0xca54[19]]());
        } else {
          this._updateCity(null);
        }
      }, this));
    },
    _bindRegionElement: function () {
      this._bindRegionSelect2();
      _0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[29]](_0xca54[22], _0x829ex1[_0xca54[28]](function (_0x829ex7) {
        this._updateCity(_0x829ex1(_0x829ex7[_0xca54[24]])[_0xca54[19]]());
      }, this));
      _0x829ex1(this[_0xca54[11]][_0xca54[12]])[_0xca54[29]](_0xca54[31], _0x829ex1[_0xca54[28]](function () {
        this._updateCity(null);
      }, this));
    },
    _updateCity: function (_0x829ex8) {
      var _0x829ex9 = _0x829ex1(this[_0xca54[11]][_0xca54[30]]);
      var _0x829exa = _0x829ex1(this[_0xca54[11]][_0xca54[27]]);
      var _0x829exb = _0x829ex9[_0xca54[39]]()[_0xca54[38]](_0xca54[37]);
      var _0x829exc = _0x829ex9[_0xca54[41]](_0xca54[40]);
      this._clearError();
      if (_0x829ex8 && this[_0xca54[11]][_0xca54[42]][_0x829ex8]) {
        this._removeSelectOptions(_0x829ex9);
        _0x829ex1[_0xca54[43]](this[_0xca54[11]][_0xca54[42]][_0x829ex8], _0x829ex1[_0xca54[28]](function (_0x829exd, _0x829exe) {
          this._renderSelectOption(_0x829ex9, _0x829exd, _0x829exe);
        }, this));
        if (this[_0xca54[13]] && _0x829ex9[_0xca54[21]](_0xca54[44] + this[_0xca54[13]] + _0xca54[45])[_0xca54[32]] > 0) {
          _0x829ex9[_0xca54[19]](this[_0xca54[13]]);
        }
        ;
        if (this[_0xca54[23]]) {
          _0x829ex9[_0xca54[21]](_0xca54[33])[_0xca54[48]](function () {
            return this[_0xca54[26]] === _0x829exa[_0xca54[19]]();
          })[_0xca54[47]](_0xca54[46], true);
        }
        ;
        if (this[_0xca54[11]][_0xca54[49]]) {
          _0x829ex9[_0xca54[53]](_0xca54[52])[_0xca54[51]](_0xca54[50]);
          _0x829exc[_0xca54[53]](_0xca54[54]);
        } else {
          _0x829ex9[_0xca54[57]](_0xca54[56])[_0xca54[51]](_0xca54[55]);
          _0x829exc[_0xca54[57]](_0xca54[54]);
        }
        ;
        _0x829ex9[_0xca54[58]]();
        _0x829exa[_0xca54[57]](_0xca54[52])[_0xca54[59]]();
        _0x829exb[_0xca54[47]](_0xca54[60], _0x829ex9[_0xca54[47]](_0xca54[61]));
        this._bindCitySelect2();
      } else {
        if (this[_0xca54[11]][_0xca54[49]]) {
          _0x829exa[_0xca54[53]](_0xca54[52])[_0xca54[51]](_0xca54[50]);
          _0x829exc[_0xca54[53]](_0xca54[54]);
        } else {
          _0x829exc[_0xca54[57]](_0xca54[54]);
          _0x829exa[_0xca54[57]](_0xca54[52]);
        }
        ;
        _0x829ex9[_0xca54[57]](_0xca54[52])[_0xca54[62]](_0xca54[50], _0xca54[50])[_0xca54[59]]();
        _0x829exa[_0xca54[58]]();
        _0x829exb[_0xca54[47]](_0xca54[60], _0x829exa[_0xca54[47]](_0xca54[61]));
        this._destroyCitySelect2();
      }
      ;
      _0x829ex9[_0xca54[47]](_0xca54[63], this[_0xca54[11]][_0xca54[64]]);
    },
    _removeSelectOptions: function (_0x829exf) {
      _0x829exf[_0xca54[21]](_0xca54[33])[_0xca54[43]](function (_0x829ex10) {
        if (_0x829ex10) {
          _0x829ex1(this)[_0xca54[65]]();
        }
      });
    },
    _bindCountrySelect2: function () {
      if (this[_0xca54[11]][_0xca54[66]]) {
        _0x829ex1(this[_0xca54[11]][_0xca54[36]])[_0xca54[3]]({
          width: _0xca54[67]
        });
      }
    },
    _bindRegionSelect2: function () {
      if (this[_0xca54[11]][_0xca54[68]]) {
        _0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[3]]({
          width: _0xca54[67]
        });
      }
    },
    _destroyRegionSelect2: function () {
      if (this[_0xca54[11]][_0xca54[68]]) {
        if (_0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[69]](_0xca54[3])) {
          _0x829ex1(this[_0xca54[11]][_0xca54[10]])[_0xca54[69]](_0xca54[3])[_0xca54[70]]();
        }
      }
    },
    _bindCitySelect2: function () {
      if (this[_0xca54[11]][_0xca54[71]]) {
        _0x829ex1(this[_0xca54[11]][_0xca54[30]])[_0xca54[3]]({
          width: _0xca54[67]
        });
      }
    },
    _destroyCitySelect2: function () {
      if (this[_0xca54[11]][_0xca54[71]]) {
        if (_0x829ex1(this[_0xca54[11]][_0xca54[30]])[_0xca54[69]](_0xca54[3])) {
          _0x829ex1(this[_0xca54[11]][_0xca54[30]])[_0xca54[69]](_0xca54[3])[_0xca54[70]]();
        }
      }
    },
    _renderSelectOption: function (_0x829exf, _0x829exd, _0x829exe) {
      _0x829exf[_0xca54[78]](_0x829ex1[_0xca54[28]](function () {
        var _0x829ex11 = _0x829exe[_0xca54[74]][_0xca54[73]](/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, _0xca54[72]);
        var _0x829ex12;
        var _0x829ex13;
        if (_0x829exe[_0xca54[75]] && _0x829ex1(_0x829ex11)[_0xca54[18]](_0xca54[76])) {
          _0x829exd = _0x829exe[_0xca54[75]];
          _0x829exe[_0xca54[74]] = _0x829ex1(_0x829ex11)[_0xca54[26]]();
        }
        ;
        _0x829ex12 = {
          value: _0x829exd,
          title: _0x829exe[_0xca54[74]],
          isSelected: false
        };
        if (this[_0xca54[11]][_0xca54[64]] === _0x829exd) {
          _0x829ex12[_0xca54[77]] = true;
        }
        ;
        _0x829ex13 = this[_0xca54[15]]({
          data: _0x829ex12
        });
        return _0x829ex1(_0x829ex13);
      }, this));
    },
    _clearError: function () {
      var _0x829ex14 = [_0xca54[79], this[_0xca54[11]][_0xca54[30]], this[_0xca54[11]][_0xca54[27]]];
      if (this[_0xca54[11]][_0xca54[79]] && typeof this[_0xca54[11]][_0xca54[79]] === _0xca54[80]) {
        this[_0xca54[11]][_0xca54[79]][_0xca54[81]](this);
      } else {
        if (!this[_0xca54[11]][_0xca54[82]]) {
          this[_0xca54[11]][_0xca54[82]] = this[_0xca54[84]][_0xca54[83]](_0xca54[82])[_0xca54[32]] ? _0x829ex1(this[_0xca54[84]][_0xca54[83]](_0xca54[82])[0]) : null;
        }
        ;
        this[_0xca54[11]][_0xca54[82]] = _0x829ex1(this[_0xca54[11]][_0xca54[82]]);
        if (this[_0xca54[11]][_0xca54[82]] && this[_0xca54[11]][_0xca54[82]][_0xca54[69]](_0xca54[85])) {
          this[_0xca54[11]][_0xca54[82]][_0xca54[88]][_0xca54[87]](this[_0xca54[11]][_0xca54[82]], _0x829ex3[_0xca54[86]](_0x829ex14));
        }
        _0x829ex1(this[_0xca54[11]][_0xca54[27]])[_0xca54[57]](_0xca54[90])[_0xca54[39]]()[_0xca54[21]](_0xca54[89])[_0xca54[65]]();
        _0x829ex1(this[_0xca54[11]][_0xca54[30]])[_0xca54[57]](_0xca54[90])[_0xca54[39]]()[_0xca54[21]](_0xca54[89])[_0xca54[65]]();
      }
    }
  });
  return _0x829ex1[_0xca54[93]][_0xca54[92]];
});
