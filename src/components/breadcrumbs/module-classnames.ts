import type { Module } from '@divi/types';

export const moduleClassnames = ({ classnamesInstance, attrs }: Module.Classnames.Params<any>): void => {
  classnamesInstance.add('rp-d5-breadcrumbs--inline');

  const schema = attrs?.breadcrumb?.innerContent?.desktop?.value?.schema ?? 'on';
  if (['on', 'yes', 'true', '1'].includes(String(schema))) {
    classnamesInstance.add('rp-d5-breadcrumbs--schema');
  }
};
