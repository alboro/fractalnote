<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service;

use OCP\IRequest;
use OC\Files\Filesystem;
use OCA\FractalNote\Service\Exception\NotFoundException;
use OCA\FractalNote\Provider\NothingProvider;
use OCA\FractalNote\Provider\CherryTree\CherryTreeProvider;

class ProviderFactory
{
    const REQUEST_KEY_CHERRYTREE = 'f';

    /**
     * @param IRequest $request
     */
    public function createProviderByRequest(IRequest $request): ?CherryTreeProvider
    {
        $paramKeys = array_keys($request->getParams());
        try {
            foreach ($this->supportedProviders() as $possibleProvider) {
                if (in_array($possibleProvider, $paramKeys, true)) {
                    return $this->createProvider($possibleProvider, (string) $request->getParam($possibleProvider));
                }
            }
        } catch (NotFoundException) {
        }
    }

    public function createDefaultProvider()
    {
        return new NothingProvider();
    }

    private function supportedProviders()
    {
        return [
            self::REQUEST_KEY_CHERRYTREE,
        ];
    }

    /**
     * @param $providerKey
     * @param $filesystemPathToStructure
     *
     * @return \OCA\FractalNote\Provider\CherryTree\CherryTreeProvider
     *
     * @throws NotFoundException
     */
    private function createProvider($providerKey, string $filesystemPathToStructure)
    {
        switch ($providerKey) {
            case self::REQUEST_KEY_CHERRYTREE:
                $instance = new CherryTreeProvider(Filesystem::getView(), $filesystemPathToStructure);
                break;
        }
        return $instance;
    }
}