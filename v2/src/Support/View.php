<?php

declare(strict_types=1);

namespace V2\Support;

final class View
{
    public function render(string $view, array $data = [], string $layout = 'student'): void
    {
        $viewPath = APP_ROOT . '/src/Views/pages/' . $view . '.php';
        $layoutPath = APP_ROOT . '/src/Views/layouts/' . $layout . '.php';

        if (!is_file($viewPath) || !is_file($layoutPath)) {
            throw new \RuntimeException('View or layout not found.');
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        $content = (string) ob_get_clean();

        include $layoutPath;
    }
}
