<?php
//**********************************************************************************************************
//*
//*   	Class: FMDatabase - Base class for interactions with a FileMaker Database.
//*
//**********************************************************************************************************
require_once('FileMaker.php');

// error_reporting(E_ERROR | E_WARNING | E_PARSE);

class FMDatabase 
{
	private $db_conn;
    private $err_message;
    
    private $testURL = '[URL_HERE]';
    private $productionURL = '[URL_HERE]'

	//------------------------------------------------------------------------------------------------------
	//  Connect to FM Database
	//------------------------------------------------------------------------------------------------------
	public function __construct($database, $user, $password){		
		$this->err_message = "";
	
		$this->db_conn = new FileMaker();
		
		$this->db_conn->setProperty('database',$database);
		$this->db_conn->setProperty('username',$user);
		$this->db_conn->setProperty('password',$password);
        
        // Handle test and development environments
		if($_SERVER['SERVER_NAME'] == $testURL)
			$this->db_conn->setProperty('hostspec', $testURL);
		else
			$this->db_conn->setProperty('hostspec', $productionURL);
        
        // Depending on your environment you might need this.
		//$this->db_conn->setProperty('curlOptions', array(CURLOPT_SSL_VERIFYPEER => false));		
	}	
	
	//------------------------------------------------------------------------------------------------------
	protected function GetError(){
		return $this->err_message;	
	}
	
	//------------------------------------------------------------------------------------------------------
	//  Add a record to a layout
	//------------------------------------------------------------------------------------------------------
	protected function AddRecord($layout, $record_data){		
		$add_command = $this->db_conn->newAddCommand($layout,$record_data);
		$result = $add_command->execute();
		
		if(FileMaker::isError($result))
		{
			$this->err_message = $result->getMessage();
			return false;
		}
		else
		{
			return true;
		}
	}
			
	//------------------------------------------------------------------------------------------------------
	//  Get all records from this layout matching the search criteria.
	//------------------------------------------------------------------------------------------------------
	protected function GetRecords($layout, $criteria = array()){
        $layoutObject = $this->db_conn->getLayout($layout);
		
		$query = $this->db_conn->newFindCommand($layout);

		foreach($criteria as $key => $value)
		{
			$query->addFindCriterion($key,$value);	
		}

		
		$result = $query->execute();
		
		if(FileMaker::isError($result))
		{
			$this->err_message = $result->getMessage();
			return false;
		}
		else
		{
			$data = array();
			$fields = $layoutObject->getFields();
			$records = $result->getRecords();
			
			foreach($records as $record)
			{
				foreach($fields as $field)
				{
					$ob[$field->getName()] = $record->getField($field->getName());
				}	
				
				$data[] = $ob;
			}	
			
			return $data;
		}	
	}
	
	//------------------------------------------------------------------------------------------------------
	//  Update records matching the search criteria.
	//------------------------------------------------------------------------------------------------------
	protected function UpdateMatching($layout, $criteria, $update_data){		
		$query = $this->db_conn->newFindCommand($layout);
	
		foreach($criteria as $key => $value)
		{
			$query->addFindCriterion($key,$value);	
		}
		
		$results = $query->execute();
		
		if(FileMaker::isError($results))
		{
			$this->err_message = $results->getMessage();
			return false;	
		}
		else
		{
			$records = $results->getRecords();
				
			foreach($records as $record)
			{
				foreach($update_data as $key => $value)
				{
					$record->setField($key,$value);
				}	
				
				$result = $record->commit();
				
				if(FileMaker::isError($result))
				{
					$this->err_message = $result->getMessage();
					return false;
				}
			}	
		}
		
		return true;
    }
    
    //------------------------------------------------------------------------------------------------------
	// Delete record by the record ID
	//------------------------------------------------------------------------------------------------------
	protected function DeleteRecord($layout, $record_id){        
        $query = $this->db_conn->newFindCommand($layout); 
		$query->addFindCriterion('recordID',$record_id);
		
		$results = $query->execute();
		$records = $results->getRecords();
		$record = $records[0];
		
		$results = $record->delete();
			
		if(FileMaker::isError($results))
		{
			$this->err_message = $results->getMessage();
			return false;
		}
		else
		{
			return true;	
		}
	}
}