<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hash;
use App\Models\User;
class AuthController extends Controller
{
      /**
     * @OA\Post(
     ** path="/api/register",
     *   tags={"Register"},
     *   summary="Register",
     *   operationId="register",
     *
     *  @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *       name="mobile_number",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      @OA\Parameter(
     *      name="password_confirmation",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
      public function register(Request $request)
      {
          $validated = $request->validate([
              'name' => 'required',
              'email' => 'required|email|unique:users',
              'password' => 'required|confirmed',
              'mobile_number' => 'required',
          ]);
          $data = $request->all();
          $data['password'] = Hash::make($data['password']);
          $user = User::create($data);
          $success['token'] =  $user->createToken('authToken')->accessToken;
          $success['name'] =  $user->name;
          return response()->json(['success' => $success]);
      }
      /**
        * @OA\Post(
        * path="/api/login",
        * operationId="authLogin",
        * tags={"Login"},
        * summary="User Login",
        * description="Login User Here",
        *     @OA\RequestBody(
        *         @OA\JsonContent(),
        *         @OA\MediaType(
        *            mediaType="multipart/form-data",
        *            @OA\Schema(
        *               type="object",
        *               required={"email", "password"},
        *               @OA\Property(property="email", type="email"),
        *               @OA\Property(property="password", type="password")
        *            ),
        *        ),
        *    ),
        *      @OA\Response(
        *          response=201,
        *          description="Login Successfully",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=200,
        *          description="Login Successfully",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=422,
        *          description="Unprocessable Entity",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(response=400, description="Bad request"),
        *      @OA\Response(response=404, description="Resource Not Found"),
        * )
        */
      public function login(Request $request)
      {
          $validator = $request->validate([
              'email' => 'email|required',
              'password' => 'required'
          ]);
          if (!auth()->attempt($validator)) {
              return response()->json(['error' => 'Unauthorised'], 401);
          } else {
              $success['token'] = auth()->user()->createToken('authToken')->accessToken;
              $success['user'] = auth()->user();
              return response()->json(['success' => $success])->setStatusCode(200);
          }
      }

    /**
        * @OA\Put(
        * path="/api/update",
        * operationId="authupdate",
        * tags={"Update"},
        * summary="User Update",
        * description="Update user details",
        *     
        *       @OA\Parameter(
        *      name="name",
        *      in="query",
        *      required=true,
        *      @OA\Schema(
        *           type="string"
        *      )
        *   ),
        *  @OA\Parameter(
        *      name="email",
        *      in="query",
        *      required=true,
        *      @OA\Schema(
        *           type="string"
        *      )
        *   ),
        *   @OA\Parameter(
        *       name="mobile_number",
        *      in="query",
        *      required=true,
        *      @OA\Schema(
        *           type="integer"
        *      )
        *   ),
        *   @OA\Parameter(
        *      name="password",
        *      in="query",
        *      required=true,
        *      @OA\Schema(
        *           type="string"
        *      )
        *   ),
        *      @OA\Response(
        *          response=201,
        *          description="Update Successful",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=200,
        *          description="Update Successful",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=422,
        *          description="Unprocessable Entity",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(response=400, description="Bad request"),
        *      @OA\Response(response=404, description="Resource Not Found"),
        * )
        */
        public function update(Request $request)
        {
          $request->validate([
              'name' => 'required',
              'email' => 'required|email',
              'password' => 'required',
              'mobile_number' => 'required',
          ]);
          $email=$request->email;
          $id = User::select('id')
              ->where('email','=',$email)
              ->get();
          $user=User::find($id);
          if($user){
            $user->name=$request->name;
            $user->email=$request->email;
            $user->password=Hash::make($request->password);
            $user->mobile_number=$request->mobile_number;
            $user->update();
  
            return response()->json(['message'=>'User record updated successfully'],200);
          }else{
              return response()->json(['message'=>'User not found'],404);
          }
        }
        /**
        * @OA\PUT(
        * path="/api/delete",
        * operationId="Delete",
        * tags={"Delete"},
        * summary="User Delete",
        * description="Delete user ",
        *     
        
        *  @OA\Parameter(
        *      name="email",
        *      in="query",
        *      required=true,
        *      @OA\Schema(
        *           type="string"
        *      )
        *   ),
       
        *      @OA\Response(
        *          response=201,
        *          description="Delete4 Successful",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=200,
        *          description="Delete Successful",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=422,
        *          description="Unprocessable Entity",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(response=400, description="Bad request"),
        *      @OA\Response(response=404, description="Resource Not Found"),
        * )
        */

  public function destroy($request){
    $request->validate([
        'email' => 'required|email',
    ]);
    $email=$request->email;
    $user=User::find($email);
    if($user){
        $user->delete();
        return response()->json(['message'=>'User record deleted successfully'],200);
  }else{
    return response()->json(['message'=>'User not found'],404);
}
}
}
