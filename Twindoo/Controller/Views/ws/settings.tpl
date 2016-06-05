<div class='content'>
  <h1>{$settings}</h1>

  <form method='post' action="#" id='settings'>
  <h2>{$mail}</h2>
  <h3>{$name} {$firstname}</h3>
    <span class='block' id='willdisapear'><a href='javascript:void(0);' id='linkpassword'>{$changePassword}</a></span>
    <div id='hidden'>
      <span class='block'><label for='password'>{$password}</label><input type='password' name='password' /></span>
      <span class='block'><label for='password2'>{$confirmPassword}</label><input type='password' name='password2' /></span>
    </div>
    <br/>
    <input type='submit' value="{$save}" />
  </form>
	<a href='/ws/unregister'>{$unregister}</a>
  {if $errorMsg ne "none"}<div id="errormsg">{$errorMsg}</div>{/if}
</div>