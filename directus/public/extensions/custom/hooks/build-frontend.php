<?php

function triggerGithubWorkflow() {
  $GITHUB_TOKEN = '';
  $client = new \GuzzleHttp\Client();

  $data = [
    'event_type' => 'build-interim',
  ];

  $client->request(
    'POST',
    'https://api.github.com/repos/sat-dillenburg/frontend/dispatches',
    [
      'json' => $data,
      'headers' => [
        'User-Agent' => 'directus',
        'Authorization' => 'token ' . $GITHUB_TOKEN,
      ],
    ]
  );
}

return [
  'actions' => [
    'item.create.pages' => function () {
      return triggerGithubWorkflow();
    },
    'item.create.events' => function () {
      return triggerGithubWorkflow();
    },
    'item.update.sat_interim' => function () {
      return triggerGithubWorkflow();
    },
    'item.update.pages' => function () {
      return triggerGithubWorkflow();
    },
    'item.update.events' => function () {
      return triggerGithubWorkflow();
    },
  ]
];
