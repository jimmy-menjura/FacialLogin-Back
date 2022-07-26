<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use JWTAuth;
use Tymon\JwtAuth\Exceptions\JwtExceptioon;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\UrlGenerator;
class AuthController extends Controller
{
    //
    public $loginAfterSignUp = true;
    protected $user;
    protected $base_url;
    public function __construct(UrlGenerator $url){
        $this->user = new user;
        $this->base_url = $url->to("/");
    }

    public function RegisterUser(Request $request){
        $validator  = Validator::make($request->all(),['name' => 'required|string', 'email' => 'required|email', 'password' => 'required|string|min:6','image' => 'required']);

    if($validator->fails()){
        return response()-json([
            "success"=>false,
            "message"=>$validator->messages()->toArray(),

        ],400);
    }
    $check_email = $this->user->where('email',$request->email)->count();
    if($check_email!=0){
        return response()->json([
            "success"=>false,
            "message"=>"perdon el correo ya está en uso, OH NOOO!!!",
        ],400);
    }
    $file_name = "";
    $base64encodedString = $request->image;
    $generated_name = uniqid()."_".time().date("Ymd")."_IMG";
    $fileBin = file_get_contents( $base64encodedString );
    $mimeType = mime_content_type ($base64encodedString );
    if('image/png' == $mimeType){
        $file_name = $generated_name.".png";
    }
    else if("image/jpg" == $mimeType){
        $file_name = $generated_name.".jpg";
    }
    else if ("image/jpeg" == $mimeType){
        $file_name = $generated_name.".jpeg";
    }
    else {
        return response()->json([
            "success"=>false,
            "message"=>"Inválido el tipo de archivo para png,jpg y jpeg",
        ],400);
    }
    $this->user->name= $request->name;
    $this->user->email= $request->email;
    $this->user->password = Hash::make($request->password);
    $this->user->image = $file_name;
    $this->user->save();
    file_put_contents("./profile_images/".$file_name,$fileBin);
    return response()->json([
        "success"=>true,
        "message"=>"Registro con éxito",
    ],200);
}
public function login(Request $request){
    $validator  = Validator::make($request->only("email","password"),[
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    if($validator->fails()){
        return response()->json([
            "success"=>false,
            "message"=>$validator->messages()->toArray(),
        ],400);
    }
$input = $request->only("email","password");
    $jwt_token =  null;
    if(!$jwt_token=auth("users")->attempt($input)){
        return response()->json([
            "success"=>false,
            "message"=>"correo o contraseña invalidos",
        ],401);
    }
    $user_image = "";
    $user = auth("users")->authenticate($jwt_token);
    $user_image = $user->image;
    return response()->json([
        "success"=>true,
        "image"=>$this->base_url."/"."profile_images"."/".$user_image,
        "token"=>$jwt_token,
    ],200);
}
}
