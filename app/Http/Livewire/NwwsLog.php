<?php

namespace App\Http\Livewire;

use Livewire\Component;

class NwwsLog extends Component
{
    public $lastTenLogLines = 'Loading...<br /><br /><br /><br /><br /><br /><br /><br /><br />';

    public function getLogLines()
    {
        $logFile = base_path() . '/storage/logs/nwws-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $this->lastTenLogLines = shell_exec('tail ' . $logFile);
        } else {
            $this->lastTenLogLines = 'The file ' . $logFile . ' was not found.<br /><br /><br /><br /><br /><br /><br /><br /><br />';
        }
    }

    public function render()
    {
        return view('livewire.nwws-log');
    }
}
