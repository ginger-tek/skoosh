<form id="settingsForm" method="post" action="/settings">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <button class="btn btn-primary" type="submit"><i class="bi bi-floppy"></i> Save Settings</button>
  <div class="grid">
    <article class="bottom-clear">
      <header>Locations</header>
      <?php foreach ($locations as $location): ?>
        <div role="group">
          <input class="input-sm" type="text" name="locations[<?= $location->id ?>][name]"
            value="<?= htmlspecialchars($location->name) ?>" required>
          <button data-tooltip="Remove" type="button" class="btn btn-error btn-sm"
            onclick="this.parentElement.remove()"><i class="bi bi-dash-circle"></i></button>
        </div>
      <?php endforeach; ?>
      <input class="input-sm" type="text" name="location" placeholder="New location">
    </article>
    <article class="bottom-clear">
      <header>Categories</header>
      <?php foreach ($categories as $category): ?>
        <div role="group">
          <input class="input-sm" type="text" name="categories[<?= $category->id ?>][name]"
            value="<?= htmlspecialchars($category->name) ?>" required>
          <button data-tooltip="Remove" type="button" class="btn btn-error btn-sm"
            onclick="this.parentElement.remove()"><i class="bi bi-dash-circle"></i></button>
        </div>
      <?php endforeach; ?>
      <input class="input-sm" type="text" name="category" placeholder="New category">
    </article>
  </div>
</form>
<script type="module">
  import { attachSubmitLoading, attachRequiredInputs } from '/assets/js/scripts.js';
  attachSubmitLoading(document.getElementById('settingsForm'));
  attachRequiredInputs();
</script>