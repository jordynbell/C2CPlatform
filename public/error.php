<?php

if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container mt-5">

    <div class="text-center">
        <h1 class="fw-bold text-center" style="font-size: 5rem;">ERROR 404</h1>
    </div>

    <div class="text-center">
        <img src="<?php echo $basePath; ?>/assets/images/404.png" alt="Error 404" class="img-fluid" width="300"
            height="300">
    </div>

    <div class="justify-content-center">
        <div class="text-center">
            <h1>
                <p>Oh no... How did we get here?</p>
                <p>Going back to safety in <span id="countdown" class="fw-bold">5</span> seconds.</p>
            </h1>
        </div>
    </div>
</div>

<script>
    const basePath = "<?php echo $basePath; ?>";
    let counter = 5;
    const countdown = setInterval(() => {
        if (counter === 0) {
            clearInterval(countdown);
            window.location.href = basePath + "/index.php";
        } else {
            document.getElementById('countdown').innerText = counter;
            counter--;
        }
    }, 1000);
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>