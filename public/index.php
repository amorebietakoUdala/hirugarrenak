<?php

use App\Kernel;

// framework.yaml, framework.session.name doesn't work on Symfony 7.2 so I hardcoded here
// session_name('PHPSESSION_hirugarrenak');
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
