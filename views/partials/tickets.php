<?php foreach ($tickets as $t): ?>
  <article class="ticket-item alert alert-<?= \App\Data\Tickets::URGENCY_CLASSES[$t->urgency] ?>">
    <div class="float-right badge badge-<?= \App\Data\Tickets::STATES_CLASSES[$t->state] ?>">
      <?= $t->state ?>  <?php if ($t->state == 'On Hold'): ?><span>: <?= $t->reason ?></span><?php endif; ?>
    </div>
    <small class="block">#<?= $t->id ?></small>
    <b><?= $t->summary ?></b>
    <div><?= $t->category_name ?> <i class="bi bi-arrow-right"></i> <?= $t->location_name ?></div>
    <a href="/tickets/<?= $t->id ?>" class="stretch-link"></a>
  </article>
<?php endforeach;
if (!\count($tickets)): ?>
  <div class="text-center" style="margin-top:5rem">
    <h3>All Clear!</h3>
    <p>Submit a ticket to get started</p>
  </div>
<?php endif; ?>