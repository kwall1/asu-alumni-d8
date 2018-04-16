; Specify the version of Drupal being used.
core = 8.x
; Specify the api version of Drush Make.
api = 2
; N/A

; The Views integration Datetime Range fields should extend the views integration for regular Datetime fields
projects[drupal][patch][] = "https://www.drupal.org/files/issues/2786577-270_0.patch"

; Rendering child fields in field group twig template
; https://www.drupal.org/node/2872723
projects[field_group][patch][] = "https://www.drupal.org/files/issues/2018-04-10/field_group-rendering_child_field_in_field_group_twig_template-2872723-7.patch"

; Allow paragraph type deletion if content exists
; https://www.drupal.org/project/paragraphs/issues/2764681
projects[paragraphs][patch][] = "https://www.drupal.org/files/issues/2764681-allow-paragraphs-type-deletion-if-content-exists-55.patch"

; Enabling Layout Plugin module causes fatal error
; Remove it. Since the mosdule in drupal core since v 8.5.0.
; https://www.drupal.org/project/layout_plugin/issues/2871585
projects[layout_plugin][patch][] = "https://www.drupal.org/files/issues/fatal-error-when-installing-module.patch"

; Flexslider pause on click - remove option when only one slide
; https://www.drupal.org/project/flexslider/issues/2219435
projects[layout_plugin][patch][] = "https://www.drupal.org/files/issues/pause_1_slide-flexslider-2219435-1.patch"
