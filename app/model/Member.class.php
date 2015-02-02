<?php
/**
 * Member Active Record
 * @author Francis Soares de Oliveira
 */
class Member extends TRecord
{
    const TABLENAME = 'member';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
        parent::addAttribute('father');
        parent::addAttribute('mother');
        parent::addAttribute('birth_date');
        parent::addAttribute('marital_status');
        parent::addAttribute('spouse_name');
        parent::addAttribute('nacionality');
        parent::addAttribute('gender');
        parent::addAttribute('profession');
        parent::addAttribute('cpf');
        parent::addAttribute('rg');
        parent::addAttribute('organ');
        parent::addAttribute('state_rg_id');
        parent::addAttribute('phone');
        parent::addAttribute('mobile_phone');
        parent::addAttribute('date_of_conversion');
        parent::addAttribute('date_of_water_baptism');
        parent::addAttribute('date_of_spirit_baptism');
        parent::addAttribute('address');
        parent::addAttribute('number_address');
        parent::addAttribute('district');
        parent::addAttribute('city_id');
        parent::addAttribute('state_id');
        parent::addAttribute('status');
        parent::addAttribute('image');
    }
}
