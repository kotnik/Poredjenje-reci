{include file='header.tpl'}

<div id="content">
  <div id="head">
    <h1>Poređenje reči</h1>
  </div>
  <div id="description">
    <p>Izaberite set:</p>
    {foreach from=$sets item=set}
    <p><a href="{$base_url}?set={$set.id}">{$set.title}</a></p>
    {/foreach}
  </div>
</div>

{include file='footer.tpl'}
