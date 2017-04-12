<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<div class="row">
    <div class="col-md-12">
        <p><?= t('The list below shows all custom added MySQL functions. All the functions in the lists can be used in DQL queries and with the QueryBuilder.'); ?></p>
        <br>
    </div>
</div>
<div class="row">
    <?php
    if (count($customStringFunctions)):
        foreach ($customStringFunctions as $dqlFuctionName => $customStringFunction):
            if (is_array($customStringFunction) && count($customStringFunction)):?>
            <div class="col-md-4 ccm-dashboard-section-menu">
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
                <ul>
                    <?php foreach ($customStringFunction as $functionName => $className): ?>
                        <li><?= strtoupper($functionName); ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php
            endif;
        endforeach;

    endif;
    ?>

</div>