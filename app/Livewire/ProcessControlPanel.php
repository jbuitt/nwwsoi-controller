<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\DaemonTrait;

class ProcessControlPanel extends Component
{
    use DaemonTrait;

    public $processStatus = 'Loading..';
    // public $processResult = '';
    public $buttonLabel = 'Start';
    // public $buttonClasses = 'bg-green-500 hover:bg-green-700';
    public $buttonIconClass = '';

    protected $listeners = ['mrllUpdated'];

    public function toggleProcess()
    {
        $this->disabledFlag = true;
        $this->buttonClasses = 'bg-gray-500 hover:bg-gray-700';
        if ($this->processStatus === 'Stopped') {
            // Start process
            $results = $this->performDeamonCommand('start');
            $this->processStatus = $results['details']['status'];
            // $this->processResult = $results['details']['result'];
            $this->buttonLabel = 'Stop';
            // $this->buttonClasses = 'bg-red-500 hover:bg-red-700';
            $this->buttonIconClass = 'fa-stop';
        } elseif ($this->processStatus === 'Running') {
            // Stop process
            $results = $this->performDeamonCommand('stop');
            $this->processStatus = $results['details']['status'];
            // $this->processResult = $results['details']['result'];
            $this->buttonLabel = 'Start';
            // $this->buttonClasses = 'bg-green-500 hover:bg-green-700';
            $this->buttonIconClass = 'fa-play';
        } else {
            $this->processStatus = 'Error';
            // $this->processResult = 'Invalid config command';
        }
        $this->dispatch('commandFinished');
    }

    /**
     * Initialize properties
     */
    public function mount()
    {
        $results = $this->performDeamonCommand('status');
        $this->processStatus = $results['details']['status'];
        // $this->processResult = $results['details']['result'];
        $this->buttonLabel = $this->processStatus === 'Running' ? 'Stop' : 'Start';
        // $this->buttonClasses = $this->processStatus === 'Running' ? 'bg-red-500 hover:bg-red-700' : 'bg-green-500 hover:bg-green-700';
        $this->buttonIconClass = $this->processStatus === 'Running' ? 'fa-stop' : 'fa-play';
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.process-control-panel');
    }

}
