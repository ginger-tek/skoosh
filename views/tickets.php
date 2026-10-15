<div class="bottom-spacing flex">
  <input id="search" type="search" placeholder="Search tickets..." class="bottom-clear" autocomplete="off" autofocus>
  <a href="/tickets/create" role="button" class="btn btn-primary shrink">New</a>
</div>
<div id="empty-tickets" style="display:none">
  <p class="text-center">No tickets found by that query</p>
</div>
<?php include ROOT . '/views/partials/tickets.php'; ?>
<script type="module">
  import { attachSearchFilter } from '/assets/js/scripts.js';
  attachSearchFilter(document.getElementById('search'), '.ticket-item', document.getElementById('empty-tickets'));
</script>