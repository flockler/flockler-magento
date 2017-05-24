<?php

$installer = $this;
$installer->startSetup();

/**
 * Defining table flockler_posts
 */
$posts_table = $installer
    ->getConnection()
    ->newTable($installer->getTable('flockler/post'))
    ->addColumn('flockler_post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Entity id')
    ->addColumn('article_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('article_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('author', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('body', Varien_Db_Ddl_Table::TYPE_TEXT, '2M')
    ->addColumn('cover_pos_x', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('cover_pos_y', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('cover_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('display_style', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('pinned_index', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('order_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('published_at', Varien_Db_Ddl_Table::TYPE_DATE, null)
    ->addColumn('published_at_l10n', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('section_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('site_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('site_pinned_index', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('site_order_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('source_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('summary', Varien_Db_Ddl_Table::TYPE_TEXT, '2M')
    ->addColumn('tags', Varien_Db_Ddl_Table::TYPE_TEXT, '2M')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('url', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('attachments', Varien_Db_Ddl_Table::TYPE_TEXT, '2M')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('flockler/post'),
            array('published_at'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array('published_at'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('Flockler Post item');

/**
 * Defining table flockler_webhooks
 */

$webhooks_table = $installer
    ->getConnection()
    ->newTable($installer->getTable('flockler/webhook'))
    ->addColumn('flockler_webhook_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Entity id')
    ->addColumn('webhookid', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('secret', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('flockler/webhook'),
            array('created_at'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array('created_at'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('Flockler Webhook');


/**
 * Creating tables
 */

$installer->getConnection()->createTable($posts_table);
$installer->getConnection()->createTable($webhooks_table);

$installer->endSetup();



/**
 * Categories Flockler tab => new attribute
 */

$installer = Mage::getResourceModel('catalog/setup','catalog_setup');
$installer->startSetup();

$installer
    ->addAttribute(
        'catalog_category',
        'flockler_site_id',
        array(
            'label' => 'Site ID',
            'required' => false,
            'group' => 'Flockler'
        )
    )
    ->addAttribute(
        'catalog_category',
        'flockler_section_id',
        array(
            'label' => 'Section ID',
            'required' => false,
            'group' => 'Flockler'
        )
    )
    ->addAttribute(
        'catalog_category',
        'flockler_tags',
        array(
            'label' => 'Tags',
            'required' => false,
            'group' => 'Flockler'
        )
    )
    ->addAttribute(
        'catalog_category',
        'flockler_disable_name_tag',
        array(
            'label' => "Disable category name as tag",
            'type' => 'int',
            'input' => 'select',
            'source'    => 'eav/entity_attribute_source_boolean',
            'default' => 0,
            'required' => false,
            'group' => 'Flockler'
        )
    );


/**
 * Product Flockler tab => new attribute
 */

$installer
    ->addAttribute(
        'catalog_product',
        'flockler_site_id',
        array(
            'label' => 'Site ID',
            'required' => false,
            'group' => 'Flockler'
        )
    )
    ->addAttribute(
        'catalog_product',
        'flockler_section_id',
        array(
            'label' => 'Section ID',
            'required' => false,
            'group' => 'Flockler'
        )
    )
    ->addAttribute(
        'catalog_product',
        'flockler_tags',
        array(
            'label' => 'Tags',
            'required' => false,
            'group' => 'Flockler'
        )
    );

$installer->endSetup();
