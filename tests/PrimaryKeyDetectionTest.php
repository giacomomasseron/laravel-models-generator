<?php

declare(strict_types=1);

use GiacomoMasseroni\LaravelModelsGenerator\Drivers\SQLite\Connector;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->databasePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lmg_'.uniqid().'.sqlite';
    touch($this->databasePath);

    config()->set('database.connections.testing', [
        'driver' => 'sqlite',
        'database' => $this->databasePath,
        'prefix' => '',
    ]);
    DB::purge('testing');
});

afterEach(function (): void {
    DB::purge('testing');
    gc_collect_cycles();
    @unlink($this->databasePath);
});

it('detects the primary key when it is not the first column', function (): void {
    DB::statement('CREATE TABLE articles (title VARCHAR(50) NOT NULL, id INTEGER PRIMARY KEY)');

    $tables = (new Connector('testing'))->listTables();

    expect($tables)->toHaveKey('articles')
        ->and($tables['articles']->primaryKey?->name)->toBe('id');
});
