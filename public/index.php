<!-- 
What do add to landing page:
1. Create a hero section with a background image and a welcome message.
2. Second section can have the benefits of using Squito.
3. Reviews section with user testimonials.
4. FAQ using bootstrap accordion.
5. Get started section prompting user to register.

-->

<?php

if (!isset($_SESSION)) {
    session_start();
}

$pageTitle = "Home - Squito";

require_once __DIR__ . '/../includes/header.php';

?>

<h1>My First PHP Page</h1>
<?php
echo "Welcome to Squito!";
?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>