import React, { type ReactElement } from 'react';
import { CssStyle, StyleContainer, type StylesProps } from '@divi/module';

const cssFields = {
  nav: { subName: 'nav', label: 'Breadcrumb Navigation', selectorSuffix: ' .rp-d5-breadcrumbs__nav' },
  list: { subName: 'list', label: 'Breadcrumb List', selectorSuffix: ' .rp-d5-breadcrumbs__list' },
  item: { subName: 'item', label: 'Breadcrumb Item', selectorSuffix: ' .rp-d5-breadcrumbs__item' },
  link: { subName: 'link', label: 'Breadcrumb Link', selectorSuffix: ' .rp-d5-breadcrumbs__link' },
  current: { subName: 'current', label: 'Current Item', selectorSuffix: ' .rp-d5-breadcrumbs__current' },
  separator: { subName: 'separator', label: 'Separator', selectorSuffix: ' .rp-d5-breadcrumbs__separator' },
};

export const ModuleStyles = ({
  attrs,
  elements,
  settings,
  orderClass,
  mode,
  state,
  noStyleTag,
}: StylesProps<any>): ReactElement => (
  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
    {elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    })}
    {elements.style({ attrName: 'linkText' })}
    {elements.style({ attrName: 'currentText' })}
    {elements.style({ attrName: 'separatorText' })}
    <CssStyle selector={orderClass} attr={attrs.css} cssFields={cssFields} />
  </StyleContainer>
);
