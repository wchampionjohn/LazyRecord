<?php
namespace LazyRecord;

class Schema
{
    public $relations = array();

    // public $accessors = array();
    public $columns = array();

    public $columnNames = array();

    public $primaryKey;

    public $table;

	public $modelClass;

	public function import($schemaArray)
	{
		$this->columns = $schemaArray['columns'];
		$this->columnNames = $schemaArray['column_names'];
		$this->primaryKey = $schemaArray['primary_key'];
		$this->table = $schemaArray['table'];
		$this->modelClass = $schemaArray['model_class'];
	}

	public function getRelation($relationId)
	{
        if( ! isset($this->relations[ $relationId ]) ) {
            throw new Exception("Relation $relationId is not defined.");
        }
        return $this->relations[ $relationId ];
	}

}