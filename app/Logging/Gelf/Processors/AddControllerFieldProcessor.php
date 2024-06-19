<?php

namespace Hedii\LaravelGelfLogger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class AddControllerFieldProcessor implements ProcessorInterface
{
    /**
     * Add "controller" field
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        $context['controller'] = config('logging.channels.gelf.controller_name');

        return $record->with(context: $context);
    }
}
