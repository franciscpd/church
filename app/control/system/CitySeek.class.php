<?php
/**
 * City Search Form
 *
 * @version    1.0
 * @author     Francis Soares de Oliveira
 */
class CitySeek extends TWindow
{
    private $form;
    private $datagrid;
    private $pageNavigation;
    private $loaded;
    
    public function __construct()
    {
        parent::__construct();
        parent::setSize(600, 500);
        
        new TSession;
        
        $this->form = new TQuickForm('form_search_city');
        $this->form->class = 'tform';
        $this->form->style = 'width: 100%';
        $this->form->setFormTitle(_t('Search of City'));              
                                        
        $name = new TEntry('name');
        $name->setValue(TSession::getValue('city_name'));
        
        $this->form->addQuickField(new TLabel(_t('Name').': '), $name, 450);
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        
        $this->datagrid = new TQuickGrid;
        $this->datagrid->setHeight(230);
        $this->datagrid->addQuickColumn('ID', 'id', 'left', 40);
        $this->datagrid->addQuickColumn(_t('Name'), 'name', 'left', 485);        
        $this->datagrid->addQuickAction(_t('Select'), new TDataGridAction(array($this, 'onSelect')), 'id', 'ico_apply.png');
        $this->datagrid->createModel();
        
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $table = new TTable;
        $table->style = 'width: 100%';
        $table->addRow()->addCell($this->form);
        $table->addRow()->addCell($this->datagrid);
        $table->addRow()->addCell($this->pageNavigation);
        
        parent::add($table);
    }
    
    function onSearch()
    {
        $data = $this->form->getData();
        
        if (isset($data->name))
        {
            $filter = new TFilter('name', 'like', "%{$data->name}%");
                        
            TSession::setValue('city_filter', $filter);
            TSession::setValue('city_name', $data->name);
            
            $this->form->setData($data);
        }
        
        $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;
        
        $this->onReload($param);
    }
    
    public function onSelect($param)
    {
        try
        {
            $key = $param['key'];
            TTransaction::open('ieadb');
            $city = new City($key);
            TTransaction::close();
            
            $object = new StdClass;
            $object->city_id = $city->id;
            $object->city_name = $city->name;
            TForm::sendData('form_Member', $object);
            parent::closeWindow();
        }
        catch (Exception $e)
        {
            $object = new StdClass;
            $object->city_id = '';
            $object->city_name = '';
            TForm::sendData('form_Member', $object);
            TTransaction::rollback();
        }
    }  
    
    function show()
    {   
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
    
    function onReload($param = NULL)
    {
        try
        {
            TTransaction::open('ieadb');
            
            $repository = new TRepository('City');
            $limit = 5;
            
            $criteria = new TCriteria;
            
            if (!isset($param['order']))
            {
                $param['order'] = 'name';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param);
            $criteria->setProperty('limit', $limit);
                        
            if (TSession::getValue('city_filter'))
            {
                $criteria->add(TSession::getValue('city_filter'));
            }
            
            $criteria->add(new TFilter('state_id', '=', TSession::getValue('state_id')));
            
            $city = $repository->load($criteria);
            $this->datagrid->clear();
            if ($city)
            {
                foreach ($city as $city)
                {
                    $this->datagrid->addItem($city);
                }
            }
            
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); 
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($limit);
            
            TTransaction::close();
            $this->loaded = TRUE;
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            TTransaction::rollback();
        }
    }  
}
?>