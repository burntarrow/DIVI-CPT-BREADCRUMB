import { addFilter } from '@wordpress/hooks';
import * as cptBreadcrumbsIcon from './icons/cpt-breadcrumbs';

addFilter('divi.iconLibrary.icon.map', 'burntArrow.cptBreadcrumbs', icons => ({
  ...icons,
  [cptBreadcrumbsIcon.name]: cptBreadcrumbsIcon,
}));
