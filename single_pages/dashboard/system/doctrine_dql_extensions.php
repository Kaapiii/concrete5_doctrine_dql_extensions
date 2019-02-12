<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<div class="row">
    <div class="col-md-12">
        <p><?= t('The list below shows all added MySQL functions, which can be used ether with DQL queries or with the Doctrine QueryBuilder.'); ?></p>
        <p><?= t('Examples on how to use the functions in DQL queries can be found here.'); ?> <a href="https://github.com/beberlei/DoctrineExtensions/tree/master/tests/Query/Mysql" target="_blank"><?= t('Examples'); ?></a>
        <br>
        <br>
    </div>
</div>
<style>
    .xtwoColumns{
        -webkit-column-gap: 20px;
           -moz-column-gap: 20px;
                column-gap: 20px;
        -webkit-column-count: 2;
           -moz-column-count: 2;
                column-count: 2;
    }
</style>
<div class="row">
    <?php
    if (count($customFunctions)):
        foreach ($customFunctions as $dqlFuctionName => $customStringFunction):
            if (is_array($customStringFunction) && count($customStringFunction)):?>
            <div class="col-md-12 ccm-dashboard-section-menu">
                <?php
                    if (strpos($dqlFuctionName, 'Datetime') !== false) {
                        $title = t('MySQL date and time functions');
                        $url = 'https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html';
                        $linkName = t('MySQL documentation for date and time functions');
                    } elseif (strpos($dqlFuctionName, 'Numeric') !== false) {
                        $title = t('MySQL numeric functions');
                        $url = 'https://dev.mysql.com/doc/refman/5.7/en/numeric-functions.html';
                        $linkName = t('MySQL documentation for numeric functions');
                    } elseif (strpos($dqlFuctionName, 'String') !== false){
                        $title = t('MySQL string functions');
                        $url = 'https://dev.mysql.com/doc/refman/5.7/en/string-functions.html';
                        $linkName = t('MySQL documentation for string functions');
                    }
                ?>
                <h2><?= $title; ?></h2>
                <p><a target="_blank" href="<?= $url; ?>"><?= $linkName ;?></a></p>
                <div class="xtwoColumns">
                    <ul>
                        <?php foreach ($customStringFunction as $functionName => $className): ?>
                            <li><?= strtoupper($functionName); ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php
            endif;
        endforeach;

    endif;
    ?>
</div>
