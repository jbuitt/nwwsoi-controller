@servers(['prod1' => 'www-data@100.97.39.117', 'prod2' => 'www-data@192.168.36.13'])

@setup
    //$on_servers = ['on' => 'prod1'];
    $on_servers = ['on' => ['prod1', 'prod2'], 'parallel' => true];
    $gitlab_url = 'https://git.sm-lan.net';
    $releases_to_keep = 5;
    $releases_dir = '/var/www/nwwsoi-controller/releases';
    $persistent_dir = '/var/www/nwwsoi-controller/persistent';
    $app_dir = '/var/www/nwwsoi-controller';
    $release = date('YmdHis');
    $new_release_dir = $releases_dir . '/' . $release;
@endsetup

@story('deploy')
    download_build
    update_symlinks
    db_tasks
    post_deploy
@endstory


@task('download_build', $on_servers)
    echo 'Creating release directory..'
    [ -d {{ $new_release_dir }} ] || mkdir -p {{ $new_release_dir }}
    echo 'Changing directory to new release directory..'
    cd {{ $new_release_dir }}
    echo 'Downloading build artifacts..'
    curl --progress-bar --header 'PRIVATE-TOKEN: {{ $token }}' {{ $gitlab_url }}/api/v4/projects/{{ $project }}/jobs/{{ $job }}/artifacts --output artifacts.zip
    echo 'Extracting build artifacts into {{ $new_release_dir }}..'
    unzip -qq artifacts.zip
    rm -f artifacts.zip
@endtask

@task('update_symlinks', $on_servers)
    echo 'NWWS-OI Controller - Linking storage directory..'
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $persistent_dir }}/storage {{ $new_release_dir }}/storage

    echo 'NWWS-OI Controller - Linking .env file..'
    ln -nfs {{ $persistent_dir }}/.env {{ $new_release_dir }}/.env
@endtask

@task('db_tasks', ['on' => 'prod1'])
    cd {{ $new_release_dir }}/
    echo 'NWWS-OI Controller - Running database migrations..'
    php artisan migrate --seed --force

    echo 'NWWS-OI Controller - Running Library database migrations..'
    php artisan migrate --seed --force --database=library --path database/migrations/library --class=LibraryTablesSeeder

    echo 'NWWS-OI Controller - Creating Admin User.. (if not already created)'
    php artisan db:seed 'Database\Seeders\AdminUserSeeder' --force
@endtask

@task('post_deploy', $on_servers)
    cd {{ $new_release_dir }}/

    echo 'NWWS-OI Controller - Creating storage link..'
    php artisan storage:link

    echo 'NWWS-OI Controller - Linking current release..'
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current

    echo "NWWS-OI Controller - Restart Queue Worker.."
    php artisan queue:restart

    echo "NWWS-OI Controller - Clearing bootstrapped files.."
    php artisan optimize:clear

    echo 'Removing old NWWS-OI Controller releases..'
    NUM_RELEASES=$(ls {{ $releases_dir }} | wc -l)
    if [[ $NUM_RELEASES -gt {{ $releases_to_keep }} ]]; then
        let NUM_RELEASES_TO_DELETE=$NUM_RELEASES-{{ $releases_to_keep }}
        for dir in $(ls {{ $releases_dir }} | head -n $NUM_RELEASES_TO_DELETE); do
            echo "Deleting {{ $releases_dir }}/$dir .."
            rm -rf {{ $releases_dir }}/$dir
        done
    else
        echo 'No old releases to remove.'
    fi

    echo 'NWWS-OI Controller - Reloading PHP process..'
    sudo /usr/bin/systemctl reload php8.2-fpm.service
@endtask
