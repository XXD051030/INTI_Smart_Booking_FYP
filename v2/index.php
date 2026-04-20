<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

redirect(current_student() ? 'general.php' : 'login.php');
