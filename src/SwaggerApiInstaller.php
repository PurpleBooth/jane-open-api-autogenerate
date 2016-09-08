<?php

namespace PurpleBooth\JaneOpenApiAutogenerate;

use Composer\Composer;
use Composer\Installer\BinaryInstaller;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use Joli\Jane\OpenApi\JaneOpenApi;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SwaggerApiInstaller extends LibraryInstaller
{
    const PACKAGE_TYPE = 'swagger-api';
    const EXTRA_KEY_NAMESPACE = 'namespace';
    const EXTRA_KEY_SCHEMA_FILE = 'schema-file';
    const EXTRA_KEY_ENVIRONMENT_VARIABLE = 'environment-variable';
    const GENERATED_DIRECTORY = 'generated';
    const SCHEMA_PATH_IS_DOWNLOAD_PATTERN = '/^https?:/';

    /**
     * {@inheritdoc}
     */
    public function __construct(
        IOInterface $inputOutput,
        Composer $composer,
        $type = self::PACKAGE_TYPE,
        Filesystem $filesystem = null,
        BinaryInstaller $binaryInstaller = null
    ) {
        parent::__construct($inputOutput, $composer, $type, $filesystem, $binaryInstaller);
    }

    protected function updateCode(PackageInterface $initial, PackageInterface $target)
    {
        $this->removeCode($initial);
        $this->installCode($target);
    }

    protected function removeCode(PackageInterface $package)
    {
        // Is this a schema we download from the internet or checkout locally
        if (!$this->isSchemaToDownload($package)) {
            parent::removeCode($package);
        } else {
            $downloadPath = $this->getInstallPath($package);
            $this->removeDirectory($downloadPath);
        }
    }

    /**
     * Do we download or checkout this schema.
     *
     * @param PackageInterface $package
     *
     * @return bool
     */
    private function isSchemaToDownload(PackageInterface $package)
    {
        return preg_match(self::SCHEMA_PATH_IS_DOWNLOAD_PATTERN, $this->getInstallPath($package)) === 1;
    }

    /**
     * RM -rf in PHP.
     *
     * @param string $downloadPath
     */
    private function removeDirectory($downloadPath)
    {
        if (!(new SplFileInfo($downloadPath))->isDir()) {
            return;
        }


        $directory = new RecursiveDirectoryIterator($downloadPath);
        $iterator = new RecursiveIteratorIterator(
            -$directory,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                unlink($item->getPathname());
            }

            if ($item->isDir()) {
                rmdir($item->getPathname());
            }
        }

        rmdir($downloadPath);
    }

    protected function installCode(PackageInterface $package)
    {
        // Is this a schema we download from the internet or checkout locally
        if (!$this->isSchemaToDownload($package)) {
            parent::installCode($package);
        }

        $this->generateSwaggerClient(
            $package,
            $this->getInstallPath($package)
        );
    }

    /**
     * Generate the client for a package.
     *
     * @param PackageInterface $package
     * @param string           $downloadPath
     */
    protected function generateSwaggerClient(PackageInterface $package, $downloadPath)
    {
        $janeOpenApi = JaneOpenApi::build();

        $extra = $package->getExtra();
        $namespace = $extra[self::EXTRA_KEY_NAMESPACE];
        $openApiSchemaFile = $this->getSchemaFile($package);

        $this->io->write(
            "Generating <info>$namespace</info> from <info>$openApiSchemaFile</info>",
            true,
            IOInterface::VERBOSE
        );

        $this->io->write(
            "Writing to <info>$downloadPath</info>",
            true,
            IOInterface::VERY_VERBOSE
        );

        $generatePath = implode(DIRECTORY_SEPARATOR, [$downloadPath, self::GENERATED_DIRECTORY]);
        $files = $janeOpenApi->generate($openApiSchemaFile, $namespace, $generatePath);
        $janeOpenApi->printFiles($files, $downloadPath);

        foreach ($files as $file) {
            $this->io->write("Generated <info>{$file->getFilename()}</info>", true, IOInterface::DEBUG);
        }

        $this->io->write(
            "Generated <info>$namespace</info> from <info>$openApiSchemaFile</info>",
            true,
            IOInterface::VERBOSE
        );
    }

    /**
     * Get the schema file.
     *
     * @param PackageInterface $package
     *
     * @return string
     */
    private function getSchemaFile(PackageInterface $package)
    {
        $downloadPath = $this->getInstallPath($package);
        $extra = $package->getExtra();
        $openApiSchemaFile = $extra[self::EXTRA_KEY_SCHEMA_FILE];

        if (isset($extra[self::EXTRA_KEY_ENVIRONMENT_VARIABLE])) {
            $envVariableName = $extra[self::EXTRA_KEY_ENVIRONMENT_VARIABLE];
            $envVariable = getenv($envVariableName);

            if ($envVariable) {
                $openApiSchemaFile = $envVariable;
            }
        }

        $vendorSchemaPath = implode(DIRECTORY_SEPARATOR, [$downloadPath, $openApiSchemaFile]);

        if (file_exists($vendorSchemaPath)) {
            $openApiSchemaFile = $vendorSchemaPath;

            return $openApiSchemaFile;
        }

        return $openApiSchemaFile;
    }
}
