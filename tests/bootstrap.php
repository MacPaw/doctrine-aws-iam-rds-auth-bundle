<?php

declare(strict_types=1);

use Symfony\Component\ErrorHandler\ErrorHandler;

set_exception_handler([new ErrorHandler(), 'handleException']);
