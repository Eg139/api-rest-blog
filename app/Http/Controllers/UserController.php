<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
//use Facade\FlareClient\Http\Response;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\App;

class UserController extends Controller
{
    public function pruebas(Request $request)
    {
        return "Accion de pruebas de User Controller";
    }

    public function register(Request $request)
    {

        //Recoger los datos del usuario por post
        $json = $request->input('json', null);
        //Decodificar el JSon
        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); //array


        if (!empty($params) && !empty($params_array)) {
            //Limpiar datos
            $params_array = array_map('trim', $params_array);

            //Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                //Validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se a creado',
                    'errors' => $validate->errors()
                );
            } else {
                //Validacion pasada correctamente

                //Cifrar la contraseña
                $pwd= hash('sha256',$params->password);

                //Crear el usuario
                $user = new User();
                $user->name=$params_array['name'];
                $user->surname=$params_array['surname'];
                $user->email=$params_array['email'];
                $user->password=$pwd;
                $user->role = 'role_user';
                
                //Guardar el usuario
                $user->save();


                $data = array(
                    'status' => 'succes',
                    'code' => 200,
                    'message' => 'El usuario se a creado correctamente',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos no son correctos',
            );
        }



        //devolver los datos en JSON
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \App\Helpers\JwtAuth;

        //Recibir datos por POST
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        //Validar los datos recibidos
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            //Validacion ha fallado
            $singup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se a podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            
        //Cifrar contraseña
        $pwd= hash('sha256',$params->password);

        //Devolver token o datos
            $singup = $jwtAuth->singUp($params->email, $pwd);
            if(!empty($params->gettoken)){
                $singup = $jwtAuth->singUp($params->email, $pwd, true);
            }
        }

        return response()->json($singup,200);
    }

    public function update(Request $request){

        //COMPROBAR SI EL USUARIO ESTA IDENTIFICADO
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth;
        $checkToken = $jwtAuth->checkToken($token);

        //RECOGER LOS DATOS POR POST
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        if ($checkToken && !empty($params_array)) {
            //ACTUALIZAR EL USUARIO

            //SACAR USUARIO IDENTIFICADO
            $user = $jwtAuth->checkToken($token, true);

            //VALIDAR LOS DATOS
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users'.$user->sub
            ]);

            //QUITAR LOS CAMPOS QUE NO QUIERO ACTUALIZAR
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //ACTUALIZAR EL USUARIO EN BBDD
            $user_update = User::where('id', $user->sub)->update($params_array);
            
            //DEVOLVER ARRAY CON RESULTADO
            $data = array(
                'code'=> 200,
                'status'=>'success',
                'message'=> $user,
                'changes'=>$params_array
            );

        }else {
            $data = array(
                'code'=> 400,
                'status'=>'error',
                'message'=>'El usuario no esta identificado.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){
        //recoger datos de la peticion
        $image = $request->file('file0');

        /*Validacion de la imagen*/
        
        $validate = \Validator::make($request->all(), [
            'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //guardar imagen
        if (!$image || $validate->fails()) {
            
            $data = array(
                'code'=> 400,
                'status'=>'error',
                'message'=>'Error al subir imagen'
            );
        }else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        


        

        return response()->json($data,$data['code']);
    }

    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else {
            $data = array(
                'code'=> 404,
                'status'=>'error',
                'message'=>'La imagen no existe'
            );
            return response()->json($data,$data['code']);
        }
        

    }

    public function detail($id){

        $user = User::find($id);
        
        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        }else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
        }

        return response()->json($data,$data['code']);
    }

}
