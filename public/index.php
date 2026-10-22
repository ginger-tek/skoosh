<?php

require_once '../src/App.php';

?>
<!doctype html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Skoosh</title>
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" crossorigin href="https://unpkg.com/@digicreon/mucss@1.4.9/dist/mu.violet.css">
  <link rel="stylesheet" crossorigin href="https://unpkg.com/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/assets/styles.css">
  <script crossorigin src="https://unpkg.com/vue@3.5.44/dist/vue.global.js"></script>
  <script crossorigin src="https://unpkg.com/vue-router@5.4.0/dist/vue-router.global.js"></script>
  <script crossorigin src="https://unpkg.com/luxon@3.7.2/build/global/luxon.min.js"></script>
  <script type="module" src="/assets/main.js"></script>
</head>

<body>
  <div id="app" class="container">
    <main>
      <div aria-busy="true" class="fallback"></div>
    </main>
  </div>
</body>

</html>