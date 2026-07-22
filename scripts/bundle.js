(() => {
  'use strict';

  const React = vendor.React;
  const { addAction, addFilter } = vendor.wp.hooks;
  const { useSelect } = divi.data;
  const {
    CssStyle,
    ModuleContainer,
    ModuleGroups,
    StyleContainer,
  } = divi.module;
  const { registerModule } = divi.moduleLibrary;
  const { loggedFetch } = divi.rest;

  const metadata = {"name":"burnt-arrow/cpt-breadcrumbs","d4Shortcode":"","title":"CPT Breadcrumbs","titles":"CPT Breadcrumbs","moduleIcon":"burnt-arrow/module-cpt-breadcrumbs","moduleClassName":"dcb_breadcrumbs","moduleOrderClassName":"dcb_breadcrumbs","category":"module","attributes":{"module":{"type":"object","selector":"{{selector}}","default":{"meta":{"adminLabel":{"desktop":{"value":"CPT Breadcrumbs"}}}},"settings":{"meta":{"adminLabel":{}},"advanced":{"htmlAttributes":{}},"decoration":{"attributes":{},"background":{},"sizing":{},"spacing":{},"border":{},"boxShadow":{},"filters":{},"transform":{},"animation":{},"overflow":{},"disabledOn":{},"transition":{},"position":{},"zIndex":{},"scroll":{},"sticky":{}}}},"breadcrumb":{"type":"object","default":{"innerContent":{"desktop":{"value":{"homeLabel":"Home","archiveLabel":"","separator":"/","ariaLabel":"Breadcrumb","postType":"auto","taxonomy":"auto","showHome":"on","showArchive":"on","showCurrent":"on","schema":"on"}}}},"settings":{"innerContent":{"groupType":"group-items","items":{"homeLabel":{"groupSlug":"contentBreadcrumbs","priority":10,"render":true,"subName":"homeLabel","label":"Home Label","description":"The label used for the site home page.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"archiveLabel":{"groupSlug":"contentBreadcrumbs","priority":20,"render":true,"subName":"archiveLabel","label":"Archive Label Override","description":"Leave blank to use the registered post type or posts-page label.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"separator":{"groupSlug":"contentBreadcrumbs","priority":30,"render":true,"subName":"separator","label":"Separator","description":"Text displayed between breadcrumb items.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"ariaLabel":{"groupSlug":"contentBreadcrumbs","priority":40,"render":true,"subName":"ariaLabel","label":"Accessibility Label","description":"The aria-label applied to the breadcrumb navigation landmark.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/text","type":"field"}},"showHome":{"groupSlug":"contentBreadcrumbs","priority":50,"render":true,"subName":"showHome","label":"Show Home","description":"Include the home page in the trail.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"showArchive":{"groupSlug":"contentBreadcrumbs","priority":60,"render":true,"subName":"showArchive","label":"Show Post Type Archive","description":"Include the post type archive before taxonomy terms when that archive exists.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"showCurrent":{"groupSlug":"contentBreadcrumbs","priority":70,"render":true,"subName":"showCurrent","label":"Show Current Item","description":"Include the current post, term, or archive as the final item.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"schema":{"groupSlug":"contentBreadcrumbs","priority":80,"render":true,"subName":"schema","label":"Breadcrumb Schema","description":"Output Schema.org BreadcrumbList microdata.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/toggle","type":"field"}},"postType":{"groupSlug":"contentDataSource","priority":10,"render":true,"subName":"postType","label":"Post Type","description":"Choose a public post type. Automatic uses the current queried post type.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/select","type":"field","props":{"options":{"auto":{"label":"Automatic (current request)"}}}}},"taxonomy":{"groupSlug":"contentDataSource","priority":20,"render":true,"subName":"taxonomy","label":"Taxonomy","description":"Choose a taxonomy associated with the selected post type. Hierarchical taxonomies include their complete parent chain.","features":{"sticky":false,"responsive":false,"hover":false,"dynamicContent":false},"component":{"name":"divi/select","type":"field","props":{"options":{"auto":{"label":"Automatic (best taxonomy for the post)"},"none":{"label":"None (omit taxonomy terms)"}}}}}}}}},"linkText":{"type":"object","selector":"{{selector}} .dcb-breadcrumbs__link","settings":{"decoration":{"font":{"priority":10,"component":{"props":{"groupLabel":"Link Text","fieldLabel":"Link"}}}}}},"currentText":{"type":"object","selector":"{{selector}} .dcb-breadcrumbs__current","defaultPrintedStyle":{"decoration":{"font":{"font":{"desktop":{"value":{"weight":"600"}}}}}},"settings":{"decoration":{"font":{"priority":20,"component":{"props":{"groupLabel":"Current Item Text","fieldLabel":"Current Item"}}}}}},"separatorText":{"type":"object","selector":"{{selector}} .dcb-breadcrumbs__separator","settings":{"decoration":{"font":{"priority":30,"component":{"props":{"groupLabel":"Separator Text","fieldLabel":"Separator"}}}}}}},"customCssFields":{"nav":{"subName":"nav","label":"Breadcrumb Navigation","selectorSuffix":" .dcb-breadcrumbs__nav"},"list":{"subName":"list","label":"Breadcrumb List","selectorSuffix":" .dcb-breadcrumbs__list"},"item":{"subName":"item","label":"Breadcrumb Item","selectorSuffix":" .dcb-breadcrumbs__item"},"link":{"subName":"link","label":"Breadcrumb Link","selectorSuffix":" .dcb-breadcrumbs__link"},"current":{"subName":"current","label":"Current Item","selectorSuffix":" .dcb-breadcrumbs__current"},"separator":{"subName":"separator","label":"Separator","selectorSuffix":" .dcb-breadcrumbs__separator"}},"settings":{"content":"auto","design":"auto","advanced":"auto","groups":{"contentBreadcrumbs":{"panel":"content","priority":10,"groupName":"contentBreadcrumbs","multiElements":true,"component":{"name":"divi/composite","props":{"groupLabel":"Breadcrumbs"}}},"contentDataSource":{"panel":"content","priority":20,"groupName":"contentDataSource","multiElements":true,"component":{"name":"divi/composite","props":{"groupLabel":"Data Source"}}}}}};

  const fallbackPostTypes = {
    auto: { label: 'Automatic (current request)' },
  };

  const fallbackTaxonomies = {
    auto: { label: 'Automatic (best taxonomy for the post)' },
    none: { label: 'None (omit taxonomy terms)' },
  };

  const defaultAttrs = {
    module: {
      meta: {
        adminLabel: {
          desktop: { value: 'CPT Breadcrumbs' },
        },
      },
    },
    breadcrumb: {
      innerContent: {
        desktop: {
          value: {
            homeLabel: 'Home',
            archiveLabel: '',
            separator: '/',
            ariaLabel: 'Breadcrumb',
            postType: 'auto',
            taxonomy: 'auto',
            showHome: 'on',
            showArchive: 'on',
            showCurrent: 'on',
            schema: 'on',
          },
        },
      },
    },
  };

  const defaultPrintedStyleAttrs = {
    currentText: {
      decoration: {
        font: {
          font: {
            desktop: {
              value: { weight: '600' },
            },
          },
        },
      },
    },
  };

  const cssFields = {
    nav: { subName: 'nav', label: 'Breadcrumb Navigation', selectorSuffix: ' .dcb-breadcrumbs__nav' },
    list: { subName: 'list', label: 'Breadcrumb List', selectorSuffix: ' .dcb-breadcrumbs__list' },
    item: { subName: 'item', label: 'Breadcrumb Item', selectorSuffix: ' .dcb-breadcrumbs__item' },
    link: { subName: 'link', label: 'Breadcrumb Link', selectorSuffix: ' .dcb-breadcrumbs__link' },
    current: { subName: 'current', label: 'Current Item', selectorSuffix: ' .dcb-breadcrumbs__current' },
    separator: { subName: 'separator', label: 'Separator', selectorSuffix: ' .dcb-breadcrumbs__separator' },
  };

  const isOn = value => ['on', 'yes', 'true', '1'].includes(String(value));

  const humanizeSlug = (value, fallback) => {
    const slug = String(value ?? '').trim();
    if (!slug || 'auto' === slug) {
      return fallback;
    }

    return slug
      .replace(/[-_]+/g, ' ')
      .replace(/\b\w/g, character => character.toUpperCase());
  };

  const getStoredSourceValue = (attribute, key, fallback) => {
    if (attribute?.getIn) {
      return String(attribute.getIn(['desktop', 'value', key]) ?? fallback);
    }

    return String(attribute?.desktop?.value?.[key] ?? fallback);
  };

  const setSelectOptions = (groupConfiguration, fieldKey, fieldSuffix, options) => {
    const fields = groupConfiguration?.contentDataSource?.component?.props?.fields;
    if (!fields) {
      return;
    }

    const resolvedKey = fields[fieldKey]
      ? fieldKey
      : Object.keys(fields).find(key => key.toLowerCase().endsWith(fieldSuffix.toLowerCase()));

    if (!resolvedKey) {
      return;
    }

    const field = fields[resolvedKey];
    field.component = field.component ?? {};
    field.component.props = field.component.props ?? {};
    field.component.props.options = options;
  };

  const SettingsContent = ({ groupConfiguration }) => {
    const [postTypes, setPostTypes] = React.useState(fallbackPostTypes);
    const [taxonomies, setTaxonomies] = React.useState(fallbackTaxonomies);
    const [taxonomyPostTypes, setTaxonomyPostTypes] = React.useState({});

    const { currentPostType, currentTaxonomy } = useSelect(selectStore => {
      const modalState = selectStore('divi/modal-library').getModal('divi/module');
      const moduleId = modalState?.owner ?? '';
      const sourceAttribute = selectStore('divi/edit-post').getModuleAttr(moduleId, 'breadcrumb.innerContent');

      return {
        currentPostType: getStoredSourceValue(sourceAttribute, 'postType', 'auto'),
        currentTaxonomy: getStoredSourceValue(sourceAttribute, 'taxonomy', 'auto'),
      };
    });

    React.useEffect(() => {
      let mounted = true;

      loggedFetch({
        method: 'GET',
        restRoute: '/divi-cpt-breadcrumbs/v1/data-sources',
      })
        .then(result => {
          if (!mounted || !result || 'object' !== typeof result) {
            return;
          }

          setPostTypes(result.postTypes ?? fallbackPostTypes);
          setTaxonomies(result.taxonomies ?? fallbackTaxonomies);
          setTaxonomyPostTypes(result.taxonomyPostTypes ?? {});
        })
        .catch(() => {});

      return () => {
        mounted = false;
      };
    }, []);

    const visibleTaxonomies = React.useMemo(() => {
      if ('auto' === currentPostType) {
        return taxonomies;
      }

      const options = {};
      Object.entries(taxonomies).forEach(([taxonomy, option]) => {
        if ('auto' === taxonomy || 'none' === taxonomy) {
          options[taxonomy] = option;
          return;
        }

        const supportedPostTypes = taxonomyPostTypes[taxonomy] ?? [];
        if (supportedPostTypes.includes(currentPostType) || taxonomy === currentTaxonomy) {
          options[taxonomy] = option;
        }
      });

      return options;
    }, [currentPostType, currentTaxonomy, taxonomies, taxonomyPostTypes]);

    setSelectOptions(groupConfiguration, 'breadcrumbInnercontentPosttype', 'posttype', postTypes);
    setSelectOptions(groupConfiguration, 'breadcrumbInnercontentTaxonomy', 'taxonomy', visibleTaxonomies);

    return React.createElement(ModuleGroups, { groups: groupConfiguration });
  };

  const moduleClassnames = ({ classnamesInstance, attrs }) => {
    classnamesInstance.add('dcb-breadcrumbs--inline');

    const schema = attrs?.breadcrumb?.innerContent?.desktop?.value?.schema ?? 'on';
    if (isOn(schema)) {
      classnamesInstance.add('dcb-breadcrumbs--schema');
    }
  };

  const ModuleStyles = ({
    attrs,
    elements,
    settings,
    orderClass,
    mode,
    state,
    noStyleTag,
  }) => React.createElement(
    StyleContainer,
    { mode, state, noStyleTag },
    elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    }),
    elements.style({ attrName: 'linkText' }),
    elements.style({ attrName: 'currentText' }),
    elements.style({ attrName: 'separatorText' }),
    React.createElement(CssStyle, { selector: orderClass, attr: attrs.css, cssFields }),
  );

  const CptBreadcrumbsEdit = ({ attrs, elements, id, name }) => {
    const value = attrs?.breadcrumb?.innerContent?.desktop?.value ?? {};
    const items = [];

    if (isOn(value.showHome ?? 'on')) {
      items.push({ label: value.homeLabel || 'Home' });
    }

    if (isOn(value.showArchive ?? 'on')) {
      items.push({
        label: value.archiveLabel || humanizeSlug(value.postType, 'Content Archive'),
      });
    }

    if ('none' !== String(value.taxonomy ?? 'auto')) {
      items.push({ label: 'Parent Term' });
      items.push({ label: humanizeSlug(value.taxonomy, 'Child Term') });
    }

    if (isOn(value.showCurrent ?? 'on')) {
      items.push({ label: 'Current Item', current: true });
    }

    const separator = 'string' === typeof value.separator ? value.separator : '/';
    const listChildren = items.map((item, index) => {
      const content = item.current
        ? React.createElement('span', { className: 'dcb-breadcrumbs__current', 'aria-current': 'page' }, item.label)
        : React.createElement(
          'a',
          {
            className: 'dcb-breadcrumbs__link',
            href: '#',
            onClick: event => event.preventDefault(),
          },
          item.label,
        );
      const children = [content];

      if (index < items.length - 1) {
        children.push(
          React.createElement(
            'span',
            { className: 'dcb-breadcrumbs__separator', 'aria-hidden': 'true', key: `separator-${index}` },
            separator,
          ),
        );
      }

      return React.createElement(
        'li',
        { className: 'dcb-breadcrumbs__item', key: `item-${index}` },
        ...children,
      );
    });

    return React.createElement(
      ModuleContainer,
      {
        attrs,
        elements,
        id,
        name,
        stylesComponent: ModuleStyles,
        classnamesFunction: moduleClassnames,
      },
      elements.styleComponents({ attrName: 'module' }),
      React.createElement(
        'nav',
        { className: 'dcb-breadcrumbs__nav', 'aria-label': value.ariaLabel || 'Breadcrumb' },
        React.createElement('ol', { className: 'dcb-breadcrumbs__list' }, ...listChildren),
      ),
    );
  };

  const icon = {
    name: 'burnt-arrow/module-cpt-breadcrumbs',
    viewBox: '0 0 24 24',
    component: () => React.createElement('path', {
      d: 'M3 4h5v5H3V4Zm7 1.5h5v2h-5v-2ZM17 4l4 2.5L17 9V4ZM3 15h5v5H3v-5Zm7 1.5h5v2h-5v-2Zm7-1.5 4 2.5L17 20v-5Z',
    }),
  };

  addFilter('divi.iconLibrary.icon.map', 'burntArrow.cptBreadcrumbs', icons => ({
    ...icons,
    [icon.name]: icon,
  }));

  addAction(
    'divi.moduleLibrary.registerModuleLibraryStore.after',
    'burntArrow.cptBreadcrumbs',
    () => {
      registerModule(metadata, {
        defaultAttrs,
        defaultPrintedStyleAttrs,
        settings: {
          content: SettingsContent,
        },
        renderers: {
          edit: CptBreadcrumbsEdit,
        },
      });
    },
  );
})();
