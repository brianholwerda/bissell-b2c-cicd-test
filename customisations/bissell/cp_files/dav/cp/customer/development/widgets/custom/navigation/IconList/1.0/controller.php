<?php
namespace Custom\Widgets\navigation;

class IconList extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
        // if the maximum number of icons per row is not set then make it 6
        $max_icons_in_row = isset($this->data['attrs']['max_icons_in_row']) ? (int)$this->data['attrs']['max_icons_in_row'] : 6;
        
        $this->data['js']['width'] = floor( 99 / $max_icons_in_row );
        
        $this->data['js']['icon_items'] = array();
        $all_icon_info = explode('|', $this->data['attrs']['icon_info']);
        
        foreach($all_icon_info as $icon){

            $icon_info = explode('^', $icon);
            array_push($this->data['js']['icon_items'], array(
                'label' => $icon_info[0],
                'link' => $icon_info[1],
                'image_src' => $icon_info[2],
                'image_alt' => $icon_info[3]
            ));
        }
        return parent::getData();

    }
}