<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VagaController extends Controller
{
    
    
    public function getMyRates($userId)
    {
        $reviews = DB::table('Reviews')
            ->where('UserID', $userId)
            ->get();
    
        return response()->json($reviews);
    }
    
    
public function getAllRatesOfFriends($userId)
{
    // Buscar as amizades onde o usuário é UserID1
    $userFriends1 = DB::table('friendships')
        ->where('UserID1', $userId)
        ->where('status', 'accepted')
        ->pluck('UserID2')
        ->toArray();

    // Buscar as amizades onde o usuário é UserID2
    $userFriends2 = DB::table('friendships')
        ->where('UserID2', $userId)
        ->where('status', 'accepted')
        ->pluck('UserID1')
        ->toArray();

    // Combinar os IDs de amigos de ambas as buscas
    $allFriends = array_unique(array_merge($userFriends1, $userFriends2));

    // Buscar as avaliações dos filmes feitas pelos amigos do usuário, excluindo as do próprio usuário
    $friendRates = DB::table('Reviews')
        ->join('Users', 'Reviews.UserID', '=', 'Users.id')
        ->whereIn('Reviews.UserID', $allFriends)
        ->where('Reviews.UserID', '!=', $userId)
        ->select('Reviews.id', 'Reviews.MovieID', 'Reviews.UserID', 'Reviews.Rating', 'Reviews.dataCreated', 'Reviews.ReviewText', 'Users.userName', 'Users.Url_Image')
        ->get();

    return response()->json($friendRates);
}

    
   public function updateProfile(Request $request)
{
    $newUsername = $request->input('newUsername');
    $newBio = $request->input('newBio');
    $userId = $request->input('userId');
    $image = $request->input('image');

    // Verificar se o novo nome de usuário já existe para outro usuário
    $existingUser = DB::table('Users')
        ->where('UserName', $newUsername)
        ->where('id', '!=', $userId) // Excluir o usuário atual da verificação
        ->first();

    if ($existingUser) {
        return response()->json(['message' => 'O nome de usuário já está em uso por outro usuário'], 400);
    }

    // Atualizar o perfil se o nome de usuário não estiver em uso por outro usuário
    DB::table('Users')
        ->where('id', $userId)
        ->update([
            'UserName' => $newUsername,
            'Bio' => $newBio,
            'Url_Image' => $image
        ]);
        
    $user = DB::table('Users')
        ->select('ID', 'UserName', 'Email', 'Bio', 'url_image', 'Password')
        ->where('id', $userId)
        ->first();

    return response()->json(['user' => $user, 'status' => 'ok']);
}
    
    public function updatePassword(Request $request)
    {
        $oldPassword = $request->input('oldPassword');
        $newPassword = $request->input('newPassword');
        $userId = $request->input('userId');

        $user = DB::table('Users')->where('id', $userId)->first();

        if ($user && Hash::check($oldPassword, $user->Password)) {
            DB::table('Users')
                ->where('id', $userId)
                ->update(['Password' => Hash::make($newPassword)]);

            return response()->json(['status' => 'ok']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Senha antiga incorreta'], 401);
        }
    }
}