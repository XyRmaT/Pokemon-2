## Dependencies
* PHP: v5.6
* Smarty: v3.1.29
* jQuery: v2.0.3
* angularJS: v1.4.7

## What's new in V1.0
* Features
  * Language pack (Chinese, English)
* Code
  * All redundant stuff will be removed.
  * PHP will not return stuff to manipulate elements using JS anymore, pure JSON/HTML data.
  * Using AngularJS framework, more easy to deal with JSON updates.

## Updates

### 2015-12-27
* Implemented AngularJS pop-up, draggable, AJAX and few directives.
* Added `r` egg identifier to pokemon generator.
* Added hex color & transparency to rgba replacement in the CSS parser (e.g. #E3E3EF, alpha .5 -> rgba(227, 227, 239, .5).
* Added AJAX loading effect (header decoration bar).
* Added currency change animation (incremental/decremental step 10).
* Added SQL file to database.sql.
* Added resource files.

### 2015-12-25
* Imported Generation 6 data.
* Changed the way `Obtain::Sprite()` works for Pokemon sprites.
* Added file `include/data-constant.php`.
* Added method `Obtain::Text()` to fetch language text, supports randomization if it's an array and it's not a data array.
* Decided not to apply backward compatability at all (Removed all related CSS properties).

### 2015-12-24
* Constanlized type, egg group and location raw values.
* Added field `has_egg` (to replace the multiple usage of `time_hatched`) and `memorized_moves` to table pkm_mypkm.
* Changed field `ability_dream` to `ability_hidden` in table pkm_pkmdata.
* Optimized daycare.

### 2015-12-23
* Changed entire template engine from Discuz to Smarty v3.1.29.
  * Variables now need assign in order to display.
  * Debugging mode available, more info see Smarty official documents.
* Added language pack (located at include/language-pack, currently only added Chinese).
* Started to use CSS3 & HTML5 as V1.0 completely abandons old browsers.
* Started to use AngularJS (jQuery will be removed soon).
  * Removed jQuery.ui.
* Cleaned useless files.
* Modulized stylesheets.
* Cache class
  * Can now customize $path_css and $path_cache.
  * Improved CSS minify feature.

### To-do
- Convert all templates to .tpl
  - [x] header.tpl
  - [x] footer.tpl
  - [x] index.tpl (Needs final clean up)
  - [x] daycare.tpl
  - [ ] map.tpl
  - [ ] memcp.tpl
  - [ ] pkmcenter.tpl
  - [ ] ranking.tpl
  - [ ] shelter.tpl
  - [ ] shop.tpl
  - [ ] starter.tpl
  - [ ] userinfo.tpl
- Implement
  - [ ] Horde feature
  - [ ] Signature feature
  - [ ] Map system
  - [ ] Announcement system
  - [ ] Weather system
  - [ ] Changing of sub-color depends on which page the user's at
  - [ ] PHP queue class
- Test
  - [ ] Daycare take egg
  - [ ] Bad egg
- [ ] Remove jQuery library
- [ ] Spinda
- [ ] Create item sprite sheet, depracate item image caching.
- [ ] Unset ALL unnecessary variables that will be encoded into JSON.

### Mechanical
* Daycare
  * EXP gaining: 5 EXP per minute (floor((current_time - sent_time) / 12))
  * Cost: 10 per hour (floor((current_time - sent_time) / 3600 / 6) + 1) * 10)
 
### Notes
* Will need to figure out a simple way to fetch fields.
* Planning to upgrade to PHP7.