<?php

/**
 * @file
 * home.php
 */

/**
 * Home function.
 */
function home($leaf) {
  
  $noid = filter_var($leaf[0], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

  try {

    // http://sites.dlib.nyu.edu/viewer/api/v1/objects?type=dlts_photo_set
    // http://192.168.0.5:8080/findingaids/ttdz0j92
    // http://192.168.0.5:8080/findingaids/xgxd28gq
    // http://192.168.0.5:8080/findingaids/djh9w5nd
    // http://192.168.0.5:8080/findingaids/3xsj40p2
    // https://stageweb1.dlib.nyu.edu/ux/findingaids/latest/tamwag/mos_2021/images/xgxd28gq/
    // https://stackoverflow.com/questions/14748750/placeholder-background-image-while-waiting-for-full-image-to-load
    // https://stackoverflow.com/questions/49983243/preloader-keeps-on-loading-and-doesnt-disappear-when-the-content-is-loaded

    $config = [];

    $viewer = $_ENV['viewer'];

    $url = "$viewer/api/v1/noid/$noid";

    $request = Requests::get($url);

    if (
      $request->success &&
      $request->status_code === 200
    ) {

      $data = json_decode($request->body);

      $identifier = $data->iiif->identifier;

      $service = $data->iiif->image->service;

      $width = 230;

      $height = 230;
      
      $resources = [];

      $content_dir = json_decode(
        file_get_contents($_ENV['content_dir'] . '/mos_2021.json')
      );      

      // https://stageweb1.dlib.nyu.edu/ux/findingaids/latest/tamwag/mos_2021/images/

      $image_count = count($data->iiif->image->items);      

      foreach ($data->iiif->image->items as $key => $image) {
        $sequence = (int) $key + 1;
        $resources[] = [
          'identifier' => $identifier,
          'noid' => $noid,
          'sequence' => $sequence,
          'url' => "$service/$identifier/$sequence/full/$width,$height/0/default.jpg",
          'width' => $width,
          'height' => $height,
        ];
      }
    }
    return [
      'template' => 'resources.list.html',
      'data' => [
        'title' => 'This is a digital object: Megan O&#39;Shea&#39;s One Resource to Rule Them All: NYU Special Collections Finding Aids',
        'resources' => $resources,
        'collection' => [
          'title' => $content_dir->eadheader->filedesc->titlestmt->titleproper,
        ],
        'imageCountLabel' => "Image set: $image_count " . ngettext('image', 'images', $image_count),
        'permalink' => 'https://hdl.handle.net/2333.1/' . $noid,
      ],
    ];
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
