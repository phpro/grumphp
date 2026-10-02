<?php

$errors = [];
foreach (array_slice($argv, 1) as $path) {
    if (str_starts_with($path, '-')) {
        $errors[] = 'Received a file that a CLI tool would parse as an option: '.$path;
        continue;
    }

    if (!is_file($path)) {
        $errors[] = 'Received a path that is not a file: '.$path;
    }
}

if ($errors) {
    throw new RuntimeException(implode(PHP_EOL, $errors));
}
