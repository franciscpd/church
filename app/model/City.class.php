<?php
/**
 * City Active Record
 * @author  Francis Soares de Oliveira
 */
class City extends TRecord
{
    const TABLENAME = 'city';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $state;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('state_id');
        parent::addAttribute('name');
    }

    /**
     * Returns the state name
     */
    public function get_state_name()
    {
        // loads the associated object
        if (empty($this->state))
            $this->state = new State($this->state_id);
    
        // returns the associated object
        return $this->state->name;
    }
}
?>