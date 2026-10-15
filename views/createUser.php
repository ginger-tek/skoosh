<form id="createUserForm" method="post" action="/users">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <header>
    <h4>Create New User</h4>
  </header>
  <div class="alert alert-info" role="alert">
    A default password will be assigned to the new user, which they will be required to change upon first login.
  </div>
  <div class="grid">
    <label>Username
      <input type="text" name="username" autocapitalize="none" required>
    </label>
    <label>Display Name
      <input type="text" name="display_name" required>
    </label>
    <label>Role
      <?php if ($user->role == 'Admin' && $user->id == $current_user->id): ?>
        <input type="text" name="role" value="<?= $user->role ?>" readonly>
      <?php else: ?>
        <select name="role" required>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role ?>" <?= $user->role === $role ? 'selected' : '' ?>><?= $role ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </label>
  </div>
  <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i>Submit</button>
</form>
<script type="module">
  import { attachSubmitLoading, attachRequiredInputs } from '/assets/js/scripts.js';
  attachSubmitLoading(document.getElementById('createUserForm'));
  attachRequiredInputs();
</script>