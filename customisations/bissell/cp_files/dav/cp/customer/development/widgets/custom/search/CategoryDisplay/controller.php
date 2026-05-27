<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class CategoryDisplay extends Widget {

    function CategoryDisplay() {
        parent::Widget();

        // $this->attrs['label_title'] = new Attribute('Label Title', 'STRING', 'The label for the Title area of the widget', 'Browse Support');
        $this->attrs['label_view_all'] = new Attribute('Label View All', 'STRING', 'The label for the View All link', 'VIEW ALL');
        $this->attrs['link'] = new Attribute('Link', 'STRING', 'The base link', '/app/answers/list');
        $this->attrs['top_c'] = new Attribute('Category', 'STRING', 'Category in URL', '');

        /* ldavison - we might be able to use this to allow the paddle to start at any level
          $this->attrs['cat_lvl'] = new Attribute('Category Level', 'INT', 'The category level to use as the paddle handle. The next level will be used for the paddle content.', '/app/answers/list');
          $this->attrs['cat_lvl']->min = 1;
          $this->attrs['cat_lvl']->max = 5; // since we need two levels, we need to make the max 5
         */
    }

    function generateWidgetInformation() {
        $this->info['notes'] = msg_get_rnw(WIDGET_SERVES_TEMPL_MODEL_OWN_CUST_MSG);
    }

    function getData($data) {
        $this->CI->load->model('custom/Category_paddle_model');
        $this->CI->Category_paddle_model->getDisplayCategories($data);

        $url_c = getUrlParm('c');
        if ($url_c) {
            $c = explode(",", $url_c);
            if (count($c) > 1) {
                if ($c[0] == 6 || $c[0] == 7) {
                    $data['js']['top_c'] = $c[1];
                    //$data['js']['url'] = $c[0].','.$c[1];
                }
            } else {
                $data['js']['top_c'] = $c[0];
                //$data['js']['url'] = $c[0];
            }
        }

        $a_id = getUrlParm('a_id');
        if ($a_id) {
            $previous_cookie_c = "";
            if ($_COOKIE["previous_c"]) {
                $previous_cookie_c = $_COOKIE["previous_c"];
            }

            $this->CI->load->model('custom/Breadcrumb_model');

            $tmp = $this->CI->Breadcrumb_model->getAnswerInfo($a_id);
            //print_r($tmp);
            foreach ($tmp->Categories as $t) {
                $a_cat = "";
                $i = 0;

                foreach ($t->CategoryHierarchy as $ch) {
                    $a_cat .= $ch->ID . ',';
                    $i++;
                }
                $a_cat .= $t->ID;

                $c = explode(",", $a_cat);
                if (count($c) > 1) {
                    if ($c[0] == 6 || $c[0] == 7) {
                        $data['js']['top_c'] = $c[1];
                        //$data['js']['url'] = $c[0].','.$c[1];
                    }
                } else {
                    $data['js']['top_c'] = $c[0];
                    //$data['js']['url'] = $c[0];
                }
            }
        }


        return $data;
    }

}

