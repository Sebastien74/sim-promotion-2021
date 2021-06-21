require('../../../scss/front/default/catalog.scss');

import 'select2';

import searchText from "./search/search-text";
import searchFilters from "./search/search-filters";

$(function() {
    searchText();
    searchFilters();
});