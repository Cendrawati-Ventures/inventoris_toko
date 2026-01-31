<?php
function startLayout($title = 'Sistem Inventori UD. Bersaudara') {
    ob_start();
    global $pageTitle;
    $pageTitle = $title;
}

function endLayout() {
    global $pageTitle;
    $content = ob_get_clean();
    $title = $pageTitle;
    include __DIR__ . '/header.php';
}
?>
