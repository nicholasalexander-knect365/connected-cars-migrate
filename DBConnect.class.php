<?php

class DBConnect {
	
	public $server;
	public $dbname;
	public $user;
	public $pass;
	protected $connection;

	public function __construct($dbname, $user, $pass, $server)
	{
	    
	    $this->dbname = $dbname;
	    $this->user = $user;
	    $this->pass = $pass;
	    $this->server = $server;
	    $this->connection = null;
	}

	public function getConnection() {
		if (!$this->connection) {
			$this->connect();
		}
		return $this->connection;
	}

	private function connect() {
	    // Create connection
		$this->connection = new mysqli($this->server, $this->user, $this->pass, $this->dbname);

		// Check connection
		if ($this->connection->connect_error) {
		    throw new Exception("Connection failed: " . $this->connection->connect_error);
		}
	}
}