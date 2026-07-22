# Manual test matrix

Use a staging site running the exact Divi 5 build targeted for production.

| Context | Expected result |
| --- | --- |
| Open module Data Source settings | Post Type and Taxonomy render as dropdowns, not text inputs |
| Post Type dropdown | Shows Posts, Pages, and every public or publicly queryable CPT with label and slug |
| Taxonomy dropdown in Automatic post type mode | Shows all public or publicly queryable taxonomies |
| Select `services` | Taxonomy dropdown narrows to taxonomies registered for `services` |
| Change from one CPT to another | Taxonomy list refreshes without closing the settings modal |
| Previously saved taxonomy is not registered for newly selected CPT | Saved option remains visible; front end safely omits incompatible taxonomy terms |
| Automatic service single with Residential > New Construction | Home > Services > Residential > New Construction > Post title |
| Only New Construction assigned | Same full hierarchy; Residential is derived from term ancestors |
| Parent and child both assigned | Child is selected and parent is not duplicated |
| Two unrelated child terms | Yoast/Rank Math primary term wins; otherwise deepest term and lowest term ID win |
| Taxonomy set to None | Home > Services > Post title |
| CPT without an archive | No empty or unlinked archive crumb appears |
| Regular post with Posts page assigned | Home > Posts page title > category hierarchy > Post title |
| Hierarchical Page | Home > Parent page > Child page |
| Hierarchical CPT post | Archive > parent posts > taxonomy hierarchy > current post |
| CPT archive | Home > registered archive label |
| Child taxonomy archive | Home > associated CPT archive > parent term > child term |
| Current item disabled | Remaining items stay linked; no false `aria-current` is added |
| Schema disabled | No Schema.org microdata attributes are output |
| Visual Builder preview | Generic but responsive hierarchy preview renders and remains editable |
| REST request while logged out | Endpoint is denied |
| Front end and Theme Builder template | Trail uses the actual queried post or term |

Also verify keyboard focus, responsive wrapping, PHP error logs, browser console, caching/minification compatibility, and structured-data validation.
