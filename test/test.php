<?php

require implode(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);

if (class_exists('\Swagger\Petstore\Resource\TestResource')) {
    echo "Classes successfully generated and able to load them via the autoloader!\n";

    exit(0);
}

echo "Unable to load classes via autoloader\n";

exit(1);
