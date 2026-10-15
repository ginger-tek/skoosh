<?php

if (preg_match('#\.(?:png|jpe?g|svg|woff2?|json)$#', $_SERVER['REQUEST_URI']))
  return false;

require_once '../src/App.php';
