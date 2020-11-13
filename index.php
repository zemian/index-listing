<?php
/**
 * A simple php to browse a DocumentRoot directory where it list all dirs and files.
 *
 * NOTE: 
 * Exposing directory and files is consider security risk for publicly hosted server! This 
 * script is only intended for internal web site and serve as tool. 
 * 
 * Features:
 *   - List files alphabetically.
 *   - Each file should be listed as a link and go to the page when clicked.
 *   - List dir separately on the side as navigation.
 *   - Each dir is a link to browse sub dir content recursively. 
 *   - Provide parent link to go back up one directory when browsing sub dir.
 * 
 * Author: Zemian Deng
 * Date: 2020-11-04
 */

// Page vars
$title = 'Index Listing';
$browse_dir = $_GET['dir'] ?? '';
$parent_browse_dir = $_GET['parent'] ?? '';
$error = '';
$dirs = [];
$files = [];
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Build dir navigation
$dir_links = explode('/', $browse_dir);
$dir_links_len = count($dir_links);
$dir_paths = [];
$dir_links_idx = 1;
foreach ($dir_links as &$dir) {
    $parent_path = implode('/', $dir_paths);
    $path = "$parent_path/$dir";
    $dir_paths []= $dir;
    if ($dir_links_idx++ < $dir_links_len) { // Update all except last element
        $dir = "<a href='$url?dir=$path&parent=$parent_path'>$dir</a>"; // Update by ref!
    }
}
$browse_dir_url = implode('/', $dir_links);

// Internal vars
$root_path = __DIR__;
$list_path = "$root_path/$browse_dir";

// Validate Inputs
if ( (substr_count($browse_dir, '.') > 0) /* It should not contains '.' or '..' relative paths */
    || (!is_dir($list_path)) /* It should exists. */
) {
    $error = "ERROR: Invalid directory.";
}

// Get files and dirs listing
if (!$error) {
    // We need to get rid of the first two entries for "." and ".." returned by scandir().
    $list = array_slice(scandir($list_path), 2);
    foreach ($list as $item) {
        // NOTE: To avoid security risk, we always use $list_path as base path! Never go outside of it!
        if (is_dir("$list_path/$item")) {
            // We will not show hidden ".folder" folders
            if (substr_compare($item, '.', 0, 1) !== 0) {
                array_push($dirs, $item);
            }
        } else {
            array_push($files, $item);
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://unpkg.com/bulma">
    <title><?php echo $title; ?></title>
</head>
<body>
<div class="section">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title"><?php echo $title; ?></h1>
            </div>
        </div>
    </div>
    <div class="columns has-background-light">
        <div class="column is-one-third" style="min-height: 80vh;">
            <!-- List of Directories -->
            <div class="menu">       
                <?php // Bulma menu-label always capitalize words, so we override it to not do that for dir name sake. ?>
                <p class="menu-label" style="text-transform: inherit;"><a href="<?php echo $url; ?>">Directory:</a> <?php echo $browse_dir_url; ?></p>
                <ul class="menu-list">
                    <?php foreach ($dirs as $item) { ?>
                    <li><a href="index.php?dir=<?php echo "$browse_dir/$item"; ?>&parent=<?php echo $browse_dir; ?>"><?php echo $item; ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="column">
            <?php if ($error) { ?>
                <div class="notification is-danger">
                    <?php echo $error; ?>
                </div>
            <?php } else { ?>
                <!-- List of Files -->
                <ul class="panel has-background-white">
                    <?php foreach ($files as $item) { ?>
                        <li class="panel-block"><a href="<?php echo "$browse_dir/$item"; ?>"><?php echo $item; ?></a></li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
</div>
<div class="footer has-background-white">
    Powered by <a href="https://github.com/zemian/index-listing">index-listing</a>.
</div>

</body>
</html>
