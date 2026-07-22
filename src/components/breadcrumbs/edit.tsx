import React, { type ReactElement } from 'react';
import { ModuleContainer } from '@divi/module';

import { moduleClassnames } from './module-classnames';
import { ModuleStyles } from './styles';
import type { ServiceBreadcrumbsEditProps } from './types';

const isOn = (value: unknown): boolean => ['on', 'yes', 'true', '1'].includes(String(value));

export const ServiceBreadcrumbsEdit = ({
  attrs,
  elements,
  id,
  name,
}: ServiceBreadcrumbsEditProps): ReactElement => {
  const value = attrs?.breadcrumb?.innerContent?.desktop?.value ?? {};
  const items: Array<{ label: string; current?: boolean }> = [];

  if (isOn(value.showHome ?? 'on')) {
    items.push({ label: value.homeLabel || 'Home' });
  }
  if (isOn(value.showArchive ?? 'on')) {
    items.push({ label: value.archiveLabel || 'Services' });
  }

  items.push({ label: 'Residential' });
  items.push({ label: 'New Construction' });

  if (isOn(value.showCurrent ?? 'on')) {
    items.push({ label: 'Current Service', current: true });
  }

  const separator = typeof value.separator === 'string' ? value.separator : '/';

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      name={name}
      stylesComponent={ModuleStyles}
      classnamesFunction={moduleClassnames}
    >
      {elements.styleComponents({ attrName: 'module' })}
      <nav className="rp-d5-breadcrumbs__nav" aria-label={value.ariaLabel || 'Breadcrumb'}>
        <ol className="rp-d5-breadcrumbs__list">
          {items.flatMap((item, index) => {
            const itemNode = (
              <li className="rp-d5-breadcrumbs__item" key={`item-${index}`}>
                {item.current ? (
                  <span className="rp-d5-breadcrumbs__current" aria-current="page">{item.label}</span>
                ) : (
                  <a className="rp-d5-breadcrumbs__link" href="#" onClick={(event) => event.preventDefault()}>
                    {item.label}
                  </a>
                )}
              </li>
            );

            if (index === items.length - 1) {
              return [itemNode];
            }

            return [
              itemNode,
              <li className="rp-d5-breadcrumbs__separator" aria-hidden="true" key={`separator-${index}`}>
                {separator}
              </li>,
            ];
          })}
        </ol>
      </nav>
    </ModuleContainer>
  );
};
