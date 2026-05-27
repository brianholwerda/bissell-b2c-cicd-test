<?php
namespace Custom\Widgets\navigation;
use RightNow\Utils\Url,
    RightNow\Utils\Text,
    RightNow\Utils\Config,
    RightNow\Utils\Connect,
    RightNow\Utils\Framework,
    RightNow\Connect\v1_3 as ConnectPHP;
    
class VisualProductCategorySelectorHighLimit extends \RightNow\Widgets\VisualProductCategorySelector {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {

        //return parent::getData();

		$hierarchyType = $this->data['attrs']['type'];
        $itemID = $this->data['attrs']['show_sub_items_for'] ?: null;

        //if (!$items = $this->CI->model('Prodcat')->getDirectDescendants($hierarchyType, $itemID)->result) return false;

		if (!$items = $this->CI->model('Prodcat')->getDirectDescendants($hierarchyType, $itemID, 200, array(), 200)->result) return false;
		
        if ($items = $this->limitItems($items, $this->data['attrs']['top_level_items'], $this->data['attrs']['maximum_items'])) {
            $this->data['js'] = array(
                'items'              => $items,
                'appendedParameters' => Url::getParametersFromList($this->data['attrs']['add_params_to_url']) . Url::sessionParameter(),
            );
            
            if($item = $this->CI->model('Prodcat')->get($itemID)->result) {
                $this->data['attrs']['label_breadcrumb'] = $item->Name;
            }
            
            if($this->data['attrs']['prefetch_sub_items_non_ajax'] && !$this->data['attrs']['prefetch_sub_items']) {
                $linking = $this->CI->model('Prodcat')->getLinkingMode();
                foreach($items as $item) {
                    if ($item['hasChildren']) {
                        $id = $item['id'];
                        $this->data['js']['subItems'][$id] = $this->getSubItems($id, $hierarchyType, $linking)->result[0] ?: array();
                    }
                }
            }
        }
        else {
            return false;
        }

    }

    /**
     * Overridable methods from VisualProductCategorySelector:
     */
    // function limitItems($items, $limitTo, $maxLimit)
    // protected function filterItemIDs($items, $limitTo)
    // function($item) use ($limitTo)
    // protected function limitToMaxSize($items, $maxLimit)
    // function getSubItems($id, $filter, $linking)
}