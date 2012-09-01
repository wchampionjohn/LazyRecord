<?php
namespace LazyRecord\Migration;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use LazyRecord\Console;

class MigrationRunner
{


    public $logger;

    public $dataSourceIds = array();

    public function __construct($dsIds)
    {
        $this->logger = Console::getInstance()->getLogger();
        $this->dataSourceIds = (array) $dsIds;
    }

    public function addDataSource( $dsId ) 
    {
        $this->dataSourceIds[] = $dsId;
    }

    public function load($directory) 
    {
        $loaded = array();
        $iterator = new RecursiveIteratorIterator( 
            new RecursiveDirectoryIterator($directory) , RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach( $iterator as $path ) {
            if($path->isFile() && $path->getExtension() === 'php' ) {
                $code = file_get_contents($path);
                if( preg_match('#Migration#',$code) ) {
                    require_once($path);
                    $loaded[] = $path;
                }
            }
        }
        return $loaded;
    }

    public function getMigrationScripts() {
        $classes = get_declared_classes();
        $classes = array_filter($classes, function($class) { 
            return is_a($class,'LazyRecord\\Migration\\Migration',true) 
                && $class != 'LazyRecord\\Migration\\Migration';
        });
        // sort class with timestamp suffix
        usort($classes,function($a,$b) { 
            if( preg_match('#_(\d+)$#',$a,$regsA) && preg_match('#_(\d+)$#',$b,$regsB) ) {
                list($aId,$bId) = array($regsA[1],$regsB[1]);
                if( $aId == $bId )
                    return 0;
                return $aId < $bId ? -1 : 1;
            }
            return 0;
        });
        return $classes;
    }

    public function runDowngrade()
    {
        $scripts = $this->getMigrationScripts();
        foreach( $scripts as $script ) {
            foreach( $this->dataSourceIds as $dsId ) {
                $migration = new $script( $dsId );
                $migration->downgrade();
            }
        }
    }

    public function runUpgrade()
    {
        $scripts = $this->getMigrationScripts();
        foreach( $scripts as $script ) {
            foreach( $this->dataSourceIds as $dsId ) {
                $this->logger->info("Running migration script $script on $dsId");
                $migration = new $script( $dsId );
                $migration->upgrade();
            }
        }
    }
}
