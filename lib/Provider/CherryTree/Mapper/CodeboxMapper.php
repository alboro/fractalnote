<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Provider\CherryTree\Mapper;

use OCA\FractalNote\Provider\CherryTree\Entity\Codebox;
use OCP\IDBConnection;

/**
 * @deprecated
 */
class CodeboxMapper  extends AbstractOffsetMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'codebox', Codebox::class);
    }
}