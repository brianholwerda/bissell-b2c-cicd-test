<head>
	<!-- We pasted the generated code from the Issue Collector here, after choosing a custom trigger -->

	<!-- This is the script for the issue collector feedback form -->

	<script type="text/javascript" src="https://helixbusinesssolutions.atlassian.net/s/d41d8cd98f00b204e9800998ecf8427e-T/rwkjvl/b/1/7ebd7d8b8f8cafb14c7b0966803e5701/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?locale=en-US&collectorId=1762fdac"></script>
 
	<!-- This is the script for specifying the custom trigger.  We've replaced 'myCustomTrigger' with 'feedback-button' -->

	<script type="text/javascript">
		window.ATL_JQ_PAGE_PROPS =  {
			"triggerFunction": function(showCollectorDialog) {
				//Requries that jQuery is available! 
				jQuery("#feedback-button").click(function(e) {
					e.preventDefault();
					showCollectorDialog();
				});
			}
		};
	</script>



<style>

img.middle { vertical-align: middle; }

</style>
</head>

<body>

	
	<h2><img class="middle" src="https://bissell.custhelp.com/rnt/rnw/img/enduser/bissell-logo.png" style="width:60px;height:60px;">&nbsp;&nbsp;UAT Issue Collector</h2>
	<a href="#" id="feedback-button" class='btn btn-primary btn-large'>Report feedback</a>

</body>