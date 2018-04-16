<?php
/**
 * @file
 * Contains \Drupal\kwall_custom\Plugin\Block\kwall_custom_sidebarmenu.
 */
namespace Drupal\kwall_custom\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\menu_block\Plugin\Block\MenuBlock;


/**
 * Provides a block sidebar.
 *
 * @Block(
 *   id = "cdu_custom_sidebar_menu",
 *   admin_label = @Translation("Cdu Custom Sidebar"),
 * category = @Translation("cdu_custom"),
 * )
 */
class kwall_custom_sidebarmenu extends MenuBlock {
/*
  public function getDerivativeId(){
    return 'sidebar-menu';
  }
  //copied from menu block cant inherit because we need to change properties within
  public function build() {

    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    // Adjust the menu tree parameters based on the block's configuration.
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $expand = $this->configuration['expand'];
    $parent = $this->configuration['parent'];
    $follow = $this->configuration['follow'];
	$follow_parent = $this->configuration['follow_parent'];

    $max_depth = $level + $depth - 1;
    $parameters->setMinDepth($level);
	$min_depth = $level;


    if ($follow) {
      $level += count($parameters->activeTrail) - 1;
      end($parameters->activeTrail);
      $root_item = current($parameters->activeTrail);
      if (empty($root_item) && count($parameters->activeTrail) > 1) {
        $root_item = prev($parameters->activeTrail);
        $level--;
      }
      if ($follow_parent && (($level - 1) > $min_depth) ) {
        $root_item = prev($parameters->activeTrail);
        $level--;
      }
      $parameters->setRoot($root_item);
    }

    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($max_depth > 0) {
      $parameters->setMaxDepth(min($max_depth, $this->menuTree->maxDepth()));
    }

    // For menu blocks with start level greater than 1, only show menu items
    // from the current active trail. Adjust the root according to the current
    // position in the menu in order to determine if we can show the subtree.
    // If we're using a fixed parent item, we'll skip this step.
    $fixed_parent_menu_link_id = str_replace($menu_name . ':', '', $parent);
    if ($level > 1 && !$fixed_parent_menu_link_id) {
      if (count($parameters->activeTrail) >= $level) {
        // Active trail array is child-first. Reverse it, and pull the new menu
        // root based on the parent of the configured start level.
        $menu_trail_ids = array_reverse(array_values($parameters->activeTrail));
        $menu_root = $menu_trail_ids[$level - 1];
        $parameters->setRoot($menu_root)->setMinDepth(1);
        if ($depth > 0) {
          $max_depth = min($level - 1 + $depth - 1, $this->menuTree->maxDepth());
          $parameters->setMaxDepth($max_depth);
        }
      }
      else {
        return array();
      }
    }

    // If expandedParents is empty, the whole menu tree is built.
    if ($expand) {
      $parameters->expandedParents = array();
    }
    // When a fixed parent item is set, root the menu tree at the given ID.
    if ($fixed_parent_menu_link_id) {
      $parameters->setRoot($fixed_parent_menu_link_id);

      // If the starting level is 1, we always want the child links to appear,
      // but the requested tree may be empty if the tree does not contain the
      // active trail.
      if ($level === 1 || $level === '1') {
        // Check if the tree contains links.
        $tree = $this->menuTree->load($menu_name, $parameters);
        if (empty($tree)) {
          // Change the request to expand all children and limit the depth to
          // the immediate children of the root.
          $parameters->expandedParents = array();
          $parameters->setMinDepth(1);
          $parameters->setMaxDepth(1);
          // Re-load the tree.
          $tree = $this->menuTree->load($menu_name, $parameters);
        }
      }
    }

    // Load the tree if we haven't already.
    if (!isset($tree)) {
      $tree = $this->menuTree->load($menu_name, $parameters);
    }
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),

    );
    $tree = $this->menuTree->transform($tree, $manipulators);
    $has_children = 0;
    foreach($tree as $key => $node){
      if($node->depth == 2 && $node->inActiveTrail){
        $has_children = $node->hasChildren;
        break;
      }
    }
    if ($has_children){
      foreach($tree as $key => $node){
        if($node->depth == 2 && !$node->inActiveTrail){
          unset($tree[$key]);
        }
      }
    }
    //kint($tree);

    $build = $this->menuTree->build($tree);

    if (!empty($build['#theme'])) {
      // Add the configuration for use in menu_block_theme_suggestions_menu().
      $build['#menu_block_configuration'] = $this->configuration;
      // Remove the menu name-based suggestion so we can control its precedence
      // better in menu_block_theme_suggestions_menu().
      $build['#theme'] = 'menu';
    }

    return $build;
  }
*/


}
function kwall_custom_tree(&$tree){

}