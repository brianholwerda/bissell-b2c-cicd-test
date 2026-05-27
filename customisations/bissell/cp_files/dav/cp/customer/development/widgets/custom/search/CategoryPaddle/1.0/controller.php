<?php
namespace Custom\Widgets\search;
use \RightNow\Connect\v1_2 as RNCPHP;

class CategoryPaddle extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {

        $this->data['results'] = $this->CI->model('Prodcat')->getHierarchy(
            "categories",
            1,
            $this->data['attrs']['max_categories'],
            $topLevelIDs ? explode(',', $topLevelIDs) : array(),
            0
        )->result;
        
        if(!count($this->data['results'])) {
            return false;
        }

        $resultCount = count($this->data['results']);
        $this->data['results'] = array_chunk($this->data['results'], (int) ceil($resultCount / 3));

        // Get the Domain
        $this->data['server'] = $_SERVER['HTTP_HOST'];

        // Loop through the Category ID's to get the most popular answers
        $searchLimit = $this->data['attrs']['max_answers'];
        $answerContent = array();
        foreach($this->data['results'] as $resultIndex => $resultGroup):
            foreach ($resultGroup as $index => $item):
                $answerContent[$item['id']] = $this->CI->model('Answer')->getPopular($searchLimit, null, $item['id']);
            endforeach;
        endforeach;

        // Loop through array of returned popular answer results
        foreach($answerContent as $answerResultIndex => $answerResultGroup):
            if($answerResultGroup->warnings[0] != "No results found.") {
                foreach ($answerResultGroup->result as $answerIndex => $answerItem):
                    $cf = $this->getAnswerCustomFields($answerItem->ID);
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['title'] = $answerItem->Title;
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['excerpt'] = $answerItem->Excerpt;
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['category'] = $answerResultIndex;
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['youTubeId'] = $cf[0];//$cf[0]
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['playModal'] = $cf[2];
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['thumbNailUrl'] = "https://i1.ytimg.com/vi/".$this->data['answerResults'][$answerResultIndex][$answerItem->ID]['youTubeId']."/mqdefault.jpg";//$cf[1]
                    $this->data['answerResults'][$answerResultIndex][$answerItem->ID]['image'] = '<img class="rn_answerRightArrow" src="images/arrow-right.png"/>';
                endforeach;
            } else {
                $this->data['answerResults'][$answerResultIndex][0]['title'] = "No Results";
            }
        endforeach;
    }

    function getAnswerCustomFields($ansId) {
        $answerObject = RNCPHP\Answer::fetch($ansId);
        $youTubeId = $answerObject->CustomFields->c->youtube_id;
        $videoThumbnail = $answerObject->CustomFields->c->video_thumbnail_url;
        $videoPlayModal = $answerObject->CustomFields->c->play_modal;
        return array($youTubeId, $videoThumbnail, $videoPlayModal);
    }
}