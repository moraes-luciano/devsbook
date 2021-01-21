<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;



class ConfigController extends Controller {

    private $loggedUser;
    
    public function __construct(){

        $this->loggedUser = UserHandler::checkLogin();
       
        if($this->loggedUser === false){
            $this->redirect('/login');
        }
    }
   
    public function updateAccount(){

        $flash = [];

        $_SESSION['flash'] = (array)$_SESSION['flash'];
        foreach($_SESSION['flash'] as $key => $value){
            if(!empty($value)){
                $flash+= [$key => $value];
            }
            
            unset($_SESSION['flash'][$key]);
        }
   
        $_SESSION['flash']='';

        $this->render('settings',[
            'loggedUser' =>$this->loggedUser,
            'flash' => $flash
        ]);

    
    }




//   $_SESSION['flash']=['flash_birthdate'=> 'Data inválida'];
//   $_SESSION['flash']+=['flash_password' => 'Senha inválida'];
  
//   foreach($_SESSION['flash'] as $key => $value){
//       if(empty($value)){
//           unset($_SESSION['flash'][$key]);
//       }
//   }
  
//   if(empty($_SESSION['flash'])){
//       print_r('Sem erros');
//   }
//   else{
//       print_r($_SESSION['flash']);
//   }
  
    public function updateAction(){
        
        $userChanges = [
            'name' => filter_input(INPUT_POST,'name',FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL),
            'city' => filter_input(INPUT_POST,'city'),
            'work' => filter_input(INPUT_POST, 'work'),
        ];

        $userBirthdate = filter_input(INPUT_POST,'birthdate');
        
        if($userBirthdate){
            
            $userBirthdate = UserHandler::birthdateCheck($userBirthdate);
            if($userBirthdate){
                $userChanges+=['birthdate'=>$userBirthdate];
            }
            else{
                $_SESSION['flash']=['flash_birthdate'=> 'Data de nascimento inválida'];
            }
            
        }

        $password = filter_input(INPUT_POST, 'password');
        $newPassword = filter_input(INPUT_POST, 'newPassword');
   
        $userId = $this->loggedUser->id;


        if($newPassword && $password){

            
            $securityData = UserHandler::securityValidation($userId,$password,$newPassword); 
           
            if($securityData){
                $userChanges = array_merge($userChanges,$securityData);

                UserHandler::updateData($userChanges,$userId);

                $_SESSION['token'] = $userChanges['token'];
                $this->redirect('/config');
            }
            else{

                $_SESSION['flash']+=['flash_password' => 'Senha inválida'];

                // print_r($_SESSION['flash']);
                // print_r('chegou aqui');
                // foreach($_SESSION['flash'] as $key => $value){
                //     print_r($key);
                //     print_r('|||');
                // }
                $this->redirect('/config');

            }
            
        }
        
        
        UserHandler::updateData($userChanges,$userId);
        $this->redirect('/config');
       
    }
   

}