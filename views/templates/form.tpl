<?php
use oat\tao\helpers\Template;

Template::inc('header.tpl');
?>
    <div class="main-container">
        <h2><?=get_data('formTitle')?></h2>
        <div class="form-content">
            <?=get_data('myForm')?>
        </div>
    </div>
    <div class="data-container-wrapper"></div>
<?php Template::inc('footer.tpl'); ?>