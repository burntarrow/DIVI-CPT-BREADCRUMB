import React, { type ReactElement, useEffect, useMemo, useState } from 'react';
import { set } from 'lodash';

import { useSelect } from '@divi/data';
import { ModuleGroups } from '@divi/module';
import { loggedFetch } from '@divi/rest';
import type { FieldLibrary, Module } from '@divi/types';

import type { CptBreadcrumbsAttrs } from './types';

type DataSourceResponse = {
  postTypes?: FieldLibrary.Select.Options;
  taxonomies?: FieldLibrary.Select.Options;
  taxonomyPostTypes?: Record<string, string[]>;
};

const fallbackPostTypes: FieldLibrary.Select.Options = {
  auto: {
    label: 'Automatic (current request)',
  },
};

const fallbackTaxonomies: FieldLibrary.Select.Options = {
  auto: {
    label: 'Automatic (best taxonomy for the post)',
  },
  none: {
    label: 'None (omit taxonomy terms)',
  },
};

const getStoredSourceValue = (attribute: any, key: string, fallback: string): string => {
  if (attribute?.getIn) {
    return String(attribute.getIn(['desktop', 'value', key]) ?? fallback);
  }

  return String(attribute?.desktop?.value?.[key] ?? fallback);
};

const setSelectOptions = (
  groupConfiguration: Record<string, any>,
  fieldKey: string,
  fieldSuffix: string,
  options: FieldLibrary.Select.Options,
): void => {
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

  set(
    groupConfiguration,
    ['contentDataSource', 'component', 'props', 'fields', resolvedKey, 'component', 'props', 'options'],
    options,
  );
};

/**
 * Content settings renderer that populates the Data Source selects at runtime.
 */
export const SettingsContent = ({
  groupConfiguration,
}: Module.Settings.Panel.Props<CptBreadcrumbsAttrs>): ReactElement => {
  const [postTypes, setPostTypes] = useState<FieldLibrary.Select.Options>(fallbackPostTypes);
  const [taxonomies, setTaxonomies] = useState<FieldLibrary.Select.Options>(fallbackTaxonomies);
  const [taxonomyPostTypes, setTaxonomyPostTypes] = useState<Record<string, string[]>>({});

  const { currentPostType, currentTaxonomy } = useSelect(selectStore => {
    const modalState = selectStore('divi/modal-library').getModal('divi/module');
    const moduleId = modalState?.owner ?? '';
    const sourceAttribute = selectStore('divi/edit-post').getModuleAttr(moduleId, 'breadcrumb.innerContent');

    return {
      currentPostType: getStoredSourceValue(sourceAttribute, 'postType', 'auto'),
      currentTaxonomy: getStoredSourceValue(sourceAttribute, 'taxonomy', 'auto'),
    };
  });

  useEffect(() => {
    let mounted = true;

    loggedFetch({
      method: 'GET',
      restRoute: '/divi-cpt-breadcrumbs/v1/data-sources',
    })
      .then(result => {
        if (!mounted || !result || typeof result !== 'object') {
          return;
        }

        const response = result as DataSourceResponse;
        setPostTypes(response.postTypes ?? fallbackPostTypes);
        setTaxonomies(response.taxonomies ?? fallbackTaxonomies);
        setTaxonomyPostTypes(response.taxonomyPostTypes ?? {});
      })
      .catch(() => {
        // Keep the safe fallback options when discovery is unavailable.
      });

    return () => {
      mounted = false;
    };
  }, []);

  const visibleTaxonomies = useMemo<FieldLibrary.Select.Options>(() => {
    if ('auto' === currentPostType) {
      return taxonomies;
    }

    const options: FieldLibrary.Select.Options = {};

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

  setSelectOptions(
    groupConfiguration as Record<string, any>,
    'breadcrumbInnercontentPosttype',
    'posttype',
    postTypes,
  );
  setSelectOptions(
    groupConfiguration as Record<string, any>,
    'breadcrumbInnercontentTaxonomy',
    'taxonomy',
    visibleTaxonomies,
  );

  return <ModuleGroups groups={groupConfiguration} />;
};
