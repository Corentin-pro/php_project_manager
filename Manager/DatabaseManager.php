<?php

// Including the manager automaticaly connects to the database
namespace
{
	global $pdo;

	try
	{
		switch (DATABASE_TYPE)
		{
			case 'mysql':
				if( !empty(DATABASE_CUSTOM_DSN) )
				{
					$database_dsn = DATABASE_CUSTOM_DSN;
				}
				else
				{
					$database_dsn = 'mysql:host='.DATABASE_HOST.(!empty(DATABASE_PORT)?';port='.DATABASE_PORT:'').';dbname='.DATABASE_NAME;
				}
				$pdo = new PDO( 'mysql:'.$database_dsn, DATABASE_LOGIN, DATABASE_PASSWORD, array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8') );

				// Set for UTC time
				\DatabaseManager\exec("SET time_zone='+00:00';");
				break;
			default:
				$pdo = new PDO('sqlite:'.DATABASE_NAME);
				break;
		}
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}
	catch (PDOException $pdo_exception)
	{
		DatabaseManager\printPDOException($pdo_exception);
	}
}

namespace DatabaseManager
{
	/**
	 * Shows an error message
	 * @param   string  $p_error_message  Error string
	 */
	function showError($p_error_message)
	{
		global $controller;
		// If a controller is instanciate, giving the message to it instead of just echoing
		if( null !== $controller )
		{
			$controller->error .= $p_error_message;
		}
		else
		{
			echo $p_error_message;
		}
	}

	/**
	 * PDOException handling
	 * @param   PDOException  $p_pdo_exception       PDOException to handle
	 * @param   string        $p_additional_message  Additional message to output after the PDOException messages (used for execute parameters)
	 */
	function printPDOException($p_pdo_exception, $p_additional_message = '')
	{
		showError("<pre>" . $p_pdo_exception->getMessage() . "\n" . getExceptionTraceAsString($p_pdo_exception) . "\n" . $p_additional_message . "</pre>");
	}


	/**
	 * Safely execute database operation and roll back if needed (function should return its success)
	 * @param   function  $p_operation_function  Operation to process (anonymous function)
	 * @return  boolean                          Transaction success
	 */
	function transaction($p_operation_function)
	{
		global $pdo;
		$pdo->beginTransaction();
		if( $p_operation_function() )
		{
			$pdo->commit();
			return true;
		}
		else
		{
			$pdo->rollBack();
		}
		return false;
	}

	/**
	 * PDO::exec equivalent with PDOException handling
	 * @param   string   $p_query               SQL query to execute
	 * @return  integer                         Number of affected lines
	 */
	function exec($p_query)
	{
		global $pdo;
		if(null === $pdo)
		{
			return false;
		}

		try
		{
			return $pdo->exec($p_query);
		}
		catch (\PDOException $pdo_exception)
		{
			var_dump($pdo_exception);
			printPDOException($pdo_exception);
			return false;
		}
	}

	/**
	 * PDOStatement::prepare equivalent with PDOException handling and table regeneration if needded
	 * @param   string        $p_query               SQL query to execute
	 * @param   array         $p_driver_options      See PDO::prepare man
	 * @return  PDOStatement                         Can return false if the query fails
	 */
	function prepare($p_query, $p_driver_options = array() )
	{
		global $pdo;
		if(null === $pdo)
		{
			return false;
		}

		try
		{
			return $pdo->prepare($p_query, $p_driver_options);
		}
		catch (\PDOException $pdo_exception)
		{
			printPDOException($pdo_exception);
			return false;
		}
	}

	/**
	 * PDOStatement::execute equivalent with PDOException handling
	 * @param   PDOStatement  $p_statement           PDOStatement to execute
	 * @param   array         $p_input_parameters    Parameters for the execute
	 * @return  object                               Return an object depending on the default PDO configuration or previous prepare's $p_driver_options
	 */
	function execute( $p_statement, $p_input_parameters = array() )
	{
		try
		{
			return $p_statement->execute($p_input_parameters);
		}
		catch (\PDOException $pdo_exception)
		{
			$addidtional_message = "\nstatement :\n" . $p_statement->queryString;
			if( !empty($p_input_parameters) )
			{
				$addidtional_message .= "\n\ninput parameters :\n" . var_export($p_input_parameters,true);
			}
			printPDOException($pdo_exception, $addidtional_message);
		}

		return false;
	}

	/**
	 * Default function used to insert a ManagedObject in the database (make sure the ManagedObject's array_descriptor corresponds to the table's columns)
	 * @param   ManagedObject  $p_object      ManagedObject to be saved
	 * @param   string         $p_table_name  Name of the table to insert the data
	 * @return  boolean                       Operation success
	 */
	function insert($p_object, $p_table_name)
	{
		$valid_parameters= array();
		$execute_parameters = array();
		$array_descriptor = $p_object->getArrayDescriptor();
		foreach($array_descriptor as $attribute => $type)
		{
			if( 'readonly' !== $type )
			{
				$valid_parameters[] = $attribute;
				$execute_parameters[':' . $attribute] = (!empty($p_object->$attribute)) ? $p_object->$attribute : null;
				if( ('int' === $type) || ('float' === $type) || ('double' === $type) )
				{
					settype($execute_parameters[':' . $attribute], $type);
				}
				else if( 'password' === $type )
				{
					$execute_parameters[':' . $attribute] = password_hash($execute_parameters[':' . $attribute], PASSWORD_BCRYPT);
				}
			}
		}
		if( $statement = \DatabaseManager\prepare('INSERT INTO ' . $p_table_name . ' ('
			. implode(',',$valid_parameters) . ') VALUES (' . implode(',',array_keys($execute_parameters)) . ');') )
		{
			return \DatabaseManager\execute($statement , $execute_parameters );
		}
		return false;
	}

	/**
	 * Default function used to update a ManagedObject in the database (make sure the ManagedObject's array_descriptor corresponds to the table's columns)
	 * @param   ManagedObject  $p_object      ManagedObject to be updated
	 * @param   string         $p_table_name  Name of the table to insert the data
	 * @return  boolean                       Operation success
	 */
	function update($p_object, $p_table_name)
	{
		$query_set_array = array();
		$execute_parameters = array(':id' => (int)$p_object->id);
		$array_descriptor = $p_object->getArrayDescriptor();
		foreach($array_descriptor as $attribute => $type)
		{
			switch( $type )
			{
				case 'readonly':
				case 'fixed':
					break;
				case 'int':
				case 'float':
				case 'double':
					$query_set_array[] = $attribute . '=:' . $attribute;
					$execute_parameters[':' . $attribute] = (!empty($p_object->$attribute)) ? $p_object->$attribute : null;
					settype($execute_parameters[':' . $attribute], $type);
					break;
				case 'password':
					if( !empty($p_object->$attribute) ) // password can be left empty for update (can't be retrieve as placeholder)
					{
						$query_set_array[] = $attribute . '=:' . $attribute;
						$execute_parameters[':' . $attribute] = password_hash($p_object->$attribute , PASSWORD_BCRYPT);
					}
					break;
				default:
					$query_set_array[] = $attribute . '=:' . $attribute;
					$execute_parameters[':' . $attribute] = (!empty($p_object->$attribute)) ? $p_object->$attribute : null;
			}
		}
		$query_set = implode(', ', $query_set_array);

		if( $statement = \DatabaseManager\prepare('UPDATE ' . $p_table_name . ' SET ' . $query_set . ' WHERE id=:id;') )
		{
			return \DatabaseManager\execute($statement , $execute_parameters );
		}
		return false;
	}

	/**
	 * Get nice trace without ellipses
	 * @param   PDOException  $p_exception  PDOException to be parsed
	 * @return  string
	 */
	function getExceptionTraceAsString($p_exception)
	{
		$output = "";
		$count = 0;
		foreach ($p_exception->getTrace() as $frame)
		{
			$arguments = "";
			if (isset($frame['args']))
			{
				$arguments = array();
				foreach ($frame['args'] as $value)
				{
					if (is_string($value))
					{
						$arguments[] = "'" . $value . "'";
					}
					elseif (is_array($value))
					{
						$arguments[] = "Array";
					}
					elseif (is_null($value))
					{
						$arguments[] = 'NULL';
					}
					elseif (is_bool($value))
					{
						$arguments[] = ($value) ? "true" : "false";
					}
					elseif (is_object($value))
					{
						$arguments[] = get_class($value);
					}
					elseif (is_resource($value))
					{
						$arguments[] = get_resource_type($value);
					}
					else
					{
						$arguments[] = $value;
					}
				}
				$arguments = join(", ", $arguments);
			}
			$output .= sprintf( "#%s %s(%s): %s(%s)\n",
			$count,
			$frame['file'],
			$frame['line'],
			$frame['function'],
			$arguments );
			$count++;
		}
		return $output;
	}
}

 ?>
