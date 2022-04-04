<?php

/**
 * @file
 * home.php
 */

/**
 * Home function.
 */
function api($leaf) {
  
  $noid = filter_var($leaf[1], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
  
  $content_dir = json_decode(
    file_get_contents($_ENV['content_dir'] . '/mos_2021.json')
  );

  return $content_dir->archdesc->dsc->c[1];

}
