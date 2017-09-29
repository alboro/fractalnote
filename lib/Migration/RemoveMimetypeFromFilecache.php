<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Migration;

use OCP\Migration\IRepairStep;
use OCP\IDBConnection;
use OCP\Migration\IOutput;

class RemoveMimetypeFromFilecache implements IRepairStep {
  public function __construct(IDBConnection $connection) {}

  public function getName() {
    return "Remove custom mimetype from filecache";
  }

  public function run(IOutput $output) {
      $mimeTypeLoader = \OC::$server->getMimeTypeLoader();
      $mimetypeId = $mimeTypeLoader->getId('application/octet-stream');
      $mimeTypeLoader->updateFilecache('%.ctb', $mimetypeId);
      $output->info("Removed custom mimetype from filecache.");
  }
}
