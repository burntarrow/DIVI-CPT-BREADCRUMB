import type { Metadata, ModuleLibrary } from '@divi/types';

import metadata from './module.json';
import { CptBreadcrumbsEdit } from './edit';
import { SettingsContent } from './settings-content';
import type { CptBreadcrumbsAttrs } from './types';

import './style.scss';
import './module.scss';

const defaultAttrs = {
  module: {
    meta: {
      adminLabel: {
        desktop: {
          value: 'CPT Breadcrumbs',
        },
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
            value: {
              weight: '600',
            },
          },
        },
      },
    },
  },
};

export const cptBreadcrumbs: ModuleLibrary.Module.RegisterDefinition<CptBreadcrumbsAttrs> = {
  metadata: metadata as Metadata.Values<CptBreadcrumbsAttrs>,
  defaultAttrs,
  defaultPrintedStyleAttrs,
  settings: {
    content: SettingsContent,
  },
  renderers: {
    edit: CptBreadcrumbsEdit,
  },
};
