<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InstallPlugins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:install_plugins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs all plugins specified in .env';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        print "Installing plugins..\n";
        $pluginList = config('nwwsoi-controller.enabled_pan_plugins');
        if (is_null($pluginList) || empty($pluginList)) {
            print "No plugins specified, exiting.\n";
            return 0;
        }
        if (!file_exists(base_path() . '/plugins.json')) {
            print "No plugin config file found, exiting.\n";
            return 0;
        }
        $pluginConfig = @json_decode(file_get_contents(base_path() . '/plugins.json'));
        if (!$pluginConfig) {
            print "Error: Could not parse plugin config, exiting.\n";
            return 1;
        }
        // Loop through each plugin and install it
        foreach (explode(',', $pluginList) as $plugin) {
            print "Installing plugin $plugin..\n";
            if (!isset($pluginConfig[$plugin])) {
                print "Warning: No config found for $plugin, skipping.\n";
                continue;
            }
            if (!isset($pluginConfig[$plugin]['sourceType'])) {
                print "Warning: No source type found for $plugin, skipping.\n";
                continue;
            }
            if (!isset($pluginConfig[$plugin]['sourceUrl'])) {
                print "Warning: No source URL found for $plugin, skipping.\n";
                continue;
            }
            $sourceType = $pluginConfig[$plugin]['sourceType'];
            $sourceUrl = $pluginConfig[$plugin]['sourceUrl'];
            // Create a temporary directory
            chdir('/tmp/');
            $tempDir = shell_exec('/usr/bin/mktemp -d XXXXX');
            // Download file to temp dir
            $failedFlag = FALSE;
            switch ($sourceType) {
                case 'git':
                    if (preg_match('/^git@/', $sourceUrl)) {
                        print "Warning: SSH Git URLs are not supported, skipping.\n";
                        $failedFlag = TRUE;
                        break;
                    }
                    exec('/usr/bin/git clone ' . $sourceUrl . ' ' . $tempDir);
                    chdir($tempDir);
                    break;

                case 'zip':
                    chdir($tempDir);
                    exec('/usr/bin/curl -sLOJ -o output.zip ' . $sourceUrl . ' 2>/dev/null');
                    if (filesize('output.zip') === 0) {
                        print "Warning: Could not download plugin zip file, skipping.\n";
                        $failedFlag = TRUE;
                        break;
                    }
                    exec('/usr/bin/unzip -qq output.zip 2>/dev/null', $output, $resultCode);
                    if ($resultCode !== 0) {
                        print "Warning: Could not unzip plugin files, skipping.\n";
                        $failedFlag = TRUE;
                        break;
                    }
                    break;
            }
            if ($failedFlag) {
                exec('cd /tmp/ && rm -rf ' . $tempDir);
                continue;
            }
            // Check for install.sh script
            if (!file_exists('install.sh')) {
                print "Warning: No install.sh script found, skipping.\n";
                exec('cd /tmp/ && rm -rf ' . $tempDir);
                continue;
            }
            // Run install.sh script
            exec('./install.sh ' . base_path(), $output, $resultCode);
            if ($resultCode !== 0) {
                print "Error: install.sh script failed with output: " . implode($output) . "\n";
            }
            // Clean up
            chdir('/tmp/');
            exec('rm -rf ' . $tempDir);
        }
        // Done
        return 0;
    }
}
