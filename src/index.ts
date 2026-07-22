import { addAction } from '@wordpress/hooks';
import { registerModule } from '@divi/module-library';

import { serviceBreadcrumbs } from './components/breadcrumbs';

import './module-icons';

addAction(
  'divi.moduleLibrary.registerModuleLibraryStore.after',
  'renoPlus.serviceBreadcrumbs',
  () => {
    registerModule(serviceBreadcrumbs.metadata, {
      defaultAttrs: serviceBreadcrumbs.defaultAttrs,
      defaultPrintedStyleAttrs: serviceBreadcrumbs.defaultPrintedStyleAttrs,
      renderers: serviceBreadcrumbs.renderers,
    });
  },
);
