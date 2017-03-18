<?php

/*
 * Copyright (C) 2016 Billie Thompson
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.md file for details.
 */

namespace PurpleBooth\JaneOpenApiAutogenerate;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Class SwaggerApiPlugin.
 */
class SwaggerApiPlugin implements PluginInterface
{
    /**
     * Add the installer plugin.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new SwaggerApiInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
