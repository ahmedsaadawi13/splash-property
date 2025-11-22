// FILE: /app/core/View.php
<?php

/**
 * View Class
 * Handles rendering of view templates
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class View
{
    private $viewsPath;
    private $layoutsPath;

    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../views/';
        $this->layoutsPath = __DIR__ . '/../views/layouts/';
    }

    /**
     * Render view with layout
     */
    public function render($viewPath, $data = [], $layout = 'main')
    {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include view file
        $viewFile = $this->viewsPath . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewPath}");
        }

        include $viewFile;

        // Get view content
        $content = ob_get_clean();

        // If layout is specified, wrap content in layout
        if ($layout) {
            $layoutFile = $this->layoutsPath . $layout . '.php';

            if (!file_exists($layoutFile)) {
                throw new Exception("Layout file not found: {$layout}");
            }

            ob_start();
            include $layoutFile;
            $output = ob_get_clean();

            echo $output;
        } else {
            echo $content;
        }
    }

    /**
     * Escape output
     */
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Include partial
     */
    public function partial($partialPath, $data = [])
    {
        extract($data);
        $partialFile = $this->viewsPath . 'partials/' . $partialPath . '.php';

        if (file_exists($partialFile)) {
            include $partialFile;
        }
    }
}
