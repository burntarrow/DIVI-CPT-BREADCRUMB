import type { Metadata, ModuleLibrary } from '@divi/types';

import metadata from './module.json';
import { ServiceBreadcrumbsEdit } from './edit';
import type { ServiceBreadcrumbsAttrs } from './types';

import './style.scss';
import './module.scss';

const defaultAttrs = {
  module: {
    meta: {
      adminLabel: {
        desktop: {
          value: 'Service Breadcrumbs',
        },
      },
    },
  },
  breadcrumb: {
    innerContent: {
      desktop: {
        value: {
          homeLabel: 'Home',
          archiveLabel: 'Services',
          separator: '/',
          ariaLabel: 'Breadcrumb',
          postType: 'services',
          taxonomy: 'service-category',
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

export const serviceBreadcrumbs: ModuleLibrary.Module.RegisterDefinition<ServiceBreadcrumbsAttrs> = {
  metadata: metadata as Metadata.Values<ServiceBreadcrumbsAttrs>,
  defaultAttrs,
  defaultPrintedStyleAttrs,
  renderers: {
    edit: ServiceBreadcrumbsEdit,
  },
};
