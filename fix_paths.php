<?php
$dir = __DIR__;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && $file->getFilename() !== 'fix_paths.php' && strpos($file->getPathname(), 'vendor') === false) {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        $content = str_replace('href="/drinashop/', 'href="<?= BASE_URL ?>/', $content);
        $content = str_replace('src="/drinashop/', 'src="<?= BASE_URL ?>/', $content);
        $content = str_replace('action="/drinashop/', 'action="<?= BASE_URL ?>/', $content);
        $content = str_replace("header('Location: /drinashop/", "header('Location: ' . BASE_URL . '/", $content);
        $content = str_replace('header("Location: /drinashop/', 'header("Location: " . BASE_URL . "/', $content);
        
        // Fixed.

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated " . $file->getPathname() . "\n";
        }
    }
}
