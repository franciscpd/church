<?php
/**
 * MemberList Listing
 * @author  Francis Soares de Oliveira
 */
class MemberList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_search_Member');
        $this->form->class = 'tform'; // CSS class
        
        // creates a table
        $table = new TTable;
        $table-> width = '100%';
        $this->form->add($table);
        
        // add a row for the form title
        $row = $table->addRow();
        $row->class = 'tformtitle'; // CSS class
        $row->addCell( new TLabel(_t('Members')) )->colspan = 2;
        

        // create the form fields
        $id                             = new TEntry('id');
        $name                           = new TEntry('name');
        $cpf                            = new TEntry('cpf');


        // define the sizes
        $id->setSize(50);
        $name->setSize(300);
        $cpf->setSize(150);
        
        $cpf->setMask('999.999.999-99');;


        // add one row for each form field
        $table->addRowSet( new TLabel('ID: '), $id );
        $table->addRowSet( new TLabel(_t('Name') . ': '), $name );
        $table->addRowSet( new TLabel('CPF: '), $cpf );


        $this->form->setFields(array($id,$name,$cpf));


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Member_filter_data') );
        
        // create two action buttons to the form
        $find_button = TButton::create('find', array($this, 'onSearch'), _t('Find'), 'bs:search');
        $new_button  = TButton::create('new',  array('MemberForm', 'onEdit'), _t('New'), 'bs:plus');
        $report_button = TButton::create('report', array('MemberReport', 'onShow'), _t('Report'), 'bs:print');
        
        $this->form->addField($find_button);
        $this->form->addField($new_button);
        $this->form->addField($report_button);
        
        $buttons_box = new THBox;
        $buttons_box->add($find_button);
        $buttons_box->add($new_button);
        $buttons_box->add($report_button);
        
        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 2;
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $id   = new TDataGridColumn('id', 'ID', 'left', 50);
        $name   = new TDataGridColumn('name', _t('Name'), 'left', 300);
        $cpf   = new TDataGridColumn('cpf', 'CPF', 'left', 150);
        $phone   = new TDataGridColumn('phone', _t('Phone'), 'left', 120);
        $mobile_phone   = new TDataGridColumn('mobile_phone', _t('Mobile Phone'), 'left', 120);


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($name);
        $this->datagrid->addColumn($cpf);
        $this->datagrid->addColumn($phone);
        $this->datagrid->addColumn($mobile_phone);


        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_name= new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'name');
        $name->setAction($order_name);


        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array('MemberForm', 'onEdit'));
        $action1->setLabel(_t('Edit'));
        $action1->setImage('bs:edit');
        $action1->setField('id');
        
        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel(_t('Delete'));
        $action2->setImage('bs:trash');
        $action2->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // create the page container
        $container = TVBox::pack( new TXMLBreadCrumb('menu.xml', 'MemberList'), $this->form, $this->datagrid, $this->pageNavigation);
        parent::add($container);
    }
    
    /**
     * method onInlineEdit()
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('ieadb'); // open a transaction with database
            $object = new Member($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('MemberList_filter_id',   NULL);
        TSession::setValue('MemberList_filter_name',   NULL);
        TSession::setValue('MemberList_filter_cpf',   NULL);

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter('id', '=', "$data->id"); // create the filter
            TSession::setValue('MemberList_filter_id',   $filter); // stores the filter in the session
        }


        if (isset($data->name) AND ($data->name)) {
            $filter = new TFilter('name', 'like', "%{$data->name}%"); // create the filter
            TSession::setValue('MemberList_filter_name',   $filter); // stores the filter in the session
        }


        if (isset($data->cpf) AND ($data->cpf)) {
            $filter = new TFilter('cpf', '=', "$data->cpf"); // create the filter
            TSession::setValue('MemberList_filter_cpf',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Member_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'ieadb'
            TTransaction::open('ieadb');
            
            // creates a repository for Member
            $repository = new TRepository('Member');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('MemberList_filter_id')) {
                $criteria->add(TSession::getValue('MemberList_filter_id')); // add the session filter
            }


            if (TSession::getValue('MemberList_filter_name')) {
                $criteria->add(TSession::getValue('MemberList_filter_name')); // add the session filter
            }


            if (TSession::getValue('MemberList_filter_cpf')) {
                $criteria->add(TSession::getValue('MemberList_filter_cpf')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('ieadb'); // open a transaction with database
            $object = new Member($key, FALSE); // instantiates the Active Record
            
            if (file_exists('./app/images/' . $object->image) && !is_dir('./app/images/' . $object->image))
            {
                unlink('./app/images/' . $object->image);
            }
            
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
