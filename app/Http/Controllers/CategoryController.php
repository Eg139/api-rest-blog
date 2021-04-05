<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {

            $data = array(
                'code' => 200,
                'status' => 'succes',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'categoria no encontrada'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {

        //RECOGER LOS DATOS POR POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //VALIDAR LOS DATOS
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            //GUARDAR LA CATEGORY
            if ($validate->fails()) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha guardado la categoria'
                );
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = array(
                    'code' => 400,
                    'status' => 'succes',
                    'category' => $category
                );
            }
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'no se ha enviado ninguna categoria'
            );
        }


        //DEVOLVER LOS RESULTADOS
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        //recoger los datos que llegan por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //Validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            //Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            //Actualizar el registro(categoria)
            $category = Category::where('id', $id)->update($params_array);

            $data = array(
                'code' => 200,
                'status' => 'succes',
                'category' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'no se ha enviado ninguna categoria'
            );
        }



        //Devolver respuesta
        return response()->json($data, $data['code']);
    }
}
