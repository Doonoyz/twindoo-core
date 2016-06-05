<div class='content'>
	<div id='moreInfoDisplay'>
		{$moreInfo}
	</div>
	<div id='moreInfoOtherPart'>
	  <h1>{$login}</h1>

	  <form method='post' action="#" id='login'>
		<span class='block'><label for='mail'>{$mail}<br/><sub>{$mailSub}</sub></label><input type='text' name='mail' value="{$loginValue}"/></span>
		<span class='block'><label for='password'>{$password}</label><input type='password' name='password' value="{$passwordValue}"/></span>
		<span class="block"><label for='rememberme'>{$rememberMe}</label><input type="checkbox" id='rememberme' name='rememberme' class="crirHiddenJS" value="{$remembered}" {if $remembered eq 'true'}checked='checked'{/if}/></span>
		<br/>
		<br/>
		<br/>
		<a href='/ws/forgot'>{$forgotPassword}</a><br/>
		<a href='/ws/register'>{$registerAccount}</a><br/>
		<br/>
		<input type='submit' value="{$connect}" />
	  </form>
	  
	  <form method="post" action="#">
		<img src="/images/openid-logo.gif" />&nbsp;
		<b>{$openIdLogin}</b><br />
		<input type="text" name="openid_id" size="45" />
		<input type="hidden" name="loginType" value="openId" />

		<input type="submit" name="submit" value="{$connect}" />
	  </form>
	</div>
	  {if $errorMsg ne "none"}<div id="errormsg">{$errorMsg}</div>{/if}
</div>