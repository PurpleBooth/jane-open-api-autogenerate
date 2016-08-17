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
    const GENERATED_DIRECTORY            = "generated";

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


    /**
     * { @inheritdoc }
     */
    protected function updateCode(PackageInterface $initial, PackageInterface $target)
    {
        $this->removeCode($initial);
        $this->installCode($target);
    }

    /**
     * { @inheritdoc }
     */
    protected function removeCode(PackageInterface $package)
    {
        $initialDownloadPath = $this->getInstallPath($package);
        $this->removeDirectory($initialDownloadPath);
    }

    /**
     * @param string $downloadPath
     */
    private function removeDirectory($downloadPath)
    {
        if (!(new SplFileInfo($downloadPath))->isDir()) {
            return;
        }

        $directory = new RecursiveDirectoryIterator($downloadPath);
        $iterator  = new RecursiveIteratorIterator(
            $directory,
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

    /**
     * { @inheritdoc }
     */
    protected function installCode(PackageInterface $package)
    {
        $downloadPath = $this->getInstallPath($package);
        $this->generateSwaggerClient(
            $package,
            $downloadPath . DIRECTORY_SEPARATOR . self::GENERATED_DIRECTORY
        );
    }

    /**
     * Generate the client for a package
     *
     * @param PackageInterface $package
     * @param string           $downloadPath
     */
    protected function generateSwaggerClient(PackageInterface $package, $downloadPath)
    {
        $janeOpenApi = JaneOpenApi::build();

        $extra             = $package->getExtra();
        $namespace         = $extra[ self::EXTRA_KEY_NAMESPACE ];
        $openApiSchemaFile = $extra[ self::EXTRA_KEY_SCHEMA_FILE ];

        if (isset($extra[ self::EXTRA_KEY_ENVIRONMENT_VARIABLE ])) {
            $envVariableName = $extra[ self::EXTRA_KEY_ENVIRONMENT_VARIABLE ];
            $envVariable     = getenv($envVariableName);

            if ($envVariable) {
                $openApiSchemaFile = $envVariable;
            }
        }

        $vendorSchemaPath = implode(DIRECTORY_SEPARATOR, [$downloadPath, '..', $openApiSchemaFile]);

        if (file_exists($vendorSchemaPath)) {
            $openApiSchemaFile = $vendorSchemaPath;
        }

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

        $files = $janeOpenApi->generate($openApiSchemaFile, $namespace, $downloadPath);
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
}
