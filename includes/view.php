<?php ob_start(); ?>
    <div class="my-geo__form">
        <input type="hidden" class="my-geo__cdek_city_code" name="cdek_city_code" value="<?php echo $GLOBALS["mygeo"]->state["cdek_city_code"] ?>">
        <input type="hidden" class="my-geo__coordinates" name="coordinates" value="<?php echo htmlspecialchars(json_encode($GLOBALS["mygeo"]->state["coordinates"])) ?>">
    </div>
<?php echo ob_get_clean(); ?>