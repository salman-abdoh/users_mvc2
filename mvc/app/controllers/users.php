<?php
require_once 'controller.php';
class Users extends Controller
{
    public function __construct()
    {

        echo "<h1>inside users controller construct</h1>";
    }
    function index()
    {

        echo "<h1>index of users</h1>";
    }
    function show($id)
    {


        $user = $this->model('user');
        $userName = $user->select($id);
        $this->view('user_view', $userName);
    }
    function add()
    {

        echo "<h1>add of users</h1>";
    }

    function add_user()
    {
        print_r($_POST);
        if(isset($_POST['submit']))
        {
            $validation = new UserValidate();
    
       
            $userName=$_POST['name'];
            $password=$_POST['password'];
            $email=$_POST['email'];

            $validation->set('name', $userName)->is_required()->min(3)
            ->set('password', $password)->is_required()->max(10)
            ->set('email', $email)->is_required()->email()
            ;
           if($validation->validation()==true)
           {
               $user_data =array(
                   'name'=>$userName,
                   'password'=>md5($password),
                   'email'=>$email
                   
               );
               $u=$this->model('user');
               $message="";
               if($u->insert($user_data)){
                   $type='success';
                    $message="user created successful";
                    $this->view('feedback',array('type'=>$type,'message'=>$message));

                }
               else {
                   $type='danger';
                   $message="can not create user please check your data ";
               
                   $this->view('register',array('type'=>$type,'message'=>$message,'form_values'=>$_POST));

                }
           } 
           else{
            $todos_errors = $validation->erorr(); 
            $er_name = $validation->erorr('name'); 
            $er_email = $validation->erorr('email'); 
            $er_pass = $validation->erorr('password'); 
            foreach ($er_name as $error){
                echo '<p>' . $error . '</p>';
            }
            foreach ($er_email as $error){
                echo '<p>' . $error . '</p>';
            }
            foreach ($er_pass as $error){
                echo '<p>' . $error . '</p>';
            }
        }

        }
        
    }
    function register()
    {
        $this->view('register');
    }

    function list_all()
    { $users=$this->model("user");
        $result=$users->select();
        $this->view('users_table',$result);

    }
    function status($id){
    $user=$this->model("user");
        $user->changeStatus($id);
        $this->list_all();

//        header('location:users/list_all');


        
    }
}

class UserValidate {

    protected $_data     = array();
    protected $_errorrs   = array();
    protected $_pattern  = array();
    protected $erorrmesege = array();

   
    public function __construct() {
        $this->seterorrmesege_default();
        $this->define_pattern();
    }


   
    public function set($name, $value){
        $this->_data['name'] = $name;
        $this->_data['value'] = $value;
        return $this;
    }


   
    protected function seterorrmesege_default(){
        $this->erorrmesege = array(
            'is_required'    => 'is_required %s ',
            'min'     => 'is_required %s at least have %s character(es)',
            'max'     => 'is_required %s must contain as much as possible %s character(es)',
            
            'email'       => 'Email %s is not valid ',
        );
    }


   
    public function get_number_validators_methods(){
        return count($this->erorrmesege);
    }

   
    public function set_message($name, $value){
        if (array_key_exists($name, $this->erorrmesege)){
            $this->erorrmesege[$name] = $value;
        }
    }


    public function geterorrmesege($param = false){
        if ($param){
            return $this->erorrmesege[$param];
        }
        return $this->erorrmesege;
    }


    public function define_pattern($prefix = '', $sufix = ''){
        $this->_pattern['prefix'] = $prefix;
        $this->_pattern['sufix']  = $sufix;
    }


    
    protected function set_errorr($errorr){
        $this->_errorrs[$this->_pattern['prefix'] . $this->_data['name'] . $this->_pattern['sufix']][] = $errorr;
    }

   
    public function is_required(){
        if (empty ($this->_data['value'])){
            $this->set_errorr(sprintf($this->erorrmesege['is_required'], $this->_data['name']));
        }
        return $this;
    }


   
    public function min($length, $inclusive = false){
        $verify = ($inclusive === true ? strlen($this->_data['value']) >= $length : strlen($this->_data['value']) > $length);
        if (!$verify){
            $this->set_errorr(sprintf($this->erorrmesege['min'], $this->_data['name'], $length));
        }
        return $this;
    }


    
    public function max($length, $inclusive = false){
        $verify = ($inclusive === true ? strlen($this->_data['value']) <= $length : strlen($this->_data['value']) < $length);
        if (!$verify){
            $this->set_errorr(sprintf($this->erorrmesege['max'], $this->_data['name'], $length));
        }
        return $this;
    }


   







    public function email(){
        if (filter_var($this->_data['value'], FILTER_VALIDATE_EMAIL) === false) {
            $this->set_errorr(sprintf($this->erorrmesege['email'], $this->_data['value']));
        }
        return $this;
    }



  

    public function validation(){
        return (count($this->_errorrs) > 0 ? false : true);
    }


    
    public function erorr($param = false){
        if ($param){
            if(isset($this->_errorrs[$this->_pattern['prefix'] . $param . $this->_pattern['sufix']])){
                return $this->_errorrs[$this->_pattern['prefix'] . $param . $this->_pattern['sufix']];
            }
            else{
                return false;
            }
        }
        return $this->_errorrs;
    }
}