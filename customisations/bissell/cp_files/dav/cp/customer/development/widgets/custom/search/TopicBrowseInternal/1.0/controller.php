<?php
namespace Custom\Widgets\search;
use RightNow\Connect\v1_2 as RNCPHP;

class TopicBrowseInternal extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {

        $cId = getUrlParm('c');
	    $this->data['category'] = $cId;
        $this->data['attr']['category'] = $this->data['category'];
        // Get the sub-categories of the url parameter
        if($cId) {
            $topics = $this->CI->model('ProdCat')->getDirectDescendants(Category, $cId);
        } else {
            $topics = $this->CI->model('ProdCat')->getDirectDescendants(Category, $this->data['attrs']['default_category']);
        }
        //die(print_r($topics->results, true));
        if($topics->errors[0]->internalMessage) {
            $this->data['catError'] = $topics->errors[0]->internalMessage;
        } else {
            // Loop through the Category ID's of direct descendants and build an array of most popular answers for each
            $searchLimit = $this->data['attrs']['max_answers'];
            $answerContent = array();
            foreach($topics->result as $topicIndex => $topicItem):
                    $answerContent[$topicItem['id']] = $this->CI->model('Answer')->getPopular($searchLimit, null, $topicItem['id']);
                	$answerContent[$topicItem['id']]->label = $topicItem['label'];
            endforeach;

            // Loop through array of returned popular answer results to build a more managable array for the view
            foreach($answerContent as $answerResultIndex => $answerResultGroup):
            	$this->data['answerResults'][$answerResultIndex]['category'] = $answerResultGroup->label;
                if($answerResultGroup->warnings[0] != "No results found.") {
                    foreach ($answerResultGroup->result as $answerIndex => $answerItem):
                        $cf = $this->getAnswerCustomFields($answerItem->ID);
                        $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['title'] = $answerItem->Title;
                        $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['excerpt'] = $answerItem->Excerpt;
                        $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['youTubeId'] = $cf[0];//v4Wy7gRGgeA
                        $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['playModal'] = $cf[1];
                        $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['thumbNailUrl'] = "https://i1.ytimg.com/vi/".$this->data['answerResults'][$answerResultIndex][$answerItem->ID]['youTubeId']."/mqdefault.jpg"; 
                        $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['image'] = '<img class="rn_answerRightArrow" src="images/arrow-right.png"/>';  
                    endforeach;
                } else {
                    $this->data['answerResults'][$answerResultIndex][0]['title'] = "No Results";
                }
            endforeach;
			//die(print_r($this->data['answerResults'],true));
        }
    }

    function getAnswerCustomFields($ansId) {
        $answerObject = RNCPHP\Answer::fetch($ansId);
        $youTubeId = $answerObject->CustomFields->c->youtube_id;
        //$videoThumbnail = $answerObject->CustomFields->c->video_thumbnail_url;
        $videoPlayModal = $answerObject->CustomFields->c->play_modal;
        return array($youTubeId, $videoPlayModal);
    }
}
