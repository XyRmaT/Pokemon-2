### Dependencies
* PHP: v7.0.0
* Smarty: v3.1.29
* jQuery: v2.0.3
* angularJS: v1.4.7

### Updates

# 2015-12-23
* Changed entire template engine from Discuz to Smarty v3.1.29.
  * Variables now need assign in order to display.
  * Debugging mode available, more info see Smarty official documents.
* Added language pack (located at include/language-pack, currently only added Chinese).
* Started to use CSS3 & HTML5 as V1.0 completely abandons old browsers.
* Started to use AngularJS (jQuery will be removed soon).
* Removed jQuery.ui.
* Cleaned useless files.

### To-do
[ ] Convert all templates to .tpl
  [√] header.tpl
  [√] footer.tpl
  [√] index.tpl
  [ ] daycare.tpl
  [ ] map.tpl
  [ ] memcp.tpl
  [ ] pkmcenter.tpl
  [ ] ranking.tpl
  [ ] shelter.tpl
  [ ] shop.tpl
  [ ] starter.tpl
  [ ] userinfo.tpl
[ ] Rewrite stylsheet
[ ] Rewrite battle engine
[ ] Remove jQuery library