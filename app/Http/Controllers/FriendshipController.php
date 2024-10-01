<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendshipController extends Controller
{
    
   public function getFriends($userId)
{
    $friends = DB::table('friendships')
        ->where(function ($query) use ($userId) {
            $query->where('userID1', $userId)
                ->orWhere('userID2', $userId);
        })
        ->where('status', 'accepted')
        ->get();

    $friendsWithData = [];
    
    foreach ($friends as $friendship) {
        if ($friendship->userID1 == $userId) {
            $friendData = DB::table('Users')->where('id', $friendship->userID2)->first();
        } else {
            $friendData = DB::table('Users')->where('id', $friendship->userID1)->first();
        }

        $friendsWithData[] = [
            'friendship_id' => $friendship->id,
            'friendship_dateCreated' => $friendship->created_at,
            'friend_data' => $friendData,
        ];
    }

    return response()->json($friendsWithData);
}
    
    public function getReviewsFromFriend($friendId)
    {
        $reviews = DB::table('Reviews')
        ->join('Users', 'Reviews.UserID', '=', 'Users.id')
        ->where('Reviews.UserID', '=', $friendId)
        ->select('Reviews.id', 'Reviews.MovieID', 'Reviews.UserID', 'Reviews.Rating', 'Reviews.dataCreated', 'Reviews.ReviewText', 'Users.userName', 'Users.Url_Image')
        ->get();

        return response()->json($reviews);
    }
    
    
 public function getCommentsForReview($reviewId)
    {
        $comments = DB::table('comentarios')
            ->join('Reviews', 'comentarios.reviewId', '=', 'Reviews.id')
            ->join('Users', 'comentarios.userId', '=', 'Users.id')
            ->where('comentarios.reviewId', $reviewId)
            ->get();

        return response()->json($comments);
    }
    
    public function addComment(Request $request)
{
    

    try {
        $comment = DB::table('comentarios')->insertGetId([
            'reviewId' => $request->input('reviewId'),
            'userId' => $request->input('userId'),
            'textComentario' => $request->input('textCommentary'),
        ]);

        return response()->json(['commentId' => $comment, 'status' => 'ok'], 201);
    } catch (\Throwable $th) {
        return response()->json(['message' => 'Erro ao adicionar comentário'], 500);
    }
}

    
   public function getPendingInvites($userId)
{
    $excludedUser = DB::table('Users')
        ->where('id', $userId)
        ->first();

    $invites = DB::table('friendships')
        ->where(function ($query) use ($userId) {
            $query->where('userID2', $userId);
        })
        ->where('status', 'pending')
        ->where(function ($query) use ($userId) {
            $query->where('userID1', '!=', $userId)
                  ->orWhere('userID2', '!=', $userId);
        })
        ->join('Users', function ($join) use ($userId) {
            $join->on('Users.id', '=', 'friendships.userID1')
                 ->where('Users.id', '!=', $userId);
            $join->orOn('Users.id', '=', 'friendships.userID2')
                 ->where('Users.id', '!=', $userId);
        })
        ->select('friendships.id as friendship_id', 'friendships.*', 'Users.*')
        ->get();

    return response()->json($invites);
}



    public function acceptInvite(Request $request)
    {
        $FriendshipId = $request->input('friendship');
        
        DB::table('friendships')
            ->where('id', $FriendshipId)
            ->update(['status' => 'accepted']);

        return response()->json(['message' => 'Convite aceito']);
    }

    public function declineInvite(Request $request)
    {
        $FriendshipId = $request->input('friendship');
        
        DB::table('friendships')
            ->where('id', $FriendshipId)
            ->delete();

        return response()->json(['message' => 'Convite recusado']);
    }
    
    
    public function sendEnvite(Request $request) {
    $user1Id = $request->input('userID1');
    $userName = $request->input('userName');

    // Buscar o ID do usuário a partir do userName
    $user2Id = DB::table('Users')
        ->where('UserName', $userName)
        ->value('id');

    if (!$user2Id) {
        return response()->json(['error' => 'Usuário não encontrado'], 404);
    }

    // Verifica se a amizade já existe nos dois sentidos
    $friendshipExists = DB::table('friendships')
        ->where(function ($query) use ($user1Id, $user2Id) {
            $query->where('userID1', $user1Id)
                ->where('userID2', $user2Id);
        })
        ->orWhere(function ($query) use ($user1Id, $user2Id) {
            $query->where('userID1', $user2Id)
                ->where('userID2', $user1Id);
        })
        ->exists();

    if ($friendshipExists) {
        return response()->json(['error' => 'Esta amizade já existe.'], 422);
    }

    try {
        DB::table('friendships')->insert([
            'userID1' => $user1Id,
            'userID2' => $user2Id,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Amizade pendente criada com sucesso']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => 'Erro ao criar amizade pendente'], 500);
    }
}

   public function searchUser($searchUser)
    {
        try {
            $search = $searchUser;

            $users = $search
                ? DB::table('Users')->where('UserName', 'LIKE', "%$search%")->get()
                : DB::table('Users')->get();

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar usuários.'], 500);
        }
    }
    
    public function checkFriendshipStatus($userId1, $userId2)
{
    try {
        $friendship = DB::table('friendships')
            ->where(function ($query) use ($userId1, $userId2) {
                $query->where('userID1', $userId1)
                    ->where('userID2', $userId2);
            })
            ->orWhere(function ($query) use ($userId1, $userId2) {
                $query->where('userID1', $userId2)
                    ->where('userID2', $userId1);
            })
            ->first();

        $status = $friendship ? $friendship->status : null;

        return response()->json(['status' => $status]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao verificar status da amizade.'], 500);
    }
}

    
}
    