<?php
namespace Custom\Widgets\quiq;

class QuiqMultiline extends \RightNow\Widgets\Multiline {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
      parent::getData();
      $this->data['attrs']['counter'] = 0;
      $this->data['attrs']['limit_column_lengths'] = json_decode($this->data['attrs']['limit_column_lengths']);
    }
}
