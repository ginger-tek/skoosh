<form id="loginForm" method="post" action="/login<?= isset($next) ? "?next=" . urlencode($next) : '' ?>"
  style="margin-inline:auto;max-inline-size:320px">
  <input type="hidden" name="csrf" value="<?= $csrf ?>">
  <?php if ($error ?? false): ?>
    <article class="alert alert-error" role="alert">
      <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
    </article>
  <?php endif; ?>
  <label>Username
    <input name="username" autofocus autocapitalize="off" autocomplete="username" required>
  </label>
  <label>Password
    <div role="group">
      <input name="password" type="password" autocomplete="password" required>
      <button class="btn btn-primary" type="button" id="peek-password"><i class="bi bi-eye"></i></button>
    </div>
  </label>
  <button type="submit" class="btn btn-primary">Login</button>
</form>
<script type="module">
  import { attachPeekPassword, attachSubmitLoading, attachRequiredInputs } from '/assets/js/scripts.js';
  attachPeekPassword(document.getElementById('peek-password'));
  attachSubmitLoading(document.getElementById('loginForm'));
  attachRequiredInputs();
</script>