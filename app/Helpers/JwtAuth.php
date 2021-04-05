<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class JwtAuth{

    public $key;

    public function __construct()
    {   
        $this->key = 'Esto-es_una-prueba_2020';
    }

    public function singUp($email,$password, $getToken=NULL){

        //Buscar si existe usuario con su credencial(email-contraseña)
        $user = User::where([
            'email'=>$email,
            'password'=>$password
        ])->first();

        //Comprobar si son correctos(objeto)
        $singUp = false;
        if (is_object($user)) {
            $singUp = true;
        }
        //Generar token con los datos del usuario identificado
        if ($singUp) {
            $token = array(
                'sub'=> $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() +(7*24*60*60)
            );
            
            $jwt = JWT::encode($token,$this->key, 'HS256');
            $decoded = JWT::decode($jwt,$this->key,['HS256']);

             //devolver los datos decodificados o el token, en funcion de un parametro
             if (is_null($getToken)) {
                 $data = $jwt;
             }else {
                 $data = $decoded;
             }

        }else{
            $data = array(
                'status'=>'error',
                'message'=>'Login incorrecto'
            );
        }

        return $data;    
    }

    public function checkToken($jwt,$getIdentity=false){
        $auth = false;
        try{
            $jwt = str_replace('"','', $jwt);
            $decoded = JWT::decode($jwt,$this->key,['HS256']);
        }catch(\UnexpectedValueException $e)
        {
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        if (!empty($decoded)&& is_object($decoded)&& isset($decoded->sub)) {
            $auth = true;
        }else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }

}

?>