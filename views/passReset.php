<form id="passResetForm" method="post" action="/pass-reset">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <?php if ($is_new): ?>
    <article class="alert alert-warning" role="alert">
      <i class="bi bi-exclamation-triangle"></i> You must reset your password before logging in for the first time
    </article>
  <?php else: ?>
    <article class="alert alert-info" role="alert">
      <i class="bi bi-info-circle"></i> Resetting your password will automatically log you out
    </article>
  <?php endif; ?>
  <header>
    <h4>Reset Password</h4>
  </header>
  <?php if ($error ?? false): ?>
    <article class="alert alert-error" role="alert">
      <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
    </article>
  <?php endif; ?>
  <label>Current Password
    <div role="group">
      <input name="current_password" type="password" autocomplete="off" required>
      <button class="btn btn-primary" type="button" id="currentPassword"><i class="bi bi-eye"></i></button>
    </div>
  </label>
  <label>New Password
    <div role="group">
      <input name="new_password" type="password" autocomplete="off" required>
      <button class="btn btn-primary" type="button" id="newPassword"><i class="bi bi-eye"></i></button>
    </div>
  </label>
  <label>Confirm New Password
    <div role="group">
      <input name="confirm_new_password" type="password" autocomplete="off" required>
      <button class="btn btn-primary" type="button" id="confirmNewPassword"><i class="bi bi-eye"></i></button>
    </div>
  </label>
  <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Submit</button>
</form>
<script type="module">
  import { attachSubmitLoading, attachRequiredInputs, attachPeekPassword } from '/assets/js/scripts.js';
  attachPeekPassword(document.getElementById('currentPassword'));
  attachPeekPassword(document.getElementById('newPassword'));
  attachPeekPassword(document.getElementById('confirmNewPassword'));
  attachSubmitLoading(document.getElementById('passResetForm'));
  attachRequiredInputs();
</script>