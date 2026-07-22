import { addFilter } from '@wordpress/hooks';
import * as serviceBreadcrumbsIcon from './icons/service-breadcrumbs';

addFilter('divi.iconLibrary.icon.map', 'renoPlus.serviceBreadcrumbs', (icons) => ({
  ...icons,
  [serviceBreadcrumbsIcon.name]: serviceBreadcrumbsIcon,
}));
