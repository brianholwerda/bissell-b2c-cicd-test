<rn:meta title="BISSELL OIA Test" template="standard_OIA.php" clickstream="home" login_required="false" />

<div class="rn_PageContent">
	
<rn:widget path="custom/OPA/OPAWidget" policy_model="This is the Policy Model Project Name"/>


</div>


<style>
.floating-label {
  position: relative;
}

.floating-label label {
position: absolute;
left: 12px;
top: 14px;

font-size: 16px;
opacity: .6;

pointer-events: none;
transition: all .2s ease-in-out;
}

.floating-label.is-floating label {
  top: 5px;
  
  font-size: 12px;
  font-weight: bold;
  opacity: 1;
}

.floating-label.has-focus label {
  color: royalblue;
  opacity: 1 !important;
}

.floating-label input {
padding: 20px 10px 4px 12px;
width: 250px;
}

</style>


<script>

window.addEventListener('DOMContentLoaded', function(e) {
  var container = document.getElementsByClassName('rn_SourceSearchField');
  var input = document.querySelector('input');
  
  // Make the label float when the field is focused
  input.addEventListener('focus', function(e) {
    container.classList.add('has-focus');
    container.classList.add('is-floating');
  });
});
</script>
