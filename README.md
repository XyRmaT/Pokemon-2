## Dependencies
* PHP: v7.0
* Smarty: v3.1.29
* jQuery: v2.0.3
* angularJS: v1.4.7
* ng-sortable: v1.3.2

## What's new in V1.0
* Features
  * Language pack (Chinese, English)
* Code
  * All redundant stuff will be removed.
  * PHP will not return stuff to manipulate elements using JS anymore, pure JSON/HTML data.
  * Using AngularJS framework, more easy to deal with JSON updates.

## Updates

### 2016-08-15
* Finished starter page.
* Re-structured `include` folder, made it more manageable.

### 2016-01-19
* Finished inventory.

### 2015-12-30
* Added English language pack.
* Added tooltip.

### 2015-12-30
* Implemented trainer card system.
* Imported ng-sortable v1.3.2.
* Party Pokemon reorder done.

### 2015-12-28
* Wrapped up daycare, it's now fully functional. **Awesome point +1.** Started working on member panel now.
* Finished member panel - pokedex.
* Everything in `$r` will be pushed to AngularJS's $rootScope.

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
  - [x] memcp.tpl (Main features done)
    - [x] Info (Currently only trainer details)
    - [x] Party
    - [x] Inventory
    - [x] Inbox
    - [x] Pokedex
    - [ ] Achievement (Awaiting for next update)
    - [ ] Setting (Awaiting for next update)
  - [ ] pc.tpl
  - [ ] ranking.tpl (Awaiting for next update)
  - [x] shelter.tpl
  - [x] shop.tpl
  - [x] starter.tpl
- Implement / Updates
  - [ ] Horde feature
  - [ ] Signature feature
  - [ ] Map system
    - [ ] Websocket based chatting system
  - [ ] Announcement system
  - [ ] Weather system
  - [ ] Changing of sub-color depends on which page the user's at
  - [ ] PHP queue class
  - [x] PHP7 compatible
- Test
  - [x] Daycare take egg
  - [ ] Bad egg
- [ ] Remove jQuery library
- [ ] Spinda
- [ ] Create item sprite sheet, deprecate item image caching.
- [ ] Unset ALL unnecessary variables that will be encoded into JSON.
- [x] Improve the performance of `Kit::imagettftextblur()`.
- [ ] All other stuff marked as TODO in the code.
- [ ] Rewrite DB class, add methods such as where()/select() to build queries, chainable.

### Mechanical
* Daycare
  * EXP gaining: 5 EXP per minute (floor((current_time - sent_time) / 12))
  * Cost: 10 per hour (floor((current_time - sent_time) / 3600 / 6) + 1) * 10)
 
### Notes
* Will need to figure out a simple way to fetch fields.
* V1.0 code must be clear, easy to understand and maintain.