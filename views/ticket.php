<form id="ticketForm" method="post" action="/tickets/<?= $ticket->id ?>">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <header class="flex spaced">
    <div>
      <small>#<?= $ticket->id ?></small>
      <h4>Ticket Details</h4>
    </div>
    <div class="flex gap-small">
      <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Save</button>
      <?php if ($is_admin): ?>
        <button type="button" id="deleteBtn" class="btn btn-error"><i class="bi bi-trash"></i> Delete</button>
      <?php endif; ?>
    </div>
  </header>
  <div class="grid">
    <div>
      <label>Summary
        <input type="text" name="summary" value="<?= htmlspecialchars($ticket->summary) ?>" required>
      </label>
      <div class="grid collapse">
        <label>Assignee
          <select name="assignee_id" <?= $is_admin ? '' : 'disabled' ?> required>
            <option value="" <?= empty($ticket->assignee_id) ? 'selected' : '' ?> disabled>Select an assignee</option>
            <?php foreach ($assignees as $assignee): ?>
              <option value="<?= $assignee->id ?>" <?= $ticket->assignee_id == $assignee->id ? ' selected' : '' ?>>
                <?= htmlspecialchars($assignee->display_name) ?>
              </option>
            <?php endforeach; ?>
            <?php if (\count($assignees) === 0): ?>
              <option value="" disabled>No users available</option>
            <?php endif; ?>
          </select>
        </label>
        <label>State
          <select name="state" id="state" <?= $is_admin ? '' : 'disabled' ?> required
            onchange="this.parentElement.nextElementSibling.style.display = this.value == 'On Hold' ? 'block' : 'none'">
            <?php foreach (\App\Data\Tickets::STATES as $state): ?>
              <option value="<?= $state ?>" <?= $ticket->state == $state ? ' selected' : '' ?>><?= $state ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label style="display: <?= $ticket->state == 'On Hold' ? 'block' : 'none' ?>">
          Hold Reason
          <input type="text" name="reason" value="<?= htmlspecialchars($ticket->reason ?: '') ?>">
        </label>
      </div>
      <div class="flex">
        <label>Location
          <select name="location_id" required>
            <?php foreach ($locations as $location): ?>
              <option value="<?= $location->id ?>" <?= $ticket->location_id == $location->id ? ' selected' : '' ?>>
                <?= htmlspecialchars($location->name) ?>
              </option>
            <?php endforeach;
            if (\count($locations) === 0): ?>
              <option value="" disabled>No locations available</option>
            <?php endif; ?>
          </select>
        </label>
        <label>Category
          <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category->id ?>" <?= $ticket->category_id == $category->id ? ' selected' : '' ?>>
                <?= htmlspecialchars($category->name) ?>
              </option>
            <?php endforeach;
            if (\count($categories) === 0): ?>
              <option value="" disabled>No categories available</option>
            <?php endif; ?>
          </select>
        </label>
      </div>
      <div class="grid">
        <div>
          <label>Urgency</label>
          <div role="group">
            <?php foreach (\App\Data\Tickets::URGENCY_LEVELS as $level => $label): ?>
              <label role="button" class="btn btn-primary">
                <input name="urgency" type="radio" value="<?= $level ?>" hidden <?= $ticket->urgency == $level ? 'checked' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="bottom-spacing">
      <label style="margin-bottom:.25rem">Notes</label>
      <?php foreach ($ticket->notes as $note): ?>
        <article>
          <header class="bottom-spacing">
            <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($note->created_by_display_name) ?></span>
            <small class="float-right"><?= date(DATE_FORMAT, $note->created_at) ?></small>
          </header>
          <p class="bottom-clear"><?= htmlspecialchars($note->content) ?></p>
        </article>
      <?php endforeach; ?>
      <textarea name="note" placeholder="Add a note..."></textarea>
    </div>
  </div>
</form>
<hr>
<small>
  Created by
  <?= htmlspecialchars($ticket->created_by_display_name) ?> on
  <?= date(DATE_FORMAT, $ticket->created_at) ?><br>
  <?php if ($ticket->updated_at): ?>
    Last updated by
    <?= htmlspecialchars($ticket->updated_by_display_name) ?> on
    <?= date(DATE_FORMAT, $ticket->updated_at) ?>
  <?php endif; ?>
</small>
<?php if ($is_admin): ?>
  <dialog id="deleteModal">
    <article>
      <header>
        <b>Confirm Delete</b>
      </header>
      <form id="deleteForm" method="post" action="/tickets/<?= $ticket->id ?>/delete">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <p>Are you sure you want to delete this ticket?</p>
        <div class="flex">
          <button type="button" onclick="deleteModal.close()" class="btn btn-secondary bottom-clear"><i
              class="bi bi-x-circle"></i> Cancel</button>
          <button type="submit" class="btn btn-error bottom-clear"><i class="bi bi-trash"></i> Delete</button>
        </div>
      </form>
    </article>
  </dialog>
<?php endif; ?>
<script type="module">
  import { attachSubmitLoading } from '/assets/js/scripts.js';
  attachSubmitLoading(document.getElementById('ticketForm'));
  document.getElementById('deleteBtn')?.addEventListener('click', () => {
    attachSubmitLoading(document.getElementById('deleteForm'))
    document.getElementById('deleteModal').showModal();
  });
</script>