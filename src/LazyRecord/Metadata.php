<?php
namespace LazyRecord;
use ArrayAccess;
use IteratorAggregate;
use LazyRecord\Schema\DynamicSchemaDeclare;
use LazyRecord\TableParser\TableParser;
use ArrayIterator;
use PDO;

class Metadata
    implements ArrayAccess, IteratorAggregate
{

    /**
     * @var string data source id
     */
    public $dsId;


    /**
     * @var PDO PDO connection object
     */
    public $connection;


    /**
     * @var SQLBuilder\QueryDriver QueryDriver from SQLBuilder
     */
    public $driver;

    public function __construct($dsId) 
    {
        $this->dsId = $dsId;
        $connm = ConnectionManager::getInstance();
        $this->connection = $connm->getConnection($this->dsId);
        $this->driver = $connm->getQueryDriver($this->dsId);
        $this->init();
    }

    public function init()
    {
        $parser = TableParser::create( $this->driver, $this->connection );
        $tables = $parser->getTables();
        if( ! in_array('__meta__',$tables) ) {
            $schema = new DynamicSchemaDeclare(new Model\Metadata);
            $builder = \LazyRecord\SqlBuilder\SqlBuilder::create($this->driver);
            $sqls = $builder->build($schema);
            foreach($sqls as $sql) {
                $this->connection->query($sql);
            }
        }
    }

    public function getVersion()
    {
        if( isset( $this['version'] ) ) {
            return $this['version'];
        }
        return $this['version'] = 0;
    }

    public function offsetSet($name,$value)
    {
        $stm = $this->connection->prepare('select * from __meta__ where name = :name');
        $stm->execute(array( ':name' => $name ));
        $obj = $stm->fetch( PDO::FETCH_OBJ );
        if( $obj ) {
            $stm = $this->connection->prepare('update __meta__ set value = :value where name = :name');
            $stm->execute(array( ':name' => $name, ':value' => $value ));
        } else {
            $stm = $this->connection->prepare('insert into __meta__ (name,value) values (:name,:value)');
            $stm->execute(array( ':name' => $name, ':value' => $value ));
        }
    }

    public function offsetExists($name)
    {
        $stm = $this->connection->prepare('select * from __meta__ where name = :name');
        $stm->execute(array( ':name' => $name ));
        $data = $stm->fetch( PDO::FETCH_OBJ );
        return $data ? true : false;
    }

    public function offsetGet($name)
    {
        $stm = $this->connection->prepare('select * from __meta__ where name = :name');
        $stm->execute(array( ':name' => $name ));
        $data = $stm->fetch( PDO::FETCH_OBJ );
        if($data)
            return $data->value;
    }

    public function offsetUnset($name)
    {
        $stm = $this->connection->prepare('delete from __meta__ where name = :name');
        $stm->execute(array( ':name' => $name ));
    }

    public function getIterator()
    {
        $stm = $this->connection->prepare('select * from __meta__');
        $stm->execute();
        $rows = $stm->fetchAll(PDO::FETCH_OBJ);
        $data = array();
        foreach( $rows as $row ) {
            $data[$row->name] = $row->value;
        }
        return new ArrayIterator($data);
    }
}


