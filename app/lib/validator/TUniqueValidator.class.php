<?php
class TUniqueValidator extends TFieldValidator
{
    private $database;
    private $model;
    private $field;
    private $value;
    private $id;
    
    public function validate($label, $value, $parameters = NULL)
    {
        $this->id    = $parameters['id'];
        $this->model = $parameters['model']; 
        $this->field = $parameters['field'];
        $this->value = $value;
        $this->database = $parameters['database'];
                
        if (!$this->checkUnique())
        {
            throw new Exception("$label já cadastrado no banco de dados");
        }
    }
    
    private function checkUnique()
    {
        try
        {
            TTransaction::open($this->database);
            $repository = new TRepository($this->model);
            
            $criteria = new TCriteria;
            $criteria->add(new TFilter($this->field, '=', $this->value));
            
            if (($this->id) && ($this->id <> '') && (!is_null($this->id)))
            {
                $criteria->add(new TFilter('id', '<>', $this->id));
            }
            
            $count = $repository->count($criteria);
            
            TTransaction::close();
            
            return ($count <= 0);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
?>
