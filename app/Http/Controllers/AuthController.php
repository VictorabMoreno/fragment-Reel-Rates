<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:Users,Username',
            'email' => 'required|email|unique:Users,Email',
            'password' => 'required|min:6',
        ]);

        try {
            DB::table('Users')->insert([
                'UserName' => $validated['username'],
                'Email' => $validated['email'],
                'Password' => Hash::make($validated['password']),
                // Outros campos do usuário, se houver...
            ]);

            return response()->json(['message' => 'Usuário registrado com sucesso'], 201);
        } catch (\Throwable $th) {
            // Em caso de erro ao inserir no banco de dados
            return response()->json(['message' => 'Erro ao registrar o usuário'], 500);
        }
    }
    
  public function checkEmailExists(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $emailExists = DB::table('Users')->where('Email', $request->email)->exists();

    return response()->json(['exists' => $emailExists]);
}

   public function checkUsernameExists(Request $request)
    {
        $request->validate([
            'username' => 'required',
        ]);
    
        $usernameExists = DB::table('Users')->where('UserName', $request->username)->exists();
    
        return response()->json(['exists' => $usernameExists]);
    }
    
      public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = DB::table('Users')
        ->select('ID', 'UserName', 'Email', 'Bio', 'url_image', 'Password', 'deviceId') // Seleciona os campos desejados
        ->where('Email', $request->email)
        ->first();

    if ($user && Hash::check($request->password, $user->Password)) {
        unset($user->Password); // Remove a senha do objeto do usuário
        // Login bem-sucedido, retorna o usuário
        return response()->json(['user' => $user, 'message' => 'Login bem-sucedido'], 200);
    }

    return response()->json(['message' => 'Credenciais inválidas'], 401);
}

public function deleteUserData(Request $request)
{
    $email = $request->input('email');

    // Busca o ID do usuário pelo email na tabela Users
    $userId = DB::table('Users')->where('email', $email)->value('id');

    if ($userId) {
        // Remove todas as entradas do usuário na tabela comentarios
        DB::table('comentarios')->where('userId', $userId)->delete();

        // Remove todas as entradas do usuário na tabela Reviews
        DB::table('Reviews')->where('UserID', $userId)->delete();

        // Remove entradas na tabela friendship onde UserID1 ou UserID2 correspondem ao ID do usuário
        DB::table('friendships')->where('UserID1', $userId)
            ->orWhere('UserID2', $userId)
            ->delete();

        // Remove o usuário da tabela Users
        DB::table('Users')->where('id', $userId)->delete();

        return response()->json(['message' => 'Dados do usuário deletados com sucesso']);
    } else {
        return response()->json(['error' => 'Email não encontrado']);
    }
}




}