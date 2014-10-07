<?php namespace Hpkns\Sti;

use Illuminate\Database\Eloquent\Model;

class StiBase extends Model {

    /**
     * The name of the database fields that indicates the class name of the object
     *
     * @var string
     */
    protected $stiClassField = 'node_class';

    /**
     * Initialize the instance
     *
     * @param  array $attributes
     * @return void
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        if($this->useSti())
        {
            $this->setAttribute($this->stiClassField, get_class($this));
        }
    }

    /**
     * Indicates if the class uses STI
     *
     * @return boolean
     */
    private function useSti() {
        return ($this->stiClassField && $this->stiBaseClass);
    }

    /**
     * Override a query
     *
     * @param  boolean $excludeDeleted
     * @return Illuminate\Database\Builder
     */
    public function newQuery($excludeDeleted = true)
    {
        $builder = parent::newQuery($excludeDeleted);

        // When making a new query using STI, we use the name of
        // the class to limitate the resulsts to the records that
        // have the same class as the one we make the query from
        if ($this->useSti() && get_class(new $this->stiBaseClass) !== get_class($this)) {
            $builder->where($this->stiClassField,"=",get_class($this));
        }
        return $builder;
    }

    /**
     * Change the class after retreiving records from the builder
     *
     * @param  array $attributes
     * @return mixed
     */
    public function newFromBuilder($attributes = array())
    {
        if ($this->useSti() && $attributes->{$this->stiClassField}) {
            $class = $attributes->{$this->stiClassField};
            $instance = new $class;
            $instance->exists = true;
            $instance->setRawAttributes((array) $attributes, true);
            return $instance;
        } else {
            return parent::newFromBuilder($attributes);
        }
    }
}

