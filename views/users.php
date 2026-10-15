<div class="bottom-spacing grid">
  <a href="/users/create" role="button" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create User</a>
</div>
<?php foreach ($users as $user): ?>
  <article>
    <i class="bi bi-person-circle"></i>
    <b><?= $user->username ?> (<?= $user->role ?>)</b>
    <a href="/users/<?= $user->id ?>" class="stretch-link"></a>
  </article>
<?php endforeach; ?>