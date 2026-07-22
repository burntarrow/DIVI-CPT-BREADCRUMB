'use strict';

const fs = require('fs');
const vm = require('vm');

let registeredMetadata;
let registeredDefinition;
let stateQueue = [];
let currentPostType = 'auto';
let currentTaxonomy = 'auto';

const React = {
  createElement(type, props, ...children) {
    return { type, props: props || {}, children };
  },
  useEffect() {
    // Network behavior belongs to the PHP endpoint test. This smoke test injects
    // resolved state directly so the dependent-dropdown logic can be isolated.
  },
  useMemo(callback) {
    return callback();
  },
  useState(initialValue) {
    const value = stateQueue.length ? stateQueue.shift() : initialValue;
    return [value, () => {}];
  },
};

function selectStore(storeName) {
  if ('divi/modal-library' === storeName) {
    return {
      getModal() {
        return { owner: 'module-1' };
      },
    };
  }

  if ('divi/edit-post' === storeName) {
    return {
      getModuleAttr() {
        return {
          getIn(path) {
            const key = path[path.length - 1];
            return 'postType' === key ? currentPostType : currentTaxonomy;
          },
        };
      },
    };
  }

  throw new Error(`Unexpected Divi store: ${storeName}`);
}

global.vendor = {
  React,
  wp: {
    hooks: {
      addAction(hook, namespace, callback) {
        if ('divi.moduleLibrary.registerModuleLibraryStore.after' === hook) {
          callback();
        }
      },
      addFilter() {},
    },
  },
};

global.divi = {
  data: {
    useSelect(callback) {
      return callback(selectStore);
    },
  },
  module: {
    CssStyle() {},
    ModuleContainer() {},
    ModuleGroups() {},
    StyleContainer() {},
  },
  moduleLibrary: {
    registerModule(metadata, definition) {
      registeredMetadata = metadata;
      registeredDefinition = definition;
    },
  },
  rest: {
    loggedFetch() {
      return Promise.resolve({});
    },
  },
};

global.lodash = {};

const bundle = fs.readFileSync('scripts/bundle.js', 'utf8');
vm.runInThisContext(bundle, { filename: 'scripts/bundle.js' });

if ('burnt-arrow/cpt-breadcrumbs' !== registeredMetadata?.name) {
  throw new Error('The expected Divi module was not registered.');
}

if ('dcb_breadcrumbs' !== registeredMetadata?.moduleClassName) {
  throw new Error('The universal wrapper class was not registered.');
}

if ('function' !== typeof registeredDefinition?.settings?.content) {
  throw new Error('The custom Data Source settings renderer was not registered.');
}

currentPostType = 'services';
currentTaxonomy = 'auto';
stateQueue = [
  {
    auto: { label: 'Automatic' },
    post: { label: 'Posts (post)' },
    services: { label: 'Services (services)' },
  },
  {
    auto: { label: 'Automatic' },
    none: { label: 'None' },
    category: { label: 'Categories (category)' },
    'service-category': { label: 'Service Categories (service-category)' },
  },
  {
    category: ['post'],
    'service-category': ['services'],
  },
];

const groupConfiguration = {
  contentDataSource: {
    component: {
      props: {
        fields: {
          breadcrumbInnercontentPosttype: {
            component: { props: { options: {} } },
          },
          breadcrumbInnercontentTaxonomy: {
            component: { props: { options: {} } },
          },
        },
      },
    },
  },
};

registeredDefinition.settings.content({ groupConfiguration });

const fields = groupConfiguration.contentDataSource.component.props.fields;
const postTypeOptions = fields.breadcrumbInnercontentPosttype.component.props.options;
const taxonomyOptions = fields.breadcrumbInnercontentTaxonomy.component.props.options;

if (!postTypeOptions.services || !postTypeOptions.post) {
  throw new Error('The Post Type dropdown did not receive discovered options.');
}

if (!taxonomyOptions['service-category']) {
  throw new Error('A taxonomy registered for the selected CPT was not included.');
}

if (taxonomyOptions.category) {
  throw new Error('An unrelated taxonomy was not filtered from the dependent dropdown.');
}

if (!taxonomyOptions.auto || !taxonomyOptions.none) {
  throw new Error('Automatic and None taxonomy options must always remain available.');
}

console.log('Visual Builder dropdown bundle smoke test passed.');
