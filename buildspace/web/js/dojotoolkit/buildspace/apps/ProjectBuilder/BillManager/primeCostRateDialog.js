define('buildspace/apps/ProjectBuilder/BillManager/primeCostRateDialog', [
  'dojo/_base/declare',
  'dojo/_base/lang',
  'dojo/when',
  'dojo/html',
  'dojo/dom',
  'dojo/keys',
  'dojo/dom-style',
  'dijit/form/Form',
  'dijit/form/CurrencyTextBox',
  'dijit/form/NumberTextBox',
  'dijit/_WidgetBase',
  'dijit/_OnDijitClickMixin',
  'dijit/_TemplatedMixin',
  'dijit/_WidgetsInTemplateMixin',
  'dojo/text!./templates/primeCostRateForm.html',
  'dojo/currency',
  'dojo/number',
  'dojo/i18n!buildspace/nls/ProjectBuilder',
], function (
  declare,
  lang,
  when,
  html,
  dom,
  keys,
  domStyle,
  Form,
  CurrencyTextBox,
  NumberTextBox,
  _WidgetBase,
  _OnDijitClickMixin,
  _TemplatedMixin,
  _WidgetsInTemplateMixin,
  template,
  currency,
  number,
  nls
) {
  var PrimeCostRateForm = declare(
    'buildspace.apps.ProjectBuilder.BillManager.PrimeCostRateForm',
    [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin],
    {
      templateString: template,
      baseClass: 'buildspace-form',
      style: 'outline:none;',
      itemObj: null,
      nls: nls,
      billGridStore: null,
      elementGridStore: null,
      region: 'center',
      disableEditingMode: false,
      constructor: function (args) {
        this.inherited(arguments);
        this.currencyAbbreviation = buildspace.billCurrencyAbbreviation
          ? buildspace.billCurrencyAbbreviation
          : buildspace.currencyAbbreviation;
      },
      postCreate: function () {
        this.inherited(arguments);
        var self = this;
        dojo.connect(this.supplyRateNode, 'onKeyUp', function () {
          self.updateTotal();
        });
        dojo.connect(this.wastagePercentageNode, 'onKeyUp', function () {
          self.updateTotal();
        });
        dojo.connect(this.labourForInstallationNode, 'onKeyUp', function () {
          self.updateTotal();
        });
        dojo.connect(this.otherCostNode, 'onKeyUp', function () {
          self.updateTotal();
        });
        dojo.connect(this.profitPercentageNode, 'onKeyUp', function () {
          self.updateTotal();
        });

        // disable or enable inputs
        this.supplyRateNode.set('readOnly', this.disableEditingMode);
        this.wastagePercentageNode.set('readOnly', this.disableEditingMode);
        this.labourForInstallationNode.set('readOnly', this.disableEditingMode);
        this.otherCostNode.set('readOnly', this.disableEditingMode);
        this.profitPercentageNode.set('readOnly', this.disableEditingMode);
      },
      startup: function () {
        this.inherited(arguments);
        var self = this;
        //setting up form values before we start our form
        var pb = buildspace.dialog.indeterminateProgressBar({
          title: nls.pleaseWait + '...',
        });
        pb.show();
        var xhrArgs = {
          url: 'billManager/primeCostRateForm',
          handleAs: 'json',
          content: { id: this.itemObj.id },
          load: function (data) {
            self.primeCostRateForm.setFormValues(data);
            pb.hide();
          },
          error: function (error) {
            //something is wrong somewhere
            pb.hide();
          },
        };

        dojo.xhrGet(xhrArgs);
      },
      submit: function (dialog) {
        var pb = buildspace.dialog.indeterminateProgressBar({
          title: nls.savingData + '...',
        });
        var self = this,
          billGridStore = this.billGridStore,
          values = dojo.formToObject(self.primeCostRateForm.id),
          xhrArgs = {
            url: 'billManager/primeCostRateUpdate',
            content: values,
            handleAs: 'json',
            load: function (resp) {
              pb.hide();
              var data = resp.data;
              if (resp.success) {
                billGridStore.fetchItemByIdentity({
                  identity: self.itemObj.id,
                  onItem: function (item) {
                    for (var property in data.item) {
                      if (
                        item.hasOwnProperty(property) &&
                        property != billGridStore._getIdentifierAttribute()
                      ) {
                        billGridStore.setValue(
                          item,
                          property,
                          data.item[property]
                        );
                      }
                    }
                    billGridStore.save();
                  },
                });

                dojo.forEach(data.affected_nodes, function (node) {
                  billGridStore.fetchItemByIdentity({
                    identity: node.id,
                    onItem: function (affectedItem) {
                      for (var property in node) {
                        if (
                          affectedItem.hasOwnProperty(property) &&
                          property != billGridStore._getIdentifierAttribute()
                        ) {
                          billGridStore.setValue(
                            affectedItem,
                            property,
                            node[property]
                          );
                        }
                      }
                      billGridStore.save();
                    },
                  });
                });
                dialog.hide();
                self.elementGridStore.grid.store.save();
                self.elementGridStore.grid.store.close();
                self.elementGridStore.grid.store.fetch();
                self.elementGridStore.grid.render();
              }
            },
            error: function (error) {
              pb.hide();
              dialog.hide();
            },
          };

        if (this.primeCostRateForm.validate()) {
          pb.show();
          dojo.xhrPost(xhrArgs);
        }
      },
      roundValue(value, precision) {
        var multiplier = Math.pow(10, precision || 0);
        return Math.round(value * multiplier) / multiplier;
      },
      updateTotal: function () {
        var supplyRate = isNaN(this.supplyRateNode.get('value'))
            ? 0
            : this.supplyRateNode.get('value'),
          wastagePercentage = isNaN(this.wastagePercentageNode.get('value'))
            ? 0
            : this.wastagePercentageNode.get('value'),
          wastageAmount =
            number.parse(supplyRate) * (number.parse(wastagePercentage) / 100),
          labourForInstallation = isNaN(
            this.labourForInstallationNode.get('value')
          )
            ? 0
            : this.labourForInstallationNode.get('value'),
          otherCost = isNaN(this.otherCostNode.get('value'))
            ? 0
            : this.otherCostNode.get('value'),
          profitPercentage = isNaN(this.profitPercentageNode.get('value'))
            ? 0
            : this.profitPercentageNode.get('value'),
          profitAmount =
            (number.parse(supplyRate) +
              wastageAmount +
              number.parse(labourForInstallation) +
              number.parse(otherCost)) *
            (number.parse(profitPercentage) / 100);

        profitAmount = this.roundValue(profitAmount, 2);

        this.wastageAmountNode.set('value', wastageAmount);
        this.profitAmountNode.set('value', profitAmount);

        var total =
          number.parse(supplyRate) +
          number.parse(wastageAmount) +
          number.parse(labourForInstallation) +
          number.parse(otherCost) +
          number.parse(profitAmount);

        this.totalNode.set('value', total);
      },
    }
  );

  return declare(
    'buildspace.apps.ProjectBuilder.BillManager.PrimeCostRateDialog',
    dijit.Dialog,
    {
      style: 'padding:0px;margin:0px;',
      title: nls.primeCostRate,
      itemObj: null,
      billGridStore: null,
      elementGridStore: null,
      currentBillLockedStatus: false,
      disableEditingMode: false,
      constructor: function (args) {
        if (args.currentBillLockedStatus) {
          this.disableEditingMode = true;
        }
      },
      buildRendering: function () {
        var content = this.createContent();
        content.startup();
        this.content = content;

        this.inherited(arguments);
      },
      postCreate: function () {
        domStyle.set(this.containerNode, {
          padding: '0px',
          margin: '0px',
        });
        this.closeButtonNode.style.display = 'none';
        this.inherited(arguments);
      },
      _onKey: function (e) {
        var key = e.keyCode;
        if (key == keys.ESCAPE) {
          dojo.stopEvent(e);
        }
      },
      onHide: function () {
        this.destroyRecursive();
      },
      createContent: function () {
        var borderContainer = new dijit.layout.BorderContainer({
          style: 'padding:0px;width:450px;height:300px;',
          gutters: false,
        });

        var form = new PrimeCostRateForm({
          itemObj: this.itemObj,
          billGridStore: this.billGridStore,
          elementGridStore: this.elementGridStore,
          disableEditingMode: this.disableEditingMode,
        });

        var toolbar = new dijit.Toolbar({
          region: 'top',
          style: 'outline:none!important;padding:2px;overflow:hidden;',
        });

        toolbar.addChild(
          new dijit.form.Button({
            label: nls.save,
            iconClass: 'icon-16-container icon-16-save',
            style: 'outline:none!important;',
            onClick: dojo.hitch(form, 'submit', this),
            disabled: this.disableEditingMode,
          })
        );

        toolbar.addChild(new dijit.ToolbarSeparator());

        toolbar.addChild(
          new dijit.form.Button({
            label: nls.close,
            iconClass: 'icon-16-container icon-16-close',
            style: 'outline:none!important;',
            onClick: dojo.hitch(this, 'hide'),
          })
        );

        borderContainer.addChild(toolbar);
        borderContainer.addChild(form);

        return borderContainer;
      },
    }
  );
});
