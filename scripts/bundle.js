(function () {
  'use strict';

  if (typeof vendor === 'undefined' || typeof divi === 'undefined') {
    return;
  }

  var React = vendor.React;
  var hooks = vendor.wp && vendor.wp.hooks;
  var moduleApi = divi.module;
  var moduleLibrary = divi.moduleLibrary;

  if (!React || !hooks || !moduleApi || !moduleLibrary) {
    return;
  }

  var metadata = {"name":"reno-plus/service-breadcrumbs","d4Shortcode":"","title":"Service Breadcrumbs","titles":"Service Breadcrumbs","moduleIcon":"reno-plus/module-service-breadcrumbs","moduleClassName":"rp_d5_breadcrumbs","moduleOrderClassName":"rp_d5_breadcrumbs","category":"module","attributes":{"module":{"type":"object","selector":"{{selector}}","default":{"meta":{"adminLabel":{"desktop":{"value":"Service Breadcrumbs"}}}},"settings":{"meta":{"adminLabel":{}},"advanced":{"htmlAttributes":{}},"decoration":{"attributes":{},"background":{},"sizing":{},"spacing":{},"border":{},"boxShadow":{},"filters":{},"transform":{},"animation":{},"overflow":{},"disabledOn":{},"transition":{},"position":{},"zIndex":{},"scroll":{},"sticky":{}}}},"breadcrumb":{"type":"object","default":{"innerContent":{"desktop":{"value":{"homeLabel":"Home","archiveLabel":"Services","separator":"/","ariaLabel":"Breadcrumb","postType":"services","taxonomy":"service-category","showHome":"on","showArchive":"on","showCurrent":"on","schema":"on"}}}},"settings":{"innerContent":{"groupType":"group-items","items":{"homeLabel":{"groupSlug":"contentBreadcrumbs","priority":10,"render":true,"subName":"homeLabel","label":"Home Label","description":"The label used for the site home page.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"archiveLabel":{"groupSlug":"contentBreadcrumbs","priority":20,"render":true,"subName":"archiveLabel","label":"Services Archive Label","description":"The label for the services post type archive breadcrumb.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"separator":{"groupSlug":"contentBreadcrumbs","priority":30,"render":true,"subName":"separator","label":"Separator","description":"Text displayed between breadcrumb items.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"ariaLabel":{"groupSlug":"contentBreadcrumbs","priority":40,"render":true,"subName":"ariaLabel","label":"Accessibility Label","description":"The aria-label applied to the breadcrumb navigation landmark.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"showHome":{"groupSlug":"contentBreadcrumbs","priority":50,"render":true,"subName":"showHome","label":"Show Home","description":"Include the home page in the trail.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"showArchive":{"groupSlug":"contentBreadcrumbs","priority":60,"render":true,"subName":"showArchive","label":"Show Services Archive","description":"Include the services archive before service categories.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"showCurrent":{"groupSlug":"contentBreadcrumbs","priority":70,"render":true,"subName":"showCurrent","label":"Show Current Item","description":"Include the current post, term, or archive as the final item.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"schema":{"groupSlug":"contentBreadcrumbs","priority":80,"render":true,"subName":"schema","label":"Breadcrumb Schema","description":"Output Schema.org BreadcrumbList microdata.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"postType":{"groupSlug":"contentDataSource","priority":10,"render":true,"subName":"postType","label":"Service Post Type Slug","description":"Defaults to services. Change only if the CPT slug changes.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"taxonomy":{"groupSlug":"contentDataSource","priority":20,"render":true,"subName":"taxonomy","label":"Service Taxonomy Slug","description":"Defaults to service-category. Change only if the taxonomy slug changes.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}}}}}},"linkText":{"type":"object","selector":"{{selector}} .rp-d5-breadcrumbs__link","settings":{"decoration":{"font":{"priority":10,"component":{"props":{"groupLabel":"Link Text","fieldLabel":"Link"}}}}}},"currentText":{"type":"object","selector":"{{selector}} .rp-d5-breadcrumbs__current","defaultPrintedStyle":{"decoration":{"font":{"font":{"desktop":{"value":{"weight":"600"}}}}}},"settings":{"decoration":{"font":{"priority":20,"component":{"props":{"groupLabel":"Current Item Text","fieldLabel":"Current Item"}}}}}},"separatorText":{"type":"object","selector":"{{selector}} .rp-d5-breadcrumbs__separator","settings":{"decoration":{"font":{"priority":30,"component":{"props":{"groupLabel":"Separator Text","fieldLabel":"Separator"}}}}}}},"customCssFields":{"nav":{"subName":"nav","label":"Breadcrumb Navigation","selectorSuffix":" .rp-d5-breadcrumbs__nav"},"list":{"subName":"list","label":"Breadcrumb List","selectorSuffix":" .rp-d5-breadcrumbs__list"},"item":{"subName":"item","label":"Breadcrumb Item","selectorSuffix":" .rp-d5-breadcrumbs__item"},"link":{"subName":"link","label":"Breadcrumb Link","selectorSuffix":" .rp-d5-breadcrumbs__link"},"current":{"subName":"current","label":"Current Item","selectorSuffix":" .rp-d5-breadcrumbs__current"},"separator":{"subName":"separator","label":"Separator","selectorSuffix":" .rp-d5-breadcrumbs__separator"}},"settings":{"content":"auto","design":"auto","advanced":"auto","groups":{"contentBreadcrumbs":{"panel":"content","priority":10,"groupName":"contentBreadcrumbs","multiElements":true,"component":{"name":"divi/composite","props":{"groupLabel":"Breadcrumbs"}}},"contentDataSource":{"panel":"content","priority":20,"groupName":"contentDataSource","multiElements":true,"component":{"name":"divi/composite","props":{"groupLabel":"Data Source"}}}}}};
  var defaultAttrs = {"module":{"meta":{"adminLabel":{"desktop":{"value":"Service Breadcrumbs"}}}},"breadcrumb":{"innerContent":{"desktop":{"value":{"homeLabel":"Home","archiveLabel":"Services","separator":"/","ariaLabel":"Breadcrumb","postType":"services","taxonomy":"service-category","showHome":"on","showArchive":"on","showCurrent":"on","schema":"on"}}}}};
  var defaultPrintedStyleAttrs = {"currentText":{"decoration":{"font":{"font":{"desktop":{"value":{"weight":"600"}}}}}}};
  var ModuleContainer = moduleApi.ModuleContainer;
  var StyleContainer = moduleApi.StyleContainer;
  var CssStyle = moduleApi.CssStyle;

  var cssFields = {
    nav: { subName: 'nav', label: 'Breadcrumb Navigation', selectorSuffix: ' .rp-d5-breadcrumbs__nav' },
    list: { subName: 'list', label: 'Breadcrumb List', selectorSuffix: ' .rp-d5-breadcrumbs__list' },
    item: { subName: 'item', label: 'Breadcrumb Item', selectorSuffix: ' .rp-d5-breadcrumbs__item' },
    link: { subName: 'link', label: 'Breadcrumb Link', selectorSuffix: ' .rp-d5-breadcrumbs__link' },
    current: { subName: 'current', label: 'Current Item', selectorSuffix: ' .rp-d5-breadcrumbs__current' },
    separator: { subName: 'separator', label: 'Separator', selectorSuffix: ' .rp-d5-breadcrumbs__separator' }
  };

  function isOn(value) {
    return ['on', 'yes', 'true', '1'].indexOf(String(value)) !== -1;
  }

  function getConfig(attrs) {
    var value = attrs && attrs.breadcrumb && attrs.breadcrumb.innerContent &&
      attrs.breadcrumb.innerContent.desktop && attrs.breadcrumb.innerContent.desktop.value;

    value = value || {};

    return {
      homeLabel: value.homeLabel || 'Home',
      archiveLabel: value.archiveLabel || 'Services',
      separator: typeof value.separator === 'string' ? value.separator : '/',
      ariaLabel: value.ariaLabel || 'Breadcrumb',
      showHome: typeof value.showHome === 'undefined' ? 'on' : value.showHome,
      showArchive: typeof value.showArchive === 'undefined' ? 'on' : value.showArchive,
      showCurrent: typeof value.showCurrent === 'undefined' ? 'on' : value.showCurrent,
      schema: typeof value.schema === 'undefined' ? 'on' : value.schema
    };
  }

  function moduleClassnames(args) {
    if (args && args.classnamesInstance) {
      args.classnamesInstance.add('rp-d5-breadcrumbs--inline');
      if (isOn(getConfig(args.attrs || {}).schema)) {
        args.classnamesInstance.add('rp-d5-breadcrumbs--schema');
      }
    }
  }

  function ModuleStyles(props) {
    var attrs = props.attrs || {};
    var elements = props.elements;
    var settings = props.settings || {};
    var children = [];

    if (elements && elements.style) {
      children.push(elements.style({
        attrName: 'module',
        styleProps: {
          disabledOn: {
            disabledModuleVisibility: settings.disabledModuleVisibility
          }
        }
      }));
      children.push(elements.style({ attrName: 'linkText' }));
      children.push(elements.style({ attrName: 'currentText' }));
      children.push(elements.style({ attrName: 'separatorText' }));
    }

    if (CssStyle) {
      children.push(React.createElement(CssStyle, {
        key: 'custom-css',
        selector: props.orderClass,
        attr: attrs.css,
        cssFields: cssFields
      }));
    }

    return React.createElement.apply(React, [StyleContainer, {
      mode: props.mode,
      state: props.state,
      noStyleTag: props.noStyleTag
    }].concat(children));
  }

  function preventNavigation(event) {
    if (event && event.preventDefault) {
      event.preventDefault();
    }
  }

  function renderPreview(config) {
    var items = [];

    if (isOn(config.showHome)) {
      items.push({ label: config.homeLabel, url: '#' });
    }
    if (isOn(config.showArchive)) {
      items.push({ label: config.archiveLabel, url: '#' });
    }

    items.push({ label: 'Residential', url: '#' });
    items.push({ label: 'New Construction', url: '#' });

    if (isOn(config.showCurrent)) {
      items.push({ label: 'Current Service', current: true });
    }

    var nodes = [];
    items.forEach(function (item, index) {
      var isLast = index === items.length - 1;
      var itemChild;

      if (!item.current) {
        itemChild = React.createElement(
          'a',
          { className: 'rp-d5-breadcrumbs__link', href: '#', onClick: preventNavigation },
          item.label
        );
      } else {
        itemChild = React.createElement(
          'span',
          { className: 'rp-d5-breadcrumbs__current', 'aria-current': 'page' },
          item.label
        );
      }

      nodes.push(React.createElement(
        'li',
        { className: 'rp-d5-breadcrumbs__item', key: 'item-' + index },
        itemChild
      ));

      if (!isLast) {
        nodes.push(React.createElement(
          'li',
          { className: 'rp-d5-breadcrumbs__separator', 'aria-hidden': 'true', key: 'separator-' + index },
          config.separator
        ));
      }
    });

    return React.createElement(
      'nav',
      { className: 'rp-d5-breadcrumbs__nav', 'aria-label': config.ariaLabel },
      React.createElement.apply(React, ['ol', { className: 'rp-d5-breadcrumbs__list' }].concat(nodes))
    );
  }

  function BreadcrumbsEdit(props) {
    var children = [];
    var elements = props.elements;

    if (elements && elements.styleComponents) {
      children.push(elements.styleComponents({ attrName: 'module' }));
    }

    children.push(renderPreview(getConfig(props.attrs || {})));

    return React.createElement.apply(React, [ModuleContainer, {
      attrs: props.attrs,
      elements: props.elements,
      id: props.id,
      name: props.name,
      stylesComponent: ModuleStyles,
      classnamesFunction: moduleClassnames
    }].concat(children));
  }

  var serviceBreadcrumbsIcon = {
    name: 'reno-plus/module-service-breadcrumbs',
    viewBox: '0 0 24 24',
    component: function () {
      return React.createElement('path', {
        d: 'M3 4h5v5H3V4Zm7 1.5h5v2h-5v-2ZM17 4l4 2.5L17 9V4ZM3 15h5v5H3v-5Zm7 1.5h5v2h-5v-2Zm7-1.5 4 2.5L17 20v-5Z'
      });
    }
  };

  hooks.addFilter('divi.iconLibrary.icon.map', 'renoPlus.serviceBreadcrumbs', function (icons) {
    var nextIcons = Object.assign({}, icons);
    nextIcons[serviceBreadcrumbsIcon.name] = serviceBreadcrumbsIcon;
    return nextIcons;
  });

  hooks.addAction(
    'divi.moduleLibrary.registerModuleLibraryStore.after',
    'renoPlus.serviceBreadcrumbs',
    function () {
      moduleLibrary.registerModule(metadata, {
        defaultAttrs: defaultAttrs,
        defaultPrintedStyleAttrs: defaultPrintedStyleAttrs,
        renderers: {
          edit: BreadcrumbsEdit
        }
      });
    }
  );
}());
