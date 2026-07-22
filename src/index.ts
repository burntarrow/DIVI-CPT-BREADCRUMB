import { addAction } from '@wordpress/hooks';
import { registerModule } from '@divi/module-library';

import { cptBreadcrumbs } from './components/breadcrumbs';

import './module-icons';

addAction(
  'divi.moduleLibrary.registerModuleLibraryStore.after',
  'burntArrow.cptBreadcrumbs',
  () => {
    registerModule(cptBreadcrumbs.metadata, {
      defaultAttrs: cptBreadcrumbs.defaultAttrs,
      defaultPrintedStyleAttrs: cptBreadcrumbs.defaultPrintedStyleAttrs,
      settings: cptBreadcrumbs.settings,
      renderers: cptBreadcrumbs.renderers,
    });
  },
);
