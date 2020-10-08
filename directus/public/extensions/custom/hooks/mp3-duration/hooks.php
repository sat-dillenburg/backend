<?php

require __DIR__ . '/wapmorgan/mp3info/src/Mp3Info.php';

use wapmorgan\Mp3Info\Mp3Info;
use Directus\Application\Application;

function setDuration(\Directus\Hook\Payload $payload) {
  $fileData = $payload->getData();

  $mimeTypes = ['audio/mp3', 'audio/mpeg'];
  $isMp3File = in_array($fileData['type'], $mimeTypes);

  if ($isMp3File) {
    $instance = Application::getInstance();
    $config = $instance->getConfig();

    $storagePath = $config->get('storage.root_url');
    $fileName = $payload->get('filename_disk');
    $filePath = '.' . DIRECTORY_SEPARATOR . $storagePath . DIRECTORY_SEPARATOR . $fileName;

    $audio = new Mp3Info($filePath);
    $payload->set('duration', floor($audio->duration));
  }

  return $payload;
}

return [
  'filters' => [
    'item.update.directus_files:before' => function (\Directus\Hook\Payload $payload) {
      return setDuration($payload);
    },
    'item.create.directus_files:before' => function (\Directus\Hook\Payload $payload) {
      return setDuration($payload);
    },
  ]
];
