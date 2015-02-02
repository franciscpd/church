<?php
/**
 * MemberForm Registration
 * @author  <your name here>
 */
class MemberForm extends TPage
{
    private $form;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TForm('form_Member');
        $this->form->class = 'tform';
        
        new TSession;
        TSession::delValue('state_id');
        TSession::delValue('city_name');
        TSession::delValue('city_filter');        
    
        // creates the table container
        $table = new TTable;
        $table->style = 'width: 100%';
        
        $table->addRowSet( new TLabel(_t('Member')), '', '','' )->class = 'tformtitle';
        $this->form->add($table);
        
        $row  = $table->addRow();
        
        // create the form fields
        $id                     = new TEntry('id');
        $name                   = new TEntry('name');
        $father                 = new TEntry('father');
        $mother                 = new TEntry('mother');
        $birth_date             = new TDate('birth_date');
        $marital_status         = new TCombo('marital_status');
        $spouse_name            = new TEntry('spouse_name');
        $image                  = new TFile('image');
        $nacionality            = new TEntry('nacionality');
        $gender                 = new TRadioGroup('gender');
        $state_id               = new TDBCombo('state_id', 'ieadb', 'State', 'id', 'name', 'name');
        $city_id                = new TSeekButton('city_id');
        $city_name              = new TEntry('city_name');
        $address                = new TEntry('address');                              
        $number_address         = new TEntry('number_address');
        $district               = new TEntry('district');
        $profession             = new TEntry('profession');
        $cpf                    = new TEntry('cpf');
        $rg                     = new TEntry('rg');
        $organ                  = new TEntry('organ');
        $state_rg_id            = new TDBCombo('state_rg', 'ieadb', 'State', 'id', 'name', 'name');
        $phone                  = new TEntry('phone');
        $mobile_phone           = new TEntry('mobile_phone');
        $date_of_conversion     = new TDate('date_of_conversion');
        $date_of_water_baptism  = new TDate('date_of_water_baptism');
        $date_of_spirit_baptism = new TDate('date_of_spirit_baptism');
        $status                 = new TRadioGroup('status');
        $image                  = new TFile('image');
        
        //Altera o tamanho dos campos
        $id->setSize(100);
        $name->setSize(250);
        $father->setSize(250);
        $mother->setSize(250);
        $birth_date->setSize(100);
        $marital_status->setSize(100);
        $spouse_name->setSize(250);
        $nacionality->setSize(160);
        $state_id->setSize(160);
        $city_id->setSize(100);
        $city_name->setSize(250);
        $address->setSize(250);
        $number_address->setSize(100);
        $cpf->setSize(160);
        $rg->setSize(160);
        $organ->setSize(100);
        $state_rg_id->setSize(160);
        $phone->setSize(160);
        $mobile_phone->setSize(160);
        $date_of_conversion->setSize(100);
        $date_of_water_baptism->setSize(100);
        $date_of_spirit_baptism->setSize(100);
        $image->setSize(250);
        
        //Propriedades dos fields
        $birth_date->setMask('dd/mm/yyyy');
        $cpf->setMask('999.999.999-99');
        $rg->setMask('99999999999999');
        $phone->setMask('(99)9999-9999');
        $mobile_phone->setMask('(99)9999-9999');
        $date_of_conversion->setMask('dd/mm/yyyy');
        $date_of_water_baptism->setMask('dd/mm/yyyy');
        $date_of_spirit_baptism->setMask('dd/mm/yyyy');
        
        $marital_status->addItems(array('Solteiro', 'Casado', 'Divorciado'));
        $gender->addItems(array(' Masculino', ' Feminino'));
        $status->addItems(array(' Membro', ' Congregado', ' Desligado', ' Disciplinado', ' Transferido'));
                
        //outros
        $id->setEditable(FALSE);
        $gender->setLayout('horizontal');
        $city_name->setEditable(FALSE);
        
        //Actions 
        $state_id->setChangeAction(new TAction(array($this, 'onStateChange')));
        
        $obj = new CitySeek;
        $city_id->setAction(new TAction(array($obj, 'onReload')));
        
        //validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $cpf->addValidation('CPF', new TCPFValidator);
        
        $table_member = new TTable;
        $table_member->style = 'width: 100%';
        
        $table_member->addRowSet(new TLabel('ID: '), $id, new TLabel(_t('Name').': '), $name);
        $table_member->addRowSet(new TLabel(_t('Father').': '), $father, new TLabel(_t('Mother').': '), $mother);
        $table_member->addRowSet(new TLabel(_t('Birth of Date').': '), $birth_date, new TLabel(_t('Marital Status').': '), $marital_status);
        $table_member->addRowSet(new TLabel(_t('Spouse Name').': '), $spouse_name, new TLabel(_t('Nacionality').': '),  $nacionality);
        $table_member->addRowSet(new TLabel(_t('Gender').': '), $gender);
        $table_member->addRowSet(new TLabel('CPF: '), $cpf, new TLabel('RG: '), $rg);
        $table_member->addRowSet(new TLabel(_t('Organ').': '), $organ, new TLabel(_t('State Organ').': '), $state_rg_id);
        $table_member->addRowSet(new TLabel(_t('Address').': '), $address, new TLabel(_t('Number').': '), $number_address);
        $table_member->addRowSet(new TLabel(_t('District').': '), $district, new TLabel(_t('State').': '), $state_id);
        $table_member->addRowSet(new TLabel(_t('City').': '), $city_id, '', $city_name);
        $table_member->addRowSet(new TLabel(_t('Phone').': '), $phone, new TLabel(_t('Mobile Phone').': '), $mobile_phone);
        $table_member->addRowSet(new TLabel(_t('Conversion').': '), $date_of_conversion, new TLabel(_t('Water Baptism').': '), $date_of_water_baptism);
        $table_member->addRowSet(new TLabel(_t('Spirit Baptism').': '), $date_of_spirit_baptism);
        $table_member->addRowSet(new TLabel(_t('Status').': '), $status, new TLabel(_t('Image').': '), $image);
        
        $cell = $row->addCell($table_member);
        $cell->valign = 'top';
        
        $frame_photo = new TFrame(150, NULL);
        
        $photo     = new TImage('no-image.jpg');
        $photo->id = 'photo';        
        
        $frame_photo->add($photo);
        $frame_photo->setLegend(_t('Image'));
        
        $cell = $row->addCell($frame_photo);
        $cell->valign = 'top';
        $cell->width = 150;
        
        /**
         * Actions
         */        
        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->setImage('ico_save.png');
        
        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), _t('New'));
        $new_button->setImage('ico_new.png');
        
         $list_button=new TButton('list');
         $list_button->setAction(new TAction(array('MemberList','onReload')), _t('Back to the listing'));
         $list_button->setImage('ico_datagrid.png');
        
        // define the form fields
        $this->form->setFields(array($id, 
                                     $name,
                                     $father,
                                     $mother,
                                     $birth_date,
                                     $marital_status,
                                     $spouse_name,
                                     $nacionality,
                                     $gender,
                                     $state_id,
                                     $city_id, 
                                     $cpf,
                                     $rg,
                                     $organ,
                                     $state_rg_id,
                                     $address,
                                     $number_address,
                                     $district,
                                     $phone,
                                     $mobile_phone,
                                     $date_of_conversion,
                                     $date_of_water_baptism,
                                     $date_of_spirit_baptism,
                                     $status,
                                     $image,
                                     $save_button, 
                                     $new_button,
                                     $list_button));
        
        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $buttons );
        $cell->colspan = 4;

        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'SystemUserList'));
        $container->addRow()->addCell($this->form);

        // add the form to the page
        parent::add($container);
    }

    function onSave()
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('ieadb');
            
            // get the form data into an active record System_user
            $object = $this->form->getData('Member');            
            $object->birth_date = TDate::date2us($object->birth_date);
            $object->date_of_conversion = TDate::date2us($object->date_of_conversion);
            $object->date_of_water_baptism = TDate::date2us($object->date_of_water_baptism);
            $object->date_of_spirit_baptism = TDate::date2us($object->date_of_spirit_baptism);
                        
            // form validation
            $this->form->validate();
            
            
            if($object->image)
            {            
                $source_file = './tmp/' . $object->image;
                            
                //Verifica se foi realizado o upload
                if (file_exists($source_file))
                {
                    $canvas = new TCanvas($source_file);
                     
                    $object->image = TUtil::getSlugStr($object->name) . '.' . pathinfo($source_file, PATHINFO_EXTENSION);
                     
                    $canvas->resize(140, 140, 'crop');
                    $canvas->save('./app/images/' . $object->image);
                     
                    unlink($source_file);
                    
                    if (!file_exists('./app/images/' . $object->image))
                    {
                        $object->image = NULL;
                    }
                }
            }        
                     
            $object->store(); // stores the object
            
            $object->birth_date = TDate::date2br($object->birth_date);
            $object->date_of_conversion = TDate::date2br($object->date_of_conversion);
            $object->date_of_water_baptism = TDate::date2br($object->date_of_water_baptism);
            $object->date_of_spirit_baptism = TDate::date2br($object->date_of_spirit_baptism);
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // close the transaction
            TTransaction::close();
            
            $action_redirect = new TAction(array($this, 'onRedirect'));
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), $action_redirect);
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }        
    }    
    
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('ieadb');
                
                // instantiates object System_user
                $object = new Member($key);
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    public static function onStateChange($param)
    {
        TSession::setValue('state_id', $param['state_id']);
        TSession::delValue('city_name');
        TSession::delValue('city_filter');
        
        $object = new StdClass;
        $object->city_id = '';
        $object->city_name = '';
        TForm::sendData('form_Member', $object);
    }
    
    function show()
    {    
        TScript::create("
            $(document).ready( function()
            {
                $('#photo').attr('src', '{$this->getPhoto()}');
            });");
    
        parent::show();
    }
    
    private function getPhoto()
    {
        try
        {
            TTransaction::open('ieadb');
            
            if (isset($_GET['key']))
            {
                $object = new Member($_GET['key']);
                
                if (is_dir('./app/images/' . $object->image))
                {
                    $photo = './app/images/no-image.jpg';
                }
                else
                {                
                    $photo = './app/images/' . $object->image;
                } 
            }
            else
            {
                $photo = './app/images/no-image.jpg';
            }
                
            return $photo;
                
            // close the transaction
            TTransaction::close();                    
        }
        catch (Exception $e) // in case of exception
        {            
            // undo all pending operations
            TTransaction::rollback();
        }    
    }
    
    function onRedirect()
    {
        TApplication::executeMethod('MemberList', 'onReload');
    }
}
