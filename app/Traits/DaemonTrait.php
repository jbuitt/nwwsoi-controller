<?php

namespace App\Traits;

trait DaemonTrait
{
    /**
     * Checks to make sure all the required config variables are set in .env to connect to NWWS-OI
     *
     * @return array
     */
    public function checkConfig(): array
    {
        // Check to make sure all required config variables have been set
        $configVars = [
            'nwwsoi-controller.nwwsoi.username' => 'NWWSOI_USERNAME',
            'nwwsoi-controller.nwwsoi.password' => 'NWWSOI_PASSWORD',
            'nwwsoi-controller.nwwsoi.resource' => 'NWWSOI_RESOURCE',
            'nwwsoi-controller.nwwsoi.autostart' => 'NWWSOI_CONFIG_AUTOSTART',
            'nwwsoi-controller.nwwsoi.file_save_regex' => 'NWWSOI_FILE_SAVE_REGEX',
        ];
        // Loop through each config var and return error if it's not set
        foreach ($configVars as $configVar => $configVal) {
            if (is_null(config($configVar))) {
                return [
                    'statusCode' => 503,
                    'message' => 'Service Unavailable',
                    'details' => "Error: The config variable '$configVar' has not been set. Add a value for '$configVal' in your .env file and try again.",
                ];
            }
        }
        // If we got here, everything is good to go
        return [
            'statusCode' => 200,
            'message' => 'OK',
            'details' => '',
        ];
    }

    /**
    * Perform the process action
    *
    * @return array
    */
    public function executeArtisanCommand($command): array
    {
        //print "**DEBUG** \$command = $command\n";
        // Check to make sure that valid command was passed
        if (!in_array($command, ['status', 'start', 'stop', 'restart'])) {
            return array(
                'statusCode' => 400,
                'message' => 'Bad Request',
                'details' => [
                    'status' => 'Error',
                    'result' => 'Invalid command ' . $command,
                    'pid' => -1,
                ],
            );
        }
        // Perform command on config and return value
        return $this->performDeamonCommand($command);
    }

   /**
    * Perform the daemon command
    *
    * @param string $command The command to perform
    * @return array The return data
    */
    public function performDeamonCommand($command): array
    {
        switch($command) {
            case 'status':
                return $this->daemonStatus();

            case 'start':
                return $this->daemonStart();

            case 'stop':
                return $this->daemonStop();

            case 'restart':
                return $this->daemonRestart();
        }
        return array(
            'statusCode' => 400,
            'message' => 'Bad Request',
            'details' => array(
                'status' => 'Bad Action',
                'result' => '',
                'pid' => -1,
            ),
        );
    }

   /**
    * Get the daemon status
    *
    * @return array The return data
    */
    private function daemonStatus(): array
    {
        $pidFile = storage_path('logs') . '/nwws.pid';
        $pid = -1;
        exec('ps -ef | grep scripts\/run\.sh | grep bash | grep -v grep', $output);
        if (!empty($output) && file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            return array(
                'statusCode' => 200,
                'message' => 'OK',
                'details' => array(
                    'status' => 'Running',
                    'result' => "PID = $pid",
                    'pid' => $pid,
                ),
            );
        }
        return array(
            'statusCode' => 200,
            'message' => 'OK',
            'details' => array(
                'status' => 'Stopped',
                'result' => "process not found or PID file does not exist",
                'pid' => -1,
            ),
        );
    }

   /**
    * Start the daemon
    *
    * @return array The return data
    */
    private function daemonStart(): array
    {
        $pidFile = storage_path() . '/logs/nwws.pid';
        $running = FALSE;
        $pid = -1;
        // Check to see if process is already running
        exec('ps -ef | grep scripts\/run\.sh | grep bash | grep -v grep', $output);
        if (!empty($output) && file_exists($pidFile)) {
            $pid = intval(file_get_contents($pidFile));
            return array(
                'statusCode' => 409,
                'message' => 'Conflict',
                'details' => array(
                    'status' => 'Error',
                    'result' => "The process is already running",
                    'pid' => $pid,
                ),
            );
        } else {
            // Change directory to base_path
            chdir(base_path());
            // Start NWWS-OI ingester
            exec('./artisan nwwsoi-controller:daemon:run-ingester >storage/logs/nwws-output.log 2>&1 &', $output, $retval);
            // Check return value
            if ($retval !== 0) {
                return array(
                    'statusCode' => 500,
                    'message' => 'Server Error',
                    'details' => array(
                        'status' => 'Error',
                        'result' => "The process failed to start: " . implode(', ', $output),
                        'pid' => -1,
                    ),
                );
            }
            // Loop for 10 seconds or until process starts
            for ($i=0; $i<10; $i++) {
                $results = $this->daemonStatus();
                if ($results['details']['status'] === 'Running') {
                    $pid = $results['details']['pid'];
                    $running = TRUE;
                    break;
                }
                sleep(1);
            }
            // Return result
            if ($running) {
                return array(
                    'statusCode' => 200,
                    'message' => 'OK',
                    'details' => array(
                        'status' => 'Running',
                        'result' => "PID = $pid",
                        'pid' => $pid,
                    ),
                );
            } else {
                return array(
                    'statusCode' => 500,
                    'message' => 'Server Error',
                    'details' => array(
                        'status' => 'Error',
                        'result' => "The process did not start after 10 seconds",
                        'pid' => -1,
                    ),
                );
            }
        }
    }

   /**
    * Stop the daemon
    *
    * @return array The return data
    */
    private function daemonStop(): array
    {
        $pidFile = storage_path() . '/logs/nwws.pid';
        // Before attempting to stop, make sure process is running
        $running = TRUE;
        exec('ps -ef | grep scripts\/run\.sh | grep bash | grep -v grep', $output1);
        if (empty($output1) || !file_exists($pidFile)) {
            return array(
                'statusCode' => 409,
                'message' => 'Conflict',
                'details' => array(
                    'status' => 'Error',
                    'result' => "Process is not running or PID file does not exist",
                    'pid' => -1,
                ),
            );
        } else {
            // Process is running and pid file exists, attempt to stop it
            $pid = file_get_contents($pidFile);
            exec('kill -INT ' . $pid);
            // Wait until process stops and PID goes away
            for ($i=0; $i<10; $i++) {
                // Sleep for 1 second
                sleep(1);
                // Check for running process
                $results = $this->daemonStatus();
                if ($results['details']['status'] === 'Stopped') {
                    $running = FALSE;
                    break;
                }
            }
        }
        // If there are processes still running, force them to quit
        exec('killall nwws.py >/dev/null 2>&1');
        if (file_exists(storage_path() . '/logs/nwws.pid')) {
            unlink(storage_path() . '/logs/nwws.pid');
        }
        $running = FALSE;
        // Return
        if (!$running) {
            return array(
                'statusCode' => 200,
                'message' => 'OK',
                'details' => array(
                    'status' => 'Stopped',
                    'result' => "",
                    'pid' => -1,
                ),
            );
        }
        return array(
            'statusCode' => 500,
            'message' => 'Server Error',
            'details' => array(
                'status' => 'Error',
                'result' => "The process did not stop after 10 seconds",
                'pid' => -1,
            ),
        );
    }

   /**
    * Restart the daemon
    *
    * @return array The return data
    */
    private function daemonRestart(): array
    {
        $this->daemonStop();
        sleep(1);
        return $this->daemonStart();
    }
}
