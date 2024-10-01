<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;

class InsertDataController extends Controller
{

    
public function deleteMovie(Request $request)
{
    $id = $request->input('id');
    
    try {
        // Deleta os comentários associados à revisão
        DB::table('comentarios')->where('reviewId', $id)->delete();
        
        // Deleta a revisão
        $deleted = DB::table('Reviews')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json(['message' => 'Revisão e comentários excluídos com sucesso'], 200);
        } else {
            return response()->json(['error' => 'Revisão não encontrada'], 404);
        }
    } catch (\Exception $e) {
        \Log::error('Erro ao excluir revisão e comentários: ' . $e->getMessage());
        return response()->json(['error' => 'Erro ao excluir revisão e comentários'], 500);
    }


    }
        
    public function UpdateMovie(Request $request)
{
    try {
        $data = $request->all();

        // Verifique se o ID foi fornecido no corpo da solicitação
        if (!isset($data['id'])) {
            return response()->json(['error' => 'ID do cliente não fornecido'], 400);
        }

        $id = $data['id'];

        // Verifique se os campos 'observacao' e 'rate' foram fornecidos no corpo da solicitação
        if (!isset($data['observacao']) || !isset($data['rate'])) {
            return response()->json(['error' => 'Campos obrigatórios não fornecidos'], 400);
        }

        $observacao = $data['observacao'];
        $rate = $data['rate'];

        // Atualize os campos 'observacao' e 'rate' do registro com o ID fornecido na tabela 'rateMovies'
        $updated = DB::table('Reviews')->where('id', $id)->update([
            'ReviewText' => $observacao,
            'Rating' => $rate
        ]);

        if (!$updated) {
            return response()->json(['error' => 'Avaliação não encontrada'], 404);
        }

        return response()->json(['message' => 'Avaliação atualizada com sucesso']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao atualizar avaliação'], 500);
    }
}

   public function checkDeviceId(Request $request)
{
    $userId = $request->input('userID');
    $deviceId = $request->input('userId');

    // Verifica se o usuário existe
    $user = DB::table('Users')->where('id', $userId)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Verifica se o deviceId do usuário é nulo
    $deviceIdIsNull = is_null($user->deviceId);

    // Se o deviceId for nulo, atualize-o com o valor fornecido
    if ($deviceIdIsNull) {
        DB::table('Users')->where('id', $userId)->update(['deviceId' => $deviceId]);
    }

    return response()->json(['deviceIdIsNull' => $deviceIdIsNull]);
}

    
    public function checkTokenExists(Request $request)
    {
        $token = $request->input('token'); // Obtém o token enviado na requisição

        // Verifica se o token existe na tabela "tokens"
        $tokenExists = DB::table('tokens')->where('token', $token)->exists();

        return response()->json(['token_exists' => $tokenExists]);
    }

  

}
