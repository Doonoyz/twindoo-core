<div class='content'>
  <h1>{$text.title}</h1>

	{if !$error and !$success}
  <form method='post' action="#" id='unsubscribe'>
	{$message}<br/>
    <input type='submit' name='yes' value="{$text.yes}" />
    <input type='submit' name='no' value="{$text.no}" />
  </form>
  {else}
  <div id="{if $success}success{else}error{/if}msg">{$message}</div>
  {/if}
</div>
