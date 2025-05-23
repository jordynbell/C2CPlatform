<!-- Implementation of the footer, including javascript dependencies and links to the home page, privacy page, and a display of the current year -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
    crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>

<nav class="navbar fixed-bottom navbar-expand-lg navbar-dark navbar-custom" style="padding-top: 12px; padding-bottom: 12px;">
    <div class="container-fluid">
        <a class="text-white text-decoration-none" href="<?php echo $basePath . "/index.php" ?>"><span id="spanYear">2025</span> Squito ©</a>
        <a class="text-white text-decoration-none" href="<?php echo $basePath . "/privacy.php" ?>">Privacy Policy</a>
    </div>
</nav>

<script>
    $('#spanYear').html(new Date().getFullYear());
</script>

<style>
    body { padding-bottom: 70px; }
</style>
</html>