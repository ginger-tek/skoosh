<form id="createTicketForm" method="post" action="/tickets">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <header>
    <h4>Create New Ticket</h4>
  </header>
  <label>Summary
    <input name="summary" maxlength="140" autocomplete="off" required>
  </label>
  <div class="grid">
    <label>Location
      <select name="location_id" required>
        <option value="" disabled selected>Select a location</option>
        <?php foreach ($locations as $location): ?>
          <option value="<?= $location->id ?>"><?= htmlspecialchars($location->name) ?></option>
        <?php endforeach;
        if (\count($locations) === 0): ?>
          <option value="" disabled>No locations available</option>
        <?php endif; ?>
      </select>
    </label>
    <label>Category
      <select name="category_id" required>
        <option value="" disabled selected>Select a category</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category->id ?>"><?= htmlspecialchars($category->name) ?></option>
        <?php endforeach;
        if (\count($categories) === 0): ?>
          <option value="" disabled>No categories available</option>
        <?php endif; ?>
      </select>
    </label>
  </div>
  <div class="grid bottom-spacing">
    <div>
      <label>Urgency</label>
      <div role="group">
        <?php foreach (\App\Data\Tickets::URGENCY_LEVELS as $value => $label): ?>
          <label role="button" class="btn btn-primary">
            <input name="urgency" type="radio" value="<?= $value ?>" <?= $value === 2 ? 'checked' : '' ?> hidden>
            <?= $label ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i>Submit</button>
</form>
<script type="module">
  import { attachSubmitLoading, attachRequiredInputs } from '/assets/js/scripts.js';
  attachSubmitLoading(document.getElementById('createTicketForm'));
  attachRequiredInputs();
</script>