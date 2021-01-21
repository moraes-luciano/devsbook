<?php
namespace src\handlers;

use \src\models\User;
use \src\models\UserRelation;
use \src\handlers\PostHandler;




class UserHandler {

    public static function checkLogin(){
        
        if(!empty($_SESSION['token'])){

            $token = $_SESSION['token'];
            
            $data = User::select()->where('token', $token)->one();
            if(count($data) > 0){
                
                $loggedUser = new User();
                $loggedUser->id = $data['id'];
                $loggedUser->email = $data['email'];
                $loggedUser->name = $data['name'];
                $loggedUser->birthdate = $data['birthdate'];
                $loggedUser->city = $data['city'];
                $loggedUser->work = $data['work'];
                $loggedUser->userPicture = $data['userPicture'];
 

                return $loggedUser;
            }


        }
          return false;

    }

    public static function verifyLogin($email, $password){

        $user = User::select()->where('email', $email)->one();

        if($user){
            if(password_verify($password, $user['password'])){
                $token = md5(time().rand(0,9999).time());
                User::update()->set('token',$token)->where('email',$email)->execute();
                
                return $token;
            }
        }

        return false;
    }


    public static function emailExists($email){
        
        $user = User::select()->where('email', $email)->one();
        return $user? true : false;
    }

    public static function getUser($id, $full = false){
        
        $data = User::select()->where('id', $id)->one();

        if($data){

            $user = new User();

            $user->id = $data['id'];
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->birthdate = $data['birthdate'];
            $user->city = $data['city'];
            $user->work = $data['work'];
            $user->userPicture = $data['userPicture'];
            $user->coverPicture = $data['coverPicture'];
            
            if($full){

                $user->followers = [];
                $user->following = [];
                $user->photos = [];

                //followers

                $followers = UserRelation::select()->where('user_to', $id)->get();
                foreach($followers as $follower){
                    $userData = User::select()->where('id', $follower['user_from'])->one();
                    
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->userPicture = $userData['userPicture'];
                    
                    $user->followers[] = $newUser;
                }

                //following

                $following = UserRelation::select()->where('user_from', $id)->get();
                foreach($following as $follower){
                    $userData = User::select()->where('id', $follower['user_to'])->one();
                    
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->userPicture = $userData['userPicture'];
                    
                    $newUser->following[] = $newUser;
                }

                //photos

                $user->photos = PostHandler::getPhotosFrom($id);
               

            }
            
            return $user;
        }
        
        return false;
    }

    public static function addUser($name, $email, $password, $birthdate){

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = md5(time().rand(0,9999).time());

        User::insert([
            'email' => $email,
            'password' => $hash,
            'name' => $name,
            'birthdate' => $birthdate,
            'token' =>$token
        ])->execute();

        return $token;
    }


    

    public static function idExists($id){
        $user = User::select()->where('id', $id)->one();
        return $user? true : false;
    }


    public static function isFollowing($from, $to){
        
        $data = UserRelation::select()
            ->where('user_from', $from)
            ->where('user_to', $to)
        ->one();

        if($data){
            return true;
        }

        return false;
    }

    public static function follow($from, $to){

        UserRelation::insert([
            'user_from' => $from,
            'user_to' => $to
        ])->execute();
    }

    public static function unfollow($from, $to){

        UserRelation::delete()
            ->where('user_from',$from)
            ->where('user_to',$to)
        ->execute();
    }

    public static function searchUser($term){

        $users = [];
        $data = User::select()->where('name', 'like', '%'.$term.'%')->get();

        if($data){

            foreach($data as $user){

                $newUser = new User();
                $newUser->id = $user['id'];
                $newUser->name = $user['name'];
                $newUser->userPicture = $user['userPicture'];

                $users[] = $newUser;
                        
            }

        }

        return $users;
    }

    public static function birthdateCheck($birthdate){
        
        $birthdate = explode('/',$birthdate);
        
        if(count($birthdate) != 3){

            return false;
            
        }

        $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
        
        if(strtotime($birthdate) === false){

            return false;

        }

        return $birthdate;
    }

 

    public static function updateData($userChanges,$userId){
        

        $update = User::update();

        foreach($userChanges as $key => $value){
            if(!empty(trim($value))){
               
               $update->set($key, $value);
            }
           
        }
       
        $update->where('id', $userId)->execute();

        
    }


    public static function securityValidation($userId, $password, $newPassword){

        $user = User::select()->where('id', $userId)->one();
        
        
        if($user){
           
            if(password_verify($password, $user['password'])){
                
                
                $securityData = [
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'token' => md5(time().rand(0,9999).time())
                ];
                
                
                return $securityData;
                
            }
        }

        return false;
    }

}