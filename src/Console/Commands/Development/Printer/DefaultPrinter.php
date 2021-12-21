<?php

namespace HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development\Printer;

use Nette\PhpGenerator\Printer;

class DefaultPrinter extends Printer
{
    protected $indentation = "    ";
    protected $linesBetweenProperties = 1;
    protected $linesBetweenMethods = 1;
}
