<?php
//----------------------------------------------------------------------------//
//
//   Data table model 
//
//----------------------------------------------------------------------------//
class Datatable
{
    protected $db, $table, $names, $key, $autoincrement;

    // method declaration
    // Constructor
    // &$db - reference to PDO database handler
    // $table - name of data table
    // $names - array of data fields names
    // $filename - name of file to store the data
    // $key - unic primary key identifier field name
    // $autoincrenent - if true $key value will be autoincrement by insert()
    //
    public function __construct( &$db, $table, $names, $key='id', $autoincrement=true) {
       $this->db = $db;
       $this->table=$table;
       $this->names = $names;
       $this->key = $key;
       $this->autoincrement = $autoincrement;

       $query="CREATE TABLE IF NOT EXISTS ".$this->table." ( ";
       foreach( $this->names as $v ) {
         if( $this->autoincrement ){ 
            if($this->key==$v) $query .= " $v INTEGER PRIMARY KEY AUTOINCREMENT, ";
            else $query .= " $v TEXT, ";
         }else{
            if($this->key==$v) $query .= " $v TEXT PRIMARY KEY, ";
            else $query .= " $v TEXT, ";
         }
       }
       $query = substr($query,0, strlen($query)-2);  
       $query.=" )";
       try{  $this->db->exec($query); }
       catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode(); exit; }
    }

    protected function query_insert($data){
       $query="insert into ".$this->table." ( ";
       foreach( $this->names as $v ) {
         if( $this->autoincrement and ($this->key==$v) ) continue; 
         $query .= " $v, ";
       }
       $query = substr($query,0, strlen($query)-2);
       $query.=" ) values ( ";
       foreach( $this->names as $v ) {
         if( $this->autoincrement and ($this->key==$v) ) continue; 
         $query .= " '$data[$v]', ";
       }
       $query = substr($query,0, strlen($query)-2);
       $query.=" )";
       return $query;
    }    

    public function insert($data) {
       $query = $this->query_insert($data);
       try{ $r = $this->db->exec($query); }
       catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode()."<br />\nQuery: $query";  exit;}
       return $r;           
    }

    public function getAll($val=false,$key=false, $order="", $where="", $like="") {
       if(!$key) $key=$this->key;
       if($val) $query="select * from ".$this->table." where $key='$val'".(($order)?" ORDER BY $order ":"");
       else $query="select * from ".$this->table.(($where)?" WHERE $where ":"").(($like)?" LIKE '%$like%' ":"").(($order)?" ORDER BY $order ":""); 
       try{ $r = $this->db->query($query); }
       catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode()."<br />\nQuery: $query"; exit;}
       $result=array();
       while( $data = $r->fetch(\PDO::FETCH_ASSOC) ){
          $result[$data[$this->key]] = $data;
       }
       return $result;           
    }

    public function getNames(){ return $this->names; }
    
   
    public function update($data) {
    
      $query="update ".$this->table." set ";
      foreach( $this->names as $v ) {
         if( $this->autoincrement and ($this->key==$v) ) continue; 
         $query .= " $v ='$data[$v]', ";
       }
       $query = substr($query,0, strlen($query)-2);
       $query .= "where ".$this->key." = '".$data[$this->key]."'";
      try{ $r = $this->db->query($query); }
      catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode()."<br />\nQuery: $query"; exit;}
      return $r; 

    }

    public function delete($id,$key=false) {
      $result = false;
      if(!$key) $key=$this->key;
      $query="delete from ".$this->table." where $key='$id'";
      try{ $r = $this->db->query($query); $result = true;}
      catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode()."<br />\nQuery: $query"; exit;}
      return $result; 
       
    }

    public function get($val,$key=false) {
    
      if(!$key) $key=$this->key;
      $query="select * from ".$this->table." where $key='$val'";
      try{ $r = $this->db->query($query); }
      catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode()."<br />\nQuery: $query"; exit;}
      $result = $r->fetch(\PDO::FETCH_ASSOC);
      return $result;  
       
    }
    
    public function getLastItem($key="date"){
    
      $query="select * from ".$this->table." ORDER BY ".$key." desc";
      try{ $r = $this->db->query($query); }
      catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode()."<br />\nQuery: $query"; exit;}

      $result=$r->fetch(\PDO::FETCH_ASSOC);
      
      return $result;  
       
    }
    
// end of class datatable
}
