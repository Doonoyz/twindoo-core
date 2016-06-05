<div class='content'>
  <h1>{$forgot}</h1>

  <form method='post' action="#" id='forgot'>

    {if $successMsg ne "none"}
    <div id="successmsg">{$successMsg}</div>
    
    {else}
    
    {$introforgot}<br/>
    <span class='block'><label for='mail'>{$mail}</label><input type='text' name='mail' value="{$loginValue}"/></span>{if $errorMsg ne "none"}<div id="errormsg">{$errorMsg}</div>{/if}
    <br/>
    <br/>
    <input type='submit' value="{$submit}" />
    {/if}
  </form>
</div>