<?php

use App\Console\Commands\DbSyncCommand;

it('builds structure and data dump filenames next to target file', function () {
    $command = new DbSyncCommand;

    $method = new ReflectionMethod($command, 'buildSiblingFileName');
    $method->setAccessible(true);

    $structure = $method->invoke($command, '/tmp/file.sql', 'structure_');
    $data = $method->invoke($command, '/tmp/file.sql', 'data_');

    expect($structure)->toBe('/tmp/structure_file.sql');
    expect($data)->toBe('/tmp/data_file.sql');
});
