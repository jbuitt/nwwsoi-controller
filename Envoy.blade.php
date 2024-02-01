@servers(['prod1' => 'www-data@192.168.36.220', 'prod2' => 'www-data@X.X.X.X'])

@setup
    $on_servers = ['on' => 'prod1'];
    // $on_servers = ['on' => ['prod1', 'prod2'], 'parallel' => true];
    $gitlab_url = 'https://git.sm-lan.net';
    $num_controllers = 1;
    $releases_to_keep = 5;
    $release = date('YmdHis');
@endsetup

@story('deploy')
    download_build
    setup_new_env
    shutdown_old_docker
    startup_new_docker
    clean_up
@endstory

@task('download_build', $on_servers)
    @for ($i=1; $i<=$num_controllers; $i++)
        echo 'NWWS-OI Controller - Creating release directory (if it does not already exist)..'
        [ -d "/var/www/nwwsoi-controller{{ $i }}/releases/{{ $release }}" ] || mkdir -p /var/www/nwwsoi-controller{{ $i }}/releases/{{ $release }}/
        echo 'NWWS-OI Controller - Changing directory to new release directory..'
        cd /var/www/nwwsoi-controller{{ $i }}/releases/{{ $release }}/
        echo 'NWWS-OI Controller - Downloading build artifacts..'
        curl --progress-bar --header 'PRIVATE-TOKEN: {{ $token }}' {{ $gitlab_url }}/api/v4/projects/{{ $project }}/jobs/{{ $job }}/artifacts --output /tmp/artifacts.zip
        echo 'NWWS-OI Controller - Extracting build artifacts into /var/www/nwwsoi-controller{{ $i }}/releases/{{ $release }}/..'
        unzip -qq /tmp/artifacts.zip >/dev/null 2>&1 || echo "Error: Artifacts file could not be unzipped."; exit 1
        rm -f /tmp/artifacts.zip
    @endfor
@endtask

@task('setup_new_env', $on_servers)
    @for ($i=1; $i<=$num_controllers; $i++)
        echo 'NWWS-OI Controller - Changing directory to new release directory..'
        cd /var/www/nwwsoi-controller{{ $i }}/releases/{{ $release }}/

        echo "NWWS-OI Controller - Creating .env file.."
        cp /var/www/nwwsoi-controller{{ $i }}/persistent/.env .env

        echo "NWWS-OI Controller - Creating sail.env file.."
        cp /var/www/nwwsoi-controller{{ $i }}/sail.env sail.env

        echo "NWWS-OI Controller - Creating docker-compose.yml file.."
        cp /var/www/nwwsoi-controller{{ $i }}/docker-compose.yml docker-compose.yml
    @endfor
@endtask

@task('shutdown_old_docker', $on_servers)
    @for ($i=1; $i<=$num_controllers; $i++)
        echo 'NWWS-OI Controller - Changing directory to current directory..'
        cd /var/www/nwwsoi-controller{{ $i }}/current{{ $i }}/

        echo 'NWWS-OI Controller - Shutting down current Docker containers..'
        source sail.env
        docker compose down

        cd /var/www/nwwsoi-controller{{ $i }}/

        echo 'NWWS-OI Controller - Replace current release symlink..'
        ln -nfs /var/www/nwwsoi-controller{{ $i }}/releases/{{ $release}} /var/www/nwwsoi-controller{{ $i }}/current{{ $i }}
    @endfor
@endtask

@task('startup_new_docker', $on_servers)
    @for ($i=1; $i<=$num_controllers; $i++)
        echo 'NWWS-OI Controller - Changing directory to current directory..'
        cd /var/www/nwwsoi-controller{{ $i }}/current{{ $i }}/

        echo 'NWWS-OI Controller - Starting new Docker containers..'
        source sail.env
        docker compose up -d

        # Check to make sure Laravel API is up and responding to requests
        while true; do
            RESULTS=$(curl -sf http://127.0.0.1:${APP_PORT}/api/status || echo '{"statusCode":503,"message":"Service Unavailable","details":[]}')
            # echo "\$RESULTS = #$RESULTS#"
            if [[ $(echo $RESULTS | jq -r .statusCode) == "200" ]]; then
                break
            fi
            sleep 1
        done

        echo 'NWWS-OI Controller - Running database migrations..'
        docker exec current{{ $i }}-laravel.test-1 ./artisan migrate --seed --force --isolated
    @endfor
@endtask

@task('clean_up', $on_servers)
    @for ($i=1; $i<=$num_controllers; $i++)
        echo 'NWWS-OI Controller - Changing directory to current directory..'
        cd /var/www/nwwsoi-controller{{ $i }}/current{{ $i }}/

        echo "NWWS-OI Controller - Clearing bootstrapped files.."
        docker exec current{{ $i }}-laravel.test-1 ./artisan optimize:clear

        echo "NWWS-OI Controller - Restart Queue Worker.."
        docker exec current{{ $i }}-laravel.test-1 ./artisan queue:restart

        echo 'NWWS-OI Controller - Removing old releases..'
        NUM_RELEASES=$(ls /var/www/nwwsoi-controller{{ $i }}/releases/ | wc -l)
        if [[ $NUM_RELEASES -gt {{ $releases_to_keep }} ]]; then
            let NUM_RELEASES_TO_DELETE=$NUM_RELEASES-{{ $releases_to_keep }}
            for dir in $(ls /var/www/nwwsoi-controller{{ $i }}/releases/ | head -n $NUM_RELEASES_TO_DELETE); do
                echo "NWWS-OI Controller - Deleting /var/www/nwwsoi-controller{{ $i }}/releases/$dir .."
                rm -rf /var/www/nwwsoi-controller{{ $i }}/releases/$dir
            done
        else
            echo 'NWWS-OI Controller - No old releases to remove.'
        fi
    @endfor
@endtask