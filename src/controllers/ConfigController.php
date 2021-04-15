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
                $this->redirect('/config');
            }
            
        }

        // Profile - userImage

        if(isset($_FILES['userPicture']) && !empty($_FILES['userPicture']['tmp_name'])){
            $newUserPicture = $_FILES['userPicture'];

            if(in_array($newUserPicture['type'],['image/jpeg', 'image/jpg', 'image/png'])){
                $userPictureName = $this->cutImage($newUserPicture,200,200,'media/profile');
                $userChanges+=['userPicture'=>$userPictureName];
               
            }
        }
        
        // Cover Img 


        if(isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])){
            $newCover = $_FILES['cover'];

            if(in_array($newCover['type'],['image/jpeg', 'image/jpg', 'image/png'])){
                $coverName = $this->cutImage($newCover,850,310,'media/covers');
                $userChanges+=['coverPicture'=>$coverName];
            }
        }


        UserHandler::updateData($userChanges,$userId);
        $this->redirect('/config');
       
    }
   
    private function cutImage($file, $width, $height, $destFolder){
        list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);
        $ratio = $widthOrig/$heightOrig;

        $newWidth = $width;
        $newHeight = $newWidth/$ratio;

        if($newHeight < $height){
            $newHeight = $height;
            $newWidth = $newHeight * $ratio;
        }

        $x = $width - $newWidth;
        $y = $height - $newHeight;
        $x =$x <0? $x/2: $x;
        $y =$y <0? $y/2: $y;

        $finalImg = imagecreatetruecolor($width,$height);
        switch($file['type']){
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
        }

        imagecopyresampled(
            $finalImg, $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight, $widthOrig, $heightOrig
        );

        $fileName = md5(time().rand(0,9999)).'.jpg';
        imagejpeg($finalImg, $destFolder.'/'.$fileName);

        return $fileName;

    }
}