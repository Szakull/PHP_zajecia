<?php
include("datatable.php");



class Notebook
{
    // property declaration
    public $u=false, $g = false, $context, $error="", $baseurl;
    protected $db,  $user, $note, $visit;

    public function __construct() {
      // Tworzenie obiektu klasy PDO - baza danych SQLite
      // Zobacz: http://www.sqlitetutorial.net/sqlite-php
      try{ 
         $this->db = new PDO('sqlite:'.dirname(__FILE__).'/db.sq3'); 
         $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch(PDOException $e){ echo $e->getMessage().": ".$e->getCode();  exit; }
      
      // Model danych - tablice bazy danych
      $this->note  = new Datatable( $this->db, "note", array("note", "notetitle", "userid","date","noteid"), "noteid" );
      $this->user  = new Datatable( $this->db, "user", array("userid", "username","userlevel","pass"), "userid", false );
      $this->visit = new Datatable( $this->db, "visit", array("guestid", "description", "ip", "url", "date", "userid", "visitid"), "visitid");
       

      // generowanie id goscia nieisniejacego jeszcze
      do{
           $tmp = uniqid();
      }
      while($this->visit->get($tmp, "guestid"));
      
      
      // inicjacja parametrów
      $this->baseurl = "index.php";
      $this->context = (isset($_SESSION["context"]))?$_SESSION["context"]:NULL;
      $this->u = (isset($_SESSION["user"]))?$_SESSION["user"]:false;
      $this->g = (isset($_COOKIE['guestid']))?$_COOKIE["guestid"]:$tmp;
      
      
      // administrator
      $admin = $this->user->get("admin");
      if( !isset($admin['userid']) )
          $this->user->insert(array( "userid"=>"admin", "username"=>"admin","userlevel"=>10,"pass"=>md5("admin") ));
    }                               
    
    

    public function login($userid,$pass){
         if( !($this->u=$this->user->get($userid)) ) {
            $this->error="Bad user name or password!";
            $this->u = NULL;
            return false;
         } 
         if( $this->u["pass"]!=md5($pass) ){
            $this->error="Bad user name or password!"; 
            $this->u = NULL;
            return false;
         }
         $this->activity("logowanie");
         $_SESSION = array();
         session_regenerate_id();
         $_SESSION["token"] = md5(session_id().__FILE__);
         $_SESSION["user"] = $this->u;
         $_SESSION["context"] = NULL;
         $this->reload();
    }
    public function logout(){
       $this->activity("wylogowanie");
       $_SESSION = array();
       if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
          );
       }
       session_destroy();
       $this->reload();
    }
    public function register($userid,$username,$pass){
       if($u=$this->user->get($userid)){
          $this->error .= "Bad username";return false; }
       $u = array("userid"=>$userid,"username"=>$username,"userlevel"=>0,"pass"=>md5($pass));   
       $this->user->insert($u);
       $_SESSION["user"] = $u;
       $_SESSION["context"] = NULL;
       $this->activity("rejestracja");
       $this->reload();
    }
    public function insert_note($notetitle,$note){
       if($this->note->insert(array("note"=>$note,"notetitle"=>$notetitle,"userid"=>$this->u['userid'], "date"=>date("Y-m-d H:i:s") ))){
         $this->activity("dodano notatke");
           $this->reload();
       }
       return false;
    }
    public function delete_note($noteid){
       if($this->note->delete($noteid)){
         $this->activity("usunieto notatke"); 
         $this->reload();
         }

       return false;
    }
    public function update_note($noteid,$notetitle,$note){
       if($this->note->update(array("note"=>$note,"notetitle"=>$notetitle,"userid"=>$this->u['userid'],"date"=>$this->note->get($noteid)['date'],"noteid"=>$noteid ))){
         $this->activity("aktualizacja notatki");
         $this->reload();}
       return false;
    }
  
    public function delete_user($userid){
      $this->activity("Usuniecie uzytkowanika");
         if($n = $this->note->getAll($userid, "userid")){
            foreach($n as $k=>$v) {
               $this->note->delete($k);
            }
         } 
          if( $this->user->delete($userid) ) $this->reload();
          else return false;
    }
    public function update_user($userid){
      $this->activity("aktualizacja uzytkowanika");
       if($u = $this->user->get($userid)){
          $u['userlevel']=($u['userlevel']==10)?0:10;
          if( $this->user->update($u) ) $this->reload();
          else return false;
       }else return false;
    }
    
    function activity($description){
       $this->visit->insert(array("guestid" => $this->g,
                                 "description" => $description,
                                 "ip" => "123",//$_SERVER['HTTP_X_FORWARDED_FOR'],
                                 "url" => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                                 "date" => date("Y-m-d H:i:s"),
                                 "userid" => ($this->u)?$this->u['userid']:"Guest"));
    }
//-----------------------------------------
//---------- metoda prosess() -------------
//-----------------------------------------    
public function process(){

if( isset($_SESSION["token"]) and $_SESSION["token"] != md5(session_id().__FILE__)) $this->logout();

//przedluzenie/stworzenie ciasteczka dla aktualnego goscia
setcookie("guestid", $this->g, time()+3600*24*7); 

$data = array( "last_note"=>($lastnote = $this->note->getLastItem())?$lastnote["date"]:"- brak notatek -",
               "error1"=>"",
               "error"=>"",
               "note" =>""
             );
             
//---------- Akcje publiczne -------------

//logowanie
if(isset($_POST['userid']) and $_POST['userid']!="" and isset($_POST['pass'])){
   if( !$this->login($_POST['userid'],$_POST['pass']) ){
      $data["error1"]=$this->error;
   }
}

//rejestracja
if(isset($_POST['userid']) and isset($_POST['pass1']) and $_POST['pass1']!="" and ($_POST['pass1']==$_POST['pass2'])){
      if( !$this->register($_POST['userid'],$_POST['username'],$_POST['pass1']) )
         $data["error"]=$this->error;
}

//strona z rejestracja
if(isset($_GET['cmd']) and $_GET['cmd']=='register'){
   $this->activity("Wejscie na strone rejestracji");
   $_SESSION["context"] = $this->context = "register";
   $this->reload();
}

//strona z logowaniem
if(isset($_GET['cmd']) and $_GET['cmd']=='login'){
   $this->activity("Wejscie na strone logowania");
   $_SESSION["context"] = $this->context = "login";
   $this->reload();
}
//strona glowna
if(isset($_GET['cmd']) and $_GET['cmd']=='home'){
   $this->activity("wejscie na strone głowna");
   $_SESSION["context"] = $this->context = NULL;
   $this->reload();
}

if(isset($_GET['cmd']) and $_GET['cmd']=='action'){
   $this->activity("Wejscie na strone z aktywnoscia");
   $_SESSION["context"] = $this->context = "action";
   $this->reload();
}

if(isset($_GET['cmd']) and $_GET['cmd']=='mynotes'){
   $this->activity("Wejscie na strone z moimi notatkami");
   $_SESSION["context"] = $this->context = "mynotes";
   $this->reload();
}

//wylogowanie
if(isset($_GET['cmd']) and $_GET['cmd']=='logout'){
   $this->logout();
}

//lista_uzytkownikow
if(isset($_GET['cmd']) and $_GET['cmd'] == "userlist"){
   $this->activity("wlaczenie/wylaczenie listy uzytkownikow");
   (isset($_SESSION['userlist']) and $_SESSION['userlist'])? $_SESSION['userlist'] = false : $_SESSION['userlist'] = true;
   $this->reload();
}

//edycja uzytkowanika
if(isset($_GET['cmd']) and $_GET['cmd'] == "changeuser"){
   $this->update_user($_GET['userid']);
}

//usuwanie uzytkownika
if(isset($_GET['cmd']) and $_GET['cmd'] == "deluser") {
   $this->delete_user($_GET['userid']);
}

if(isset($_POST['note']) and $_POST['note']!=''){
   if($_POST['noteid']!='')
      $this->update_note($_POST['noteid'],$_POST['notetitle'], $_POST['note']);
   else
      $this->insert_note($_POST['notetitle'], $_POST['note']);
}

if(isset($_GET['cmd']) and $_GET['cmd'] == "noteedit"){
   $data['note'] = $this->note->get($_GET['noteid']);
}
if(isset($_GET['cmd']) and $_GET['cmd'] == "notedelete"){
$this->delete_note($_GET['noteid']);
}

if(!$this->context)
{
   if(isset($_POST['search']) and $_POST['search'] != ""){
      $data["notes"] = $this->note->getAll(false, false, "date asc", $_POST['where'], $_POST['search']);
   }
   else $data["notes"] = $this->note->getAll(false, false, "date asc");

   $data['users']=$this->user->getAll();
}

if($this->context == "mynotes"){
   $user = $this->u['userid'];
   if(isset($_POST['search']) and $_POST['search'] != ""){
      $where = "userid = '$user' and ".$_POST['where'];
      $data["notes"] = $this->note->getAll(false, false, "date asc", $where, $_POST['search']);
   }
   else $data["notes"] = $this->note->getAll(false, false, "date asc", "userid = '$user'");

   $data['users']=$this->user->getAll();
}

if($this->context == "action"){
   if(isset($_POST['submit'])){
      $where = "description LIKE '%$_POST[search]%'";
      $where .= " and userid LIKE '%$_POST[user]%'";
      $where .= " and guestid LIKE '%$_POST[guest]%'";
      $where .= " and ip LIKE '%$_POST[ipadress]%'";
      $where .= " and url LIKE '%$_POST[url]%'";
      $where .= ($_POST['date_from'])?" and date >= '".str_replace("T"," ",$_POST['date_from'])."'":"";
      $where .= ($_POST['date_to'])?" and date <= '".str_replace("T"," ",$_POST['date_to'])."'":"";
      
      $data["actions"] = $this->visit->getAll(false, false, "date desc", $where);
   }
   else $data["actions"] = $this->visit->getAll(false, false, "date desc");

   $data['users']=$this->user->getAll();
}


return $data;
} //---- end of function process()


//-----------------------------------------
//---------- metoda makepage() -------------
//-----------------------------------------    
  public function makepage($data){
    $this->view("header",$data);
    switch($this->context){
      case "login":
         $this->view("menu",$data);
         $this->view("login",$data);
      break;
      case "register":
        $this->view("menu",$data);
        $this->view("register",$data);
      break;  
      case "action":
         $this->view("menu",$data);
         $this->view("action",$data);
       break; 
      default:
         $this->view("menu",$data);
         $this->view("notes", $data);
    }
    $this->view("footer",$data);
  } 
    
//-----------------------------------------
//---------- metoda view() -------------
//-----------------------------------------    
  public function view($view,$data=NULL,$tostring=false){
      $buf="";
      if($data) extract($data);
      if($tostring) ob_start();
      include("view/$view.php");
      if($tostring) { 
         $buf = ob_get_contents();
         ob_end_clean();
         return $buf;
      }
  }
  
  protected function reload(){
     header("Location: $this->baseurl");
     exit;
  }
  
} //------ end of class Form ---------------------------------------------------   