<?php

namespace App\Services;

class Env
{
  public static function load(): void
  {
    $path = ROOT . '/.env';
    if (file_exists($path))
      foreach (parse_ini_file($path) as $k => $v)
        putenv("$k=$v");
  }
}