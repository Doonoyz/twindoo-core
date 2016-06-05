<div class='content'>
  <h1>{$forgot}</h1>

  <form method='post' action="#" id='forgot'>

    {if $successMsg ne "none"}
      {if $errorMsg ne "none"}
        <div id="errormsg">{$errorMsg}</div>
      {else}
        <div id="successmsg">{$successMsg}</div>
      {/if}
    {else}
    
    {$intropassword}<br/>
    <span class='block'><label for='password'>{$password}</label><input type='password' name='password' value=""/></span>
    <br/>
    <br/>
    <span class='block'><label for='chackpassword'>{$checkpassword}</label><input type='password' name='checkpassword' value=""/></span>
    <br/>

    
    {if $errorMsg ne "none"}<div id="errormsg">{$errorMsg}</div>{/if}
    <br/>
    

    <input type='submit' value="{$submit}" />
    {/if}

  </form>
</div>
