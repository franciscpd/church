<?php
/**
 * State Active Record
 * @author  Francis Soares de Oliveira
 */
class State extends TRecord
{
    const TABLENAME = 'state';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('uf');
        parent::addAttribute('name');
    }
}
?>