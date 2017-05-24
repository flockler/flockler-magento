<?php

class Flockler_Flockler_Block_Adminhtml_Flockler_Posts_Grid extends Mage_adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('flockler_posts_list_grid');
        $this->setDefaultSort('published_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('flockler/post')->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header' => Mage::helper('flockler_flockler')->__('Post Title'),
            'index'     => 'title',
        ));

        $this->addColumn('tags', array(
            'header' => Mage::helper('flockler_flockler')->__('Tags'),
            'index'  => 'tags',
            'getter' => 'getRowTags'
        ));

        $this->addColumn('site_id', array(
            'header' => Mage::helper('flockler_flockler')->__('Site ID'),
            'index'     => 'site_id',
            'width' => '50px',
        ));

        $this->addColumn('section_id', array(
            'header' => Mage::helper('flockler_flockler')->__('Section ID'),
            'index'     => 'section_id',
            'width' => '50px',
        ));

        $this->addColumn('published_at', array(
            'header' => Mage::helper('flockler_flockler')->__('Published'),
            'index'     => 'published_at',
            'width' => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()
                ->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('flockler_post_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('flockler_posts');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('flockler_flockler')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => Mage::helper('flockler_flockler')->__('Are you sure?')
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
