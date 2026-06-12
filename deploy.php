<?php
declare(strict_types=1);

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/crontab.php';
require 'contrib/rsync.php';


$branch = exec('git branch --show-current');
$identityFilePath = posix_getpwnam(get_current_user())['dir'] . '/.ssh/id_rsa';
$isSandbox = $branch == 'sandbox';
$host = '129.146.80.28';
$user = 'ubuntu';
$deployPath = '/var/www/app.piskefotografia.com.br';
$environment = 'PRODUCTION';

if ($isSandbox) {
    $branch = 'sandbox';
    $deployPath = '/var/www/sandbox-app.piskefotografia.com.br';
    $environment = 'SANDBOX';
}

//Validations
if ($branch != 'sandbox' && $branch != 'main') {
    die("Deploy aborted! INVALID BRANCH!\n");
}
if (!file_exists($identityFilePath)) {
    die("Deploy aborted! You need to create the key `$identityFilePath`!\n");
}

set('keep_releases', 3);

set('writable_mode', 'acl');
set('http_user', 'www-data');

// Project name
set('application', 'Piske Fotografia');
set('repository', 'git@github.com:jonathanlourette/gestor-piskefotografia.git');
set('branch', $branch);

set('git_tty', true);

// Configuração do Rsync
set('rsync', [
    'exclude' => [
        '.git',
        '/.github',
        '/storage',
        '/vendor',
        '.gitignore',
        '/deploy.php',
        '/.env',
        '/documentation',
        '/.docker',
        '/docker-compose.yml',
        '/.idea',
        '/.env.example',
        '/README.md',
        '/AGENTS.md',
        '/.DS_Store',
    ],
    'exclude-file' => false,
    'include' => [],
    'include-file' => false,
    'filter' => [],
    'filter-file' => false,
    'filter-perdir' => false,
    'flags' => 'rz',
    'options' => ['delete'],
    'timeout' => 60,
]);
set('rsync_src', __DIR__);

add('shared_files', []);
add('shared_dirs', [
    'storage',
]);

add('writable_dirs', [
    'bootstrap/cache',
    'storage',
]);

host($host)
    ->setRemoteUser($user)
    ->setDeployPath($deployPath)
    ->setIdentityFile($identityFilePath)
    ->setForwardAgent(false)
    ->setSshMultiplexing(false);


task('environment:show', function () use ($environment) {
    print "+----------------------------------------+\n";
    print "  DEPLOYING TO $environment \n";
    print "+----------------------------------------+\n";
});

// Tarefa para remover a pasta da release que falhou
task('deploy:cleanup_failed', function () {
    if (has('release_path')) {
        $path = get('release_path');

        // Verifica se a pasta realmente existe no servidor antes de tentar apagar
        if (test("[ -d $path ]")) {
            run("rm -rf $path");
            writeln("A pasta do deploy falho foi removida: $path");
        }
    }
});

task('deploy:update_code', function () {
    invoke('rsync');
});

before('deploy:info', 'environment:show');

after('deploy:failed', 'deploy:unlock');
after('deploy:failed', 'deploy:cleanup_failed');

before('deploy:symlink', 'artisan:migrate');

after('deploy:success', function () {
    run('sudo systemctl restart apache2');
    run('sudo systemctl restart fila-gestor-piskefotografia-prod');
});

if (!$isSandbox) {
    after('deploy:success', 'crontab:sync');

    add('crontab:jobs', [
        // '* * * * * cd {{current_path}} && {{bin/php}} artisan schedule:run >> /dev/null 2>&1',
    ]);
}


