<?php
//@description: test smoke javascript for managing dialog box

?>
<h1> smoke alert</h1>
<pre>
		smoke.alert('alert',func_callback,{title:"Titre"});
</pre>

<script>
	function func_callback() {
		alert("Function called");
	}
	function smoke_alert()
	{
		smoke.alert('alert',func_callback,{title:"Titre"});
	}
	
</script>
<button onclick="smoke_alert()">smoke_alert</button>

<h1> smoke confirm</h1>
<pre>
		smoke.confirm('Confirmez-vous',function(ev) { if (ev) { alert("confirmé");},{title:"Titre"});
</pre>

<script>

	function smoke_confirm()
	{
		smoke.confirm('Confirmez-vous',function(ev) { if (ev) { alert("confirmé");}},{title:"Titre"});
	}
	
</script>
<button onclick="smoke_confirm()">smoke_confirm</button>

<h1> smoke prompt</h1>
<pre>
	function func_callback_prompt(ev)
	{
		if (ev) { alert ("accepted  "+ev);}
		console.debug(ev);
		return true;
	}
	
smoke.prompt('Confirmez-vous',func_callback_prompt,{title:"Titre"});
</pre>

<script>
	function func_callback_prompt(ev)
	{
		if (ev) { alert ("accepted  "+ev);}
		console.debug(ev);
		return true;
	}
	function smoke_prompt()
	{
		smoke.prompt('Donnez un élement',func_callback_prompt,{title:"Titre"});
	}
	
</script>
<button onclick="smoke_prompt()">smoke_prompt</button>
