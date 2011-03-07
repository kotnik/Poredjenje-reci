{include file='header-admin.tpl'}

    <p>Svi setovi (<a href="?action=new">Novi</a>):</p>
    {foreach from=$sets item=set}
    <p>
      {$set.title} - - <i>{$set.id}</i> - -
      (<a href="?set={$set.id}&action=view">Vidi</a>
      <a href="?set={$set.id}&action=edit">Uredi</a>
      <a href="?set={$set.id}&action=delete">Briši</a>)
    </p>
    {/foreach}

{include file='footer-admin.tpl'}
