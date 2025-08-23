<?php
/**
 * Header
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */

require_once(__DIR__ . '/../../config/config.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Gau Yeu</title>
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS; ?>">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_ICONS; ?>">
    <link rel="stylesheet" href="<?php echo CUSTOM_CSS; ?>">
    <link rel="stylesheet" href="<?php echo CHATBOT_CSS; ?>">
</head>

<body>


    <script src="<?php echo JQUERY_JS; ?>"></script>
    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script src="<?php echo CUSTOM_JS; ?>"></script>
    <script src="<?php echo CHATBOT_JS; ?>"></script>
</body>

</html>