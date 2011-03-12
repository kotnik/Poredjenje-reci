{include file='header.tpl'}

<div id="content">
  <div id="head">
    <h1>Poređenje reči</h1>
  </div>
  <div id="description">
    <p>Prvi put vas vidim i trebaju mi sledeći podaci.</p>
    <form method="post" action="{$url}?set={$set}">
      <p>
        <label for="index">Vaš broj indexa:</label>
        <input type="text" name="index" id="index" />
      </p>
      <p>
        <label for="name">Vaše ime i prezime:</label>
        <input type="text" name="name" id="name" size="40" />
      </p>
      <p>
        <input name="submit" type="submit" class="submit" value="Snimi me" />
      </p>
    </form>
  </div>
</div>

{include file='footer.tpl'}
