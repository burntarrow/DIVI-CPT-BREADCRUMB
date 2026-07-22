# Manual test matrix

Use a staging site running the target Divi 5 build.

| Context | Expected trail |
| --- | --- |
| Service single with Residential > New Construction | Home > Services > Residential > New Construction > Post title |
| Service single with only New Construction assigned | Same full hierarchy; Residential is derived from ancestors |
| Service single with parent and child both assigned | Child is selected; parent is not duplicated |
| Service single with two unrelated child terms | Yoast/Rank Math primary term wins; otherwise deepest term and lowest term ID win deterministically |
| Services archive | Home > Services |
| Child service-category archive | Home > Services > Residential > New Construction |
| Current item disabled | Remaining items stay linked; no false `aria-current` is added |
| Schema disabled | No Schema.org microdata attributes are output |
| Visual Builder | Illustrative preview renders and all module controls remain editable |
| Front end / Theme Builder template | Trail uses the actual queried post or term |

Also verify keyboard focus, responsive wrapping, PHP error logs, browser console, and Rich Results Test output.
