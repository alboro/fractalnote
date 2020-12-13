<?php
/**
 * NextCloud - fractalnote
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

// @todo
// before this: automatically add new mimetype to config/mimetypes.json
// refactor this: show, that it is inspired by \OC\Core\Command\Maintenance\Mimetype\UpdateDB::execute, add comments

class AddMimetypeToFilecache implements IRepairStep {
  public function __construct(IDBConnection $connection) {}

  public function getName() {
    return "Add custom mimetype to filecache";
  }

  public function run(IOutput $output) {
    $mimeTypeDetector = \OC::$server->getMimeTypeDetector();
    $mimeTypeLoader = \OC::$server->getMimeTypeLoader();

    // Register custom mimetype
    $mimeTypeDetector->getAllMappings();
    $mimeTypeDetector->registerType('ctb', 'application/cherrytree-ctb');

    // And update the filecache for it.
    $mimetypeId = $mimeTypeLoader->getId('application/cherrytree-ctb');
    $mimeTypeLoader->updateFilecache('%.ctb', $mimetypeId);

    $output->info("Added custom mimetype to filecache.");
  }
}
