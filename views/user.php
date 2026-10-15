<form id="userForm" method="post" action="/users/<?= $user->id ?>">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <header class="flex spaced">
    <div>
      <small>ID: <?= $user->id ?></small>
      <h4>User Details</h4>
    </div>
    <div class="flex gap-small">
      <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Save</button>
      <?php if ($user->id != $current_user->id): ?>
        <button type="button" id="deleteBtn" class="btn btn-error"><i class="bi bi-trash"></i> Delete</button>
      <?php endif; ?>
    </div>
  </header>
  <div class="grid">
    <label>Username
      <input type="text" name="username" value="<?= $user->username ?>" autocapitalize="none" required>
    </label>
    <label>Display Name
      <input type="text" name="display_name" value="<?= $user->display_name ?>" required>
    </label>
    <label>Role
      <?php if ($user->role == 'Admin' && $user->id == $current_user->id): ?>
        <span data-tooltip="Use CLI to change role"><i class="bi bi-info-circle"></i></span>
      <?php endif; ?>
      <?php if ($user->role == 'Admin' && $user->id == $current_user->id): ?>
        <input type="text" name="role" value="<?= $user->role ?>" readonly disabled>
      <?php else: ?>
        <select name="role" required>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role ?>" <?= $user->role === $role ? 'selected' : '' ?>><?= $role ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </label>
    <label>Status
      <?php if ($user->role == 'Admin' && $user->id == $current_user->id): ?>
        <span data-tooltip="Use CLI to change status"><i class="bi bi-info-circle"></i></span>
      <?php endif; ?>
      <?php if ($user->role == 'Admin' && $user->id == $current_user->id): ?>
        <input type="text" name="status" value="<?= $user->status ?>" readonly disabled>
      <?php else: ?>
        <select name="status" required>
          <?php foreach ($statuses as $status): ?>
            <option value="<?= $status ?>" <?= $user->status === $status ? 'selected' : '' ?>><?= $status ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </label>
  </div>
</form>
<hr>
<small>
  Created by
  <?= htmlspecialchars($user->created_by_display_name) ?> on
  <?= date(DATE_FORMAT, $user->created_at) ?><br>
  <?php if ($user->updated_at): ?>
    Last updated by
    <?= htmlspecialchars($user->updated_by_display_name) ?> on
    <?= date(DATE_FORMAT, $user->updated_at) ?>
  <?php endif; ?>
</small>
<?php if ($user->id != $current_user->id): ?>
  <dialog id="deleteModal">
    <article>
      <header>
        <b>Confirm Delete</b>
      </header>
      <form id="deleteForm" method="post" action="/users/<?= $user->id ?>/delete">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <p>Are you sure you want to delete this user?</p>
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
  attachSubmitLoading(document.getElementById('userForm'));
  document.getElementById('deleteBtn')?.addEventListener('click', () => {
    attachSubmitLoading(document.getElementById('deleteForm'))
    document.getElementById('deleteModal').showModal();
  });
</script>