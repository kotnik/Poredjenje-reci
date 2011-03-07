{include file='header-admin.tpl'}

    <p><b>{$set.title}</b>:</p>
    <form method="post" action="{$admin_url}">
      <p>
        <label for="title">Naslov:</label>
        <input type="text" name="title" id="title" size="40" value="{if $set.words}{$set.title}{/if}" />
      </p>
      <p>
        <label for="active">Aktivan:</label>
        <input type="checkbox" name="active" id="active" {if $set.active == 1} checked {/if}/>
      </p>
      <p>
        <label for="question">Pitanje:</label>
        <input type="text" name="question" id="question" size="40" value="{$set.question}" />
      </p>
      <p>
        <label for="intro">Intro text:</label>
        <textarea name="intro" rows="10" cols="40">{$set.intro}</textarea>
      </p>
      {if $set.words}
      <p>
        Reči:
        <i>
          {foreach from=$set.words item=word}
          {$word}
          {/foreach}
        </i>
      </p>
      {else}
      <p>
        <label for="words">Reči (odvojene spejsom):</label>
        <input type="text" name="words" id="words" size="40" />
      </p>
      {/if}
      <p>
        <input type="hidden" name="form_action" value="{$action}" />
        <input type="hidden" name="set_id" value="{$set.id}" />
        <input name="submit" type="submit" class="submit" value="Snimi" />
      </p>
    </form>

{include file='footer-admin.tpl'}
