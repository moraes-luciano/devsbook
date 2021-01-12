<?php
namespace src\handlers;

use \src\models\Post;
use \src\models\User;
use \src\models\UserRelation;

class PostHandler {

    public static function addPost($userId, $postType, $body){
       
        $body = trim($body);
        
        if(!empty($userId && !empty($body))){
            Post::insert([
                'id_user' => $userId,
                'type' => $postType,
                'created_at'=>date('Y-m-d H:i:s'),
                'body' => $body
            ])->execute();
        }
    }

    public static function getHomeFeed($userId, $page){

        $perPage = 2; // numero de comentários por página
        // 1. Pegar a lista de usuários que eu sigo.
        
        $userList = UserRelation::select()->where('user_from', $userId)->get();
        $users = [];
        
        foreach($userList as $userItem){
            $users[] = $userItem['user_to'];
        }
        
        $users[] = $userId; //adicionar no feed as minhas próprias postagens.

        
        
        // 2. Pegar os posts dessas pessoas ordenados pela data.
        
        $postList = Post::select()
            ->where('id_user', 'in', $users)
            ->orderBy('created_at','desc')
            ->page($page, $perPage)
        ->get();
        
        $total = Post::select()
            ->where('id_user', 'in', $users)
        ->count();

        $pageCount = ceil($total/$perPage);


        // 3. Transformar o resultado em objetos dos models.

        $posts=[];

        foreach($postList as $postItem){
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;

            if($postItem['id_user'] === $userId){
                $newPost->mine = true;
            }


             // 4. Preencher as informações adicionais no post.

            $newUser = User::select()->where('id', $postItem['id_user'])->one();

            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->userPicture = $newUser['userPicture'];

            // TODO 4.1 Preencher as informações de Like
            
            $newPost->likeCount = 0;
            $newPost->liked = false;
            
            // TODO 4.2 Preencher as informações de comentários
            
            $newPost->comments = [];



            $posts[] = $newPost;
        }

        // 5. Retornar o resultado.

        return [
            'posts'=>$posts,
            'pageCount'=>$pageCount,
            'currentPage' =>$page
        ];
    }
}