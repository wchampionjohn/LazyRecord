<?php

class SchemaGeneratorTest extends PHPUnit_Framework_TestCase
{

	function getLogger()
	{
		return new TestLogger;
	}


	function test()
	{
		$generator = new LazyRecord\SchemaGenerator;
		$generator->addPath( 'tests/schema/' );
		$generator->setLogger( $this->getLogger() );
		$generator->setTargetPath( 'tests/build/' );
		$classMap = $generator->generate();

		foreach( $classMap as $class => $file ) {
			var_dump( $file ); 
			path_ok( $file , $class );
#  			unlink( $file );
		}

#  		$files = array();
#  		$files[] = 'tests/build/BookSchemaProxy.php';
#  		$files[] = 'tests/build/tests/AuthorSchemaProxy.php';
#  		$files[] = 'tests/build/tests/AuthorBookSchemaProxy.php';
#  		$files[] = 'tests/build/tests/BookSchemaProxy.php';
#  		$files[] = 'tests/build/tests/BookBase.php';
#  		$files[] = 'tests/build/tests/Book.php';

	}

}