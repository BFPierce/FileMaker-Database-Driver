<?php
/**
 * Class: FMDatabase
 * + Base class for FileMaker database interactions.
 * @author Jeffery White <jefferyawhite868@gmail.com>
 * 
 */
require_once('FileMaker.php');


class FMDatabase{
	private $dbConn;
	private $errorMessage;
	
	private $testURL = '[URL_HERE]';
	private $productionURL = '[URL_HERE]';

	/**
	 * Create a new instance of the database connection.
	 *
	 * @param string $database - database name.
	 * @param string $user - user name.
	 * @param string $password - database password.
	 */
	public function __construct($database, $user, $password){		
		$this->errorMessage = null;
	
		$this->dbConn = new FileMaker();
		
		$this->dbConn->setProperty('database',$database);
		$this->dbConn->setProperty('username',$user);
		$this->dbConn->setProperty('password',$password);
		
		if($_SERVER['SERVER_NAME'] == $testURL)
			$this->dbConn->setProperty('hostspec', $testURL);
		else
			$this->dbConn->setProperty('hostspec', $productionURL);	
	}	
	
	/**
	 * Get the last FileMaker error message.
	 *
	 * @return string - last filemaker message recorded.
	 */
	protected function GetError(){
		return $this->errorMessage;	
	}
	
	/**
	 * Add record to the database.
	 *
	 * @param string $layout - Database layout name.
	 * @param array $recordData - Key:value record data to add.
	 * @return bool 
	 */
	protected function AddRecord($layout, $recordData){		
		$addCommand = $this->dbConn->newAddCommand($layout, $recordData);
		$result = $addCommand->execute();
		
		if(FileMaker::isError($result)){
			$this->errorMessage = $result->getMessage();
			return false;
		} else {
			return true;
		}
	}
			
	/**
	 * Get records from the database matching the criteria.
	 *
	 * @param string $layout - Database layout name.
	 * @param array $criteria - Key:value pairs matching database fields.
	 * @return array - an array of record objects (key:value)
	 */
	protected function GetRecords($layout, $criteria = array()){
		$layoutObject = $this->db_conn->getLayout($layout);
		
		$query = $this->dbConn->newFindCommand($layout);

		foreach($criteria as $key => $value)
			$query->addFindCriterion($key, $value);	
		
		$result = $query->execute();
		
		if(FileMaker::isError($result)){
			$this->errorMessage = $result->getMessage();
			return false;
		} else {
			$data = array();
			$fields = $layoutObject->getFields();
			$records = $result->getRecords();
			
			foreach($records as $record){
				foreach($fields as $field)
					$obj[$field->getName()] = $record->getField($field->getName());	

				array_push($data[], $obj);
			}				
			return $data;
		}	
	}
	
	/**
	 * Update records matching a provided criteria.
	 *
	 * @param string $layout - Database layout name.
	 * @param array $criteria - Key:value matching database fields.
	 * @param array $updateData - Key:value pairs to update for each found record.
	 * @return bool
	 */
	protected function UpdateMatching($layout, $criteria, $updateData){		
		$query = $this->db_conn->newFindCommand($layout);
	
		foreach($criteria as $key => $value)
			$query->addFindCriterion($key, $value);	
		
		
		$results = $query->execute();
		
		if(FileMaker::isError($results)){
			$this->errorMessage = $results->getMessage();
			return false;	
		} else {
			$records = $results->getRecords();
				
			foreach($records as $record){
				foreach($updateData as $key => $value)
					$record->setField($key, $value);	
				
				$result = $record->commit();
				
				if(FileMaker::isError($result)){
					$this->errorMessage = $result->getMessage();
					return false;
				}
			}	
		}

		return true;
	}
	
	/**
	 * Delete a record by its recordID.
	 *
	 * @param string $layout - Database layout name.
	 * @param string $recordID - Record ID of the record to delete.
	 * @return bool
	 */
	protected function DeleteRecord($layout, $recordID){        
		$query = $this->db_conn->newFindCommand($layout); 
		$query->addFindCriterion('recordID',$recordID);
		
		$results = $query->execute();
		$records = $results->getRecords();
		$record = $records[0];
		
		$results = $record->delete();
			
		if(FileMaker::isError($results)){
			$this->errorMessage = $results->getMessage();
			return false;
		} else {
			return true;	
		}
	}
}