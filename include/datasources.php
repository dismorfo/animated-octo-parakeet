<?php

/**
 * Search.
 */
function search($config) {

  $items = [];

  $endpoint = $_ENV['discovery'];

  $viewer = $_ENV['viewer'];

  $app = $_ENV['APP_ROOT'];

  if ($config['start'] == 1) {
    $start = 0;
  } else {
    $start = $config['start'];
  }

  $bundles = [];

  if ($config['books']) {
    $bundles[] = 'bundle:dlts_book';
  }

  if ($config['photos']) {
    $bundles[] = 'bundle:dlts_photo_set';
  }

  if ($config['maps']) {
    $bundles[] = 'bundle:dlts_map';
  }

  if ($config['clips']) {
    $bundles[] = 'bundle:dlts_clip';
  }

  if ($config['playlists']) {
    $bundles[] = 'bundle:dlts_playlist';
  }

  $query = http_build_query(
    [
      'wt' => 'json',
      'query' => $config['query'],
      'rows' => $config['limit'],
      'start' => $start,
      'fq' => '(' . implode(' OR ', $bundles) . ')',
    ]
  );

  $query .= '&fq=(ss_language:en OR ss_language:und)';

  if (isset($config['collection'])) {
    $query .= '&fq=sm_collection_identifier:' . $config['collection'];
  }

  $query .= '&fl=' . implode(',', [
    'entity_id',
    'bundle',
    'sm_field_identifier',
    'ss_language',
    'ss_title_long',
    'ss_type',
    'ss_identifier',
    'ss_noid',
    'sm_collection_label',
    'sm_collection_code',
    'sm_collection_partner_code',
    'sm_collection_partner_label',
    'ss_thumbnail',
  ]);

  $query .= "&fq=ss_noid:['' TO *]";

  // For later on.
  // $query .= '&facet=true&facet.field=bundle_name&facet.field=sm_collection_label&facet.field=sm_publisher&facet.field=iass_pubyear&facet.field=ss_publication_location&facet.field=sm_subject_label&facet.field=sm_vid_Terms';

  $request = Requests::get("$endpoint?$query");

  if (
    $request->success &&
    $request->status_code === 200
  ) {

    $body = json_decode($request->body);
    
    $response = isset($body->response) ? $body->response : $body->docs;
    
    foreach ($response->docs as $doc) {
      $bundle = bundle($doc->bundle);
      switch ($bundle) {
        case 'books':
        case 'maps':
          $identifier = $doc->sm_field_identifier[0];
          $noid = $doc->ss_noid;
          $items[] = [
            'nid' => $doc->entity_id,
            'noid' => $doc->ss_noid,
            'path' => "$app/viewer/$noid",
            'type' => $bundle,
            'handle' => $doc->ss_handle,
            'thumbnail' => "$viewer/api/image/$bundle/$identifier/1/full/70,/0/default.jpg",
            'identifier' => $identifier,
            'title' => $doc->ss_title_long,
            'collections' => [
              'code' => $doc->sm_collection_code[0],
              'label' => $doc->sm_collection_label[0],
            ],
            'partners' => [
              'code' => $doc->sm_collection_partner_code[0],
              'label' => $doc->sm_collection_partner_label[0],
            ],
            'language' => $doc->ss_language,
          ];
          break;

        case 'photos':
          $identifier = $doc->ss_identifier;
          $noid = $doc->ss_noid;
          $items[] = [
            'nid' => $doc->entity_id,
            'language' => $doc->ss_language,
            'title' => $doc->ss_title_long,
            'identifier' => $identifier,
            'noid' => $doc->ss_noid,
            'path' => "$app/viewer/$noid",
            'type' => $bundle,
            'handle' => $doc->ss_handle,
            'thumbnail' => "$viewer/api/image/$bundle/$identifier/1/full/70,/0/default.jpg",
            'collections' => [
              'code' => $doc->sm_collection_code[0],
              'label' => $doc->sm_collection_label[0],
            ],
            'partners' => [
              'code' => $doc->sm_collection_partner_code[0],
              'label' => $doc->sm_collection_partner_label[0],
            ],
          ];
          break;

        case 'playlists':
          if (isset($doc->ss_type)) {
            $type = $doc->ss_type;
          } else {
            $type = $bundle;
          }
          $noid = $doc->ss_noid;
          $items[] = [
            'nid' => $doc->entity_id,
            'path' => "$app/player/$noid",
            'language' => $doc->ss_language,
            'title' => $doc->ss_title_long,
            'identifier' => $doc->ss_identifier,
            'noid' => $doc->ss_noid,
            'type' => $type,
            'handle' => $doc->ss_handle,
            'thumbnail' => $doc->ss_thumbnail,
            'collections' => [
              'code' => $doc->sm_collection_code[0],
              'label' => $doc->sm_collection_label[0],
            ],
            'partners' => [
              'code' => $doc->sm_collection_partner_code[0],
              'label' => $doc->sm_collection_partner_label[0],
            ],
          ];
          break;

          case 'clips':
            if (isset($doc->ss_type)) {
              $type = $doc->ss_type;
            } else {
              $type = $bundle;
            }
            $noid = $doc->ss_noid;
            $items[] = [
              'nid' => $doc->entity_id,
              'path' => "$app/player/$noid",
              'language' => $doc->ss_language,
              'title' => $doc->ss_title_long,
              'identifier' => $doc->ss_identifier,
              'noid' => $doc->ss_noid,
              'type' => $type,
              'thumbnail' => $doc->ss_thumbnail,
              'handle' => $doc->ss_handle,
              'collections' => [
                'code' => $doc->sm_collection_code[0],
                'label' => $doc->sm_collection_label[0],
              ],
              'partners' => [
                'code' => $doc->sm_collection_partner_code[0],
                'label' => $doc->sm_collection_partner_label[0],
              ],
            ];
            break;
      }
    }
  }

  $facets = [];

  // For later.
  // foreach ($body->facet_counts->facet_fields as $key => $field) {
  //   $facets[$key] = [];
  //   foreach ($field as $i => $entry) {
  //     if ($i % 2 == 0) {
  //       $facets[$key][] = [
  //         'label' => $entry,
  //         'count' => $field[$i + 1],
  //       ];
  //     }
  //   }
  // }

  return [
    'items' => $items,
    'start' => $start,
    'limit' => $config['limit'],
    'maxPage' => ceil((int) $response->numFound / $config['limit']),
    'numFound' => (int) $response->numFound,
    'facet' => $facets,
  ];
}

/**
 * Select entity by NoId.
 */
function select_by_noid($noid, $lang = 'en', $reset_cache = true) {
  $cache = $_ENV['content_dir'] . '/noid.' . $noid . '.' . $lang . '.json';
  $item = [];
  $mediaplayer = $_ENV['mediaplayer'];
  // cache exists and no need to reset request.
  if (!$reset_cache && file_exists($cache)) {
    return json_decode(file_get_contents($cache));
  } else { // cache does not exists or to reset requested.
    $endpoint = $_ENV['discovery'];
    $query = http_build_query(
      [ 
        'wt' => 'json',
        'start' => 0,
        'fq' => "ss_noid:$noid",
      ]
    );
    $query .= "&fq=(ss_language:$lang OR ss_language:und)";
    $query .= '&fl=' . implode(',', [
      'entity_id',
      'bundle',
      'sm_field_identifier',
      'ss_language',
      'ss_title_long',
      'ss_identifier',
      'ss_noid',
      'ss_manifest',
      'ss_thumbnail',
      'sm_collection_label',
      'sm_collection_code',
      'sm_collection_identifier',
      'sm_collection_partner_identifier',
      'sm_collection_partner_code',
      'sm_collection_partner_label',
    ]);

    $request = Requests::get("$endpoint?$query");

    if (
      $request->success &&
      $request->status_code === 200
    ) {

      $body = json_decode($request->body);

      $response = isset($body->response) ? $body->response : $body->docs;
  
      $doc = $response->docs[0];
      
      $noid = $doc->ss_noid;

      $item = [
        'nid' => $doc->entity_id,
        'noid' => $doc->ss_noid,
        'manifest' => $doc->ss_manifest,
        'type' => bundle($doc->bundle),
        'identifier' => $doc->sm_field_identifier[0],
        'title' => $doc->ss_title_long,
        'iframe' => "$mediaplayer/api/v0/noid/$noid/embed",
        'collections' => (object) [
          'code' => $doc->sm_collection_code[0],
          'label' => $doc->sm_collection_label[0],
          'identifier' => $doc->sm_collection_identifier[0],
        ],
        'partners' => (object) [
          'code' => $doc->sm_collection_partner_code[0],
          'label' => $doc->sm_collection_partner_label[0],
          'identifier' => $doc->sm_collection_partner_identifier[0],
        ],
        'language' => $doc->ss_language,
      ];
      file_put_contents($cache, json_encode($item), LOCK_EX);   
      return (object) $item;
    } else {
      throw new Exception('Not found - Request failed.');
    }
  } 
  return (object) $item;  
}

function collections() {
  $collections_cache = $_ENV['content_dir'] . '/collections.json';
  if (file_exists($collections_cache)) {
    return json_decode(file_get_contents($collections_cache));
  } else {
    $media = $_ENV['mediaplayer'];
    $viewer = $_ENV['viewer'];
    $request_collections_viewer = Requests::get("$viewer/api/v1/collections");
    $request_collections_media = Requests::get("$media/api/v1/collections");
    if (
      $request_collections_viewer->success &&
      $request_collections_viewer->status_code === 200 &&
      $request_collections_media->success &&
      $request_collections_media->status_code === 200
    ) {
      $viewer_data = json_decode($request_collections_viewer->body);
      $media_data = json_decode($request_collections_media->body);
      $collections = array_merge($viewer_data->response->docs, $media_data->response->docs);
      file_put_contents($collections_cache, json_encode($collections), LOCK_EX);
      return $collections;
    } else {
      throw new Exception('Unable to request collections - Request failed.');
    }
  }
}

function footer_links() {
  return [
    'links' => [
      [
        'link' => 'https://guides.nyu.edu/using_archives/home',
        'label' => 'Using Archives & Manuscripts',
        'weigth' => 0,
      ],
      [
        'link' => 'https://nyu.qualtrics.com/jfe/form/SV_3Qw7bzL1VzG6hVA',
        'label' => 'Report Harmful Language',
        'weigth' => 1,
      ],
      [
        'link' => 'https://www.nyu.edu/footer/accessibility.html',  
        'label' => 'NYU Libraries Open Metadata Policy',
        'weigth' => 2,
      ],
      [
        'link' => 'https://www.nyu.edu/footer/accessibility.html',
        'label' => 'NYU Digital Accessibility Policy',
        'weigth' => 3,
      ],
    ],
  ];
}
