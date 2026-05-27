<rn:meta controller_path="custom/opa/OPAWidget" js_path="custom/opa/OPAWidget" base_css="custom/opa/OPAWidget" presentation_css="widgetCss/OPAWidget.css"/>

<script id="opa_script_placeholder"></script>
<? if ($this->data['error_text']): ?>
    <div><?=$this->data['error_text']?></div>
<? else: ?>
    <div id="opa_interview_div" style="
    <?=($this->data['attrs']['height'] != '') ? "height:".$this->data['attrs']['height'].";max-height:".$this->data['attrs']['height'].";overflow-y:auto;" : "" ?>
    <?=($this->data['attrs']['width'] != '') ? "width:".$this->data['attrs']['width'].";max-width:".$this->data['attrs']['width'].";overflow-x:auto;" : "" ?>
    "/>
    <?="<script src=\"".$this->data['opaWdUrl']."/staticresource/interviews.js\"></script>" ?>
    <script type="text/javascript">
        function onLoad(interviewObj) {
            // do nothing
        }
        function onNavigate(redirectUrl) {
            window.location.replace(redirectUrl);
        }
        var opaEl = document.getElementById("opa_interview_div");
        var wdUrl = "<?=$this->data['opaWdUrl'] ?>";
        var deployment = "<?=$this->data['attrs']['policy_model'] ?>";
        var locale = "<?=$this->data['attrs']['locale'] ?>";
        var startParams = {
        <? if ($this->data['userToken'] != ''): ?>
            __CHECKPOINT_ACTION:"<?=$this->data['checkpointAction'] ?>",
            user:"<?=$this->data['userToken'] ?>"
            <? if ($this->data['attrs']['init_id'] != ''): ?>
            ,initID:"<?=$this->data['attrs']['init_id'] ?>"
            <? endif; ?>
        <? endif; ?>
        };
        try {
            var seedData = <?=$this->data['seedData'] != '' ? $this->data['seedData'] : "null" ?>;
            var seedDataOk = true;
        } catch (e) {
            opaEl.innerHTML = "Seed data JSON object is invalid: " + e.message;
        }
        if (seedDataOk) {
            OraclePolicyAutomationInterview.StartInterview(opaEl, wdUrl, deployment, locale, null, startParams, seedData, onLoad, onNavigate);
        }
        
             
    </script>
    </div>   
<? endif; ?>