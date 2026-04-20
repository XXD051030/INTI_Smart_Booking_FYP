<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

\V2\Support\Auth::logoutAdmin();
flash('message', 'Admin session closed.');
redirect('admin/index.php');
