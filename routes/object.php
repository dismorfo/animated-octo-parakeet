<?php

/**
 * @file
 * home.php
 */

/**
 * Home function.
 */
function object($leaf) {
  try {

    $noid = filter_var($leaf[0], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

    $sequence = filter_var($leaf[1], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
 
    $viewer = $_ENV['viewer'];

    $request = Requests::get("$viewer/api/v1/noid/$noid");

    if ($request->success && $request->status_code === 200) {

      setlocale(LC_ALL, 'en_US');

      $source = "https://dev-sites.dlib.nyu.edu/viewer/mirador/$noid?sequence=$sequence&embed=true";

      $data = json_decode($request->body);

      $identifier = $data->identifier;

      $type = $data->type . 's';

      $manifest = "$viewer/api/presentation/$type/$identifier/manifest.json";

      $image_count = count($data->iiif->image->items);

      $tilesource = $data->iiif->image->items[$sequence - 1];

      //krumo($data);

      $ead = json_decode(
        file_get_contents($_ENV['content_dir'] . '/mos_2021.json')
      ); 

      // $tilesource = implode(',', $data->iiif->image->items);

      return [
        'template' => 'object.view.html',
        'data' => [
          'collection' => [
            'title' => $ead->eadheader->filedesc->titlestmt->titleproper,
          ],
          'title' => 'View Image or Images: This is a digital object',
          'object' => [
            'label' => 'This is a digital object',
            'manifest' => $manifest,
            'tilesource' => $tilesource,
            'noid' => $noid,
            'source' => "$viewer/$type/$identifier/$sequence?embed=true",
            'sequence' => $sequence,
          ],
          'imageCountLabel' => "Image set: $image_count " . ngettext('image', 'images', $image_count),
          'pageLimit' => 1,
          'currentPage' => $sequence,
          'permalink' => 'https://hdl.handle.net/2333.1/' . $noid,
          'maxPage' => count($data->iiif->image->items),
          'pageRange' => 1,
          'start' => $start,
          'end' => $start,
        ],
      ];    
    }
    else {
      throw new Exception('Viewer request failed.');
    }
  }
  catch (Exception $e) {
    return [
      'template' => 'error.html',
      'data' => [
        'title' => 'Error',
        'body' => $e->getMessage(),
      ],
    ];
  }

}
