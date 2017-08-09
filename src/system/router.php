<?php
$router = 'build/system/router.php';
$scriptFilename = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
$extension = pathinfo($scriptFilename, PATHINFO_EXTENSION);

// All file excepted .php file should be directly server
if ($extension !== 'php' && $_SERVER['SCRIPT_NAME'] != '/' && file_exists($scriptFilename)) {
    return false;
}

$requestedFile = 'pages/' . trim($_SERVER["REQUEST_URI"], '/');
if ($_SERVER["REQUEST_URI"] == '/' && file_exists('build/pages/index.php')) {
    ob_start();
    include 'build/pages/index.php';
    $content = ob_get_clean();
    $content = str_replace('</body>', '<script src="system/LiveReload/assets/js/livereload.js"></script></body>', $content);
    echo $content;
    return;
} else {
    if (is_dir('build/' . $requestedFile) &&
        file_exists('build/' . $requestedFile . '/index.php')
    ) {
        ob_start();
        include 'build/' . $requestedFile . '/index.php';
        $content = ob_get_clean();
        $content = str_replace('</body>', '<script src="system/LiveReload/assets/js/livereload.js"></script></body>', $content);
        echo $content;
        return;
    } elseif (file_exists('build/' . $requestedFile . '.php')) {
        ob_start();
        include 'build/pages/' . trim($_SERVER["REQUEST_URI"], '/') . '.php';
        $content = ob_get_clean();
        $content = str_replace('</body>', '<script src="system/LiveReload/assets/js/livereload.js"></script></body>', $content);
        echo $content;
        return;
    }
}

include 'build/system/error.php';