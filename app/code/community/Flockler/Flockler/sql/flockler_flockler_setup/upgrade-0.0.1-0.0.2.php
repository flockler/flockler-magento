<?php

$updater = $this;
$updater->startSetup();

// Change "flockler_posts" table's "published_at" column's type from DATE to TIMESTAMP
$updater->getConnection()->modifyColumn('flockler_posts', 'published_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP);

$updater->endSetup();
