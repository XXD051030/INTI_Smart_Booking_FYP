<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

\V2\Support\Auth::logoutStudent();
flash('message', 'You have been signed out.');
redirect('login.php');
