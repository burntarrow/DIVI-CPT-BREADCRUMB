import React, { type ReactElement } from 'react';
import { ModuleContainer } from '@divi/module';

import { moduleClassnames } from './module-classnames';
import { ModuleStyles } from './styles';
import type { CptBreadcrumbsEditProps } from './types';

const isOn = (value: unknown): boolean => ['on', 'yes', 'true', '1'].includes(String(value));

const humanizeSlug = (value: unknown, fallback: string): string => {
  const slug = String(value ?? '').trim();
  if (!slug || 'auto' === slug) {
    return fallback;
  }

  return slug
    .replace(/[-_]+/g, ' ')
    .replace(/\b\w/g, character => character.toUpperCase());
};

export const CptBreadcrumbsEdit = ({
  attrs,
  elements,
  id,
  name,
}: CptBreadcrumbsEditProps): ReactElement => {
  const value = attrs?.breadcrumb?.innerContent?.desktop?.value ?? {};
  const items: Array<{ label: string; current?: boolean }> = [];

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
      <nav className="dcb-breadcrumbs__nav" aria-label={value.ariaLabel || 'Breadcrumb'}>
        <ol className="dcb-breadcrumbs__list">
          {items.map((item, index) => (
            <li className="dcb-breadcrumbs__item" key={`item-${index}`}>
              {item.current ? (
                <span className="dcb-breadcrumbs__current" aria-current="page">{item.label}</span>
              ) : (
                <a className="dcb-breadcrumbs__link" href="#" onClick={event => event.preventDefault()}>
                  {item.label}
                </a>
              )}
              {index < items.length - 1 && (
                <span className="dcb-breadcrumbs__separator" aria-hidden="true">{separator}</span>
              )}
            </li>
          ))}
        </ol>
      </nav>
    </ModuleContainer>
  );
};
