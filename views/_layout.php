<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skoosh</title>
  <link rel="stylesheet" href="https://unpkg.com/@digicreon/mucss/dist/mu.violet.css">
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <link rel="manifest" href="/assets/manifest.json">
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>

<body class="container">
  <header>
    <nav>
      <ul>
        <li><a href="/"><img class="logo" src="/assets/img/favicon.svg"> <b>Skoosh</b></a></li>
      </ul>
      <ul>
        <?php if ($current_user): ?>
          <li>
            <details class="dropdown">
              <summary><i class="bi bi-person-circle"></i> <?= $current_user->display_name ?></summary>
              <ul dir="rtl">
                <?php if ($is_admin): ?>
                  <li dir="ltr"><a href="/tickets"><i class="bi bi-list-task"></i> Tickets</a></li>
                  <li dir="ltr"><a href="/settings"><i class="bi bi-gear-fill"></i> Settings</a></li>
                  <li dir="ltr"><a href="/users"><i class="bi bi-people-fill"></i> Users</a></li>
                <?php endif; ?>
                <li dir="ltr"><a href="/pass-reset"><i class="bi bi-key-fill"></i> Reset Password</a></li>
                <li dir="ltr"><a style="cursor:pointer" onclick="logoutForm.requestSubmit()"><i
                      class="bi bi-box-arrow-right"></i> Logout</a></li>
              </ul>
              <form id="logoutForm" method="post" action="/logout">
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
              </form>
            </details>
          </li>
        <?php else: ?>
          <li><a href="/login"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
  <main>
    <?php include $view ?>
  </main>
  <?php if ($current_user): ?>
    <script type="module">
      import { subscribeToNotifications } from '/assets/js/scripts.js';
      subscribeToNotifications();
    </script>
  <?php endif; ?>
</body>

</html>