<div class='content'>
  <h1>{$register}</h1>

<div id='moreInfoDisplay'>
		{$moreInfo}
	</div>
	<div id='moreInfoOtherPart'>
  <form method='post' action="#" id='register'>

    {if $success ne "none"}
      {if $error eq "none"}
        <div id="successmsg">{$success}</div>
      {else}
        <div id="errormsg">{$error}</div>
      {/if}
    {else}
    
    <span class='block'><label for='mail'>{$mail}</label><input type='text' name='mail' value="{$mailValue}"/></span>
    <span class='block'><label for='login'>{$login}</label><input type='text' name='login' value="{$loginValue}"/></span>
    <span class='block'><label for='name'>{$name}</label><input type='text' name='name' value="{$nameValue}"/></span>
    <span class='block'><label for='firstname'>{$firstName}</label><input type='text' name='firstname' value="{$firstNameValue}"/></span>
    <span class='block'><label for='password'>{$password}</label><input type='password' name='password' value="{$passwordValue}"/></span>
    <span class='block'><label for='password2'>{$confirmPassword}</label><input type='password' name='password2' value="{$passwordValue2}"/></span>
    <span class='block'><img src='/ws/showcaptcha' alt='captcha' /></span>
    <span class='block'><label for='captcha'>{$securityCode}</label><input type='text' name='captcha' value=""/></span>
    {if $error ne "none"}<div id="errormsg">{$error}</div>{/if}
    <br/>
    <br/>
    <input type='submit' value="{$registerSubmit}" />
    {/if}
  </form>
  </div>
</div>
