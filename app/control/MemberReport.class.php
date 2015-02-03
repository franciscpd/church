<?php
/**
 * MemberReport Report
 * @author  <your name here>
 */
class MemberReport extends TPage
{
    protected $form;
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_Member_report');
        $this->form->class = 'tform'; // CSS class
        
        // creates the table container
        $table = new TTable;
        $table->width = '100%';
        
        // add the table inside the form
        $this->form->add($table);

        // define the form title
        $table->addRowSet( new TLabel(_t('Report')), '', '' )->class = 'tformtitle';
        
        // create the form fields
        $name                           = new TEntry('name');
        $birth_date_ini                 = new TDate('birth_date_ini');
        $birth_date_fin                 = new TDate('birth_date_fin');
        $output_type                    = new TRadioGroup('output_type');

        $name->setSize(200);
        $birth_date_ini->setSize(100);
        $birth_date_fin->setSize(100);
        $output_type->setSize(100);
        
        //Masks
        $birth_date_ini->setMask('dd/mm/yyyy');
        $birth_date_fin->setMask('dd/mm/yyyy');

        // validations
        $output_type->addValidation('Output', new TRequiredValidator);

        // add one row for each form field
        $table->addRowSet( new TLabel(_t('Name') . ': '), $name, '' );
        $table->addRowSet( new TLabel(_t('Birth of Date') . ': '), $birth_date_ini, $birth_date_fin );
        $table->addRowSet( $label_output_type = new TLabel(_t('Output') . ': '), $output_type, '' );

        $this->form->setFields(array($name, $birth_date_ini, $birth_date_fin, $output_type));
        
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF'));;
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        $generate_button = TButton::create('generate', array($this, 'onGenerate'), _t('Generate'), 'bs:ok');
        $list_button = TButton::create('list', array('MemberList', 'onReload'), _t('Back to the listing'), 'bs:th-list');
        
        $this->form->addField($generate_button);
        $this->form->addField($list_button);
        
        $buttons_box = new THBox;
        $buttons_box->add($generate_button);
        $buttons_box->add($list_button);
        
        // add a row for the form action
        $row = $table->addRow();
        $row->class = 'tformaction'; // CSS class
        $row->addCell($buttons_box)->colspan = 3;
        
        // create the page container
        $container = TVBox::pack( new TXMLBreadCrumb('menu.xml', 'MemberList'), $this->form);
        parent::add($container);
    }
    
    function onShow()
    {
    }

    /**
     * method onGenerate()
     * Executed whenever the user clicks at the generate button
     */
    function onGenerate()
    {
        try
        {
            // open a transaction with database 'ieadb'
            TTransaction::open('ieadb');
            
            // get the form data into an active record
            $formdata = $this->form->getData();
            
            $repository = new TRepository('Member');
            $criteria   = new TCriteria;
            
            if ($formdata->name)
            {
                $criteria->add(new TFilter('name', 'like', "%{$formdata->name}%"));
            }
            if (($formdata->birth_date_ini) && ($formdata->birth_date_fin))
            {
                $date_ini = TDate::date2us($formdata->birth_date_ini);
                $date_fin = TDate::date2us($formdata->birth_date_fin);
                
                $mon_ini  = substr($date_ini,5,2);
                $day_ini  = substr($date_ini,8,2);
                
                $mon_fin  = substr($date_fin,5,2);
                $day_fin  = substr($date_fin,8,2);
            
                $criteria->add(new TFilter("SUBSTR(birth_date, 6, 2)", 'BETWEEN', $mon_ini, $mon_fin));
                $criteria->add(new TFilter("SUBSTR(birth_date, 9, 2)", 'BETWEEN', $day_ini, $day_fin));
            }
           
            $objects = $repository->load($criteria);
            $format  = $formdata->output_type;
            
            if ($objects)
            {
                $widths = array(40, 400,100);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '10', 'B',   '#ffffff', '#6B6B6B');
                $tr->addStyle('datap', 'Arial', '10', '',    '#000000', '#E5E5E5');
                $tr->addStyle('datai', 'Arial', '10', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Times', '16', 'B',  '#4A5590', '#C0D3E9');
                $tr->addStyle('footer', 'Times', '12', 'BI', '#4A5590', '#C0D3E9');
                
                // add a header row
                $tr->addRow();
                $tr->addCell(_t('Members'), 'center', 'header', 3);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('ID', 'left', 'title');
                $tr->addCell(_t('Name'), 'left', 'title');
                $tr->addCell(_t('Birth of Date'), 'left', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->id, 'left', $style);
                    $tr->addCell($object->name, 'left', $style);
                    $tr->addCell(TDate::date2br($object->birth_date), 'left', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(TDate::date2br(date('Y-m-d')) . ' ' . date('h:i:s'), 'right', 'footer', 3);
                // stores the file
                if (!file_exists("app/output/Member.{$format}") OR is_writable("app/output/Member.{$format}"))
                {
                    $tr->save("app/output/Member.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/Member.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/Member.{$format}");
                
                // shows the success message
                new TMessage('info', _t('Report generated. Please, enable popups in the browser (just in the web)'));
            }
            else
            {
                new TMessage('error', _t('No records found'));
            }
    
            // fill the form with the active record data
            $this->form->setData($formdata);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
