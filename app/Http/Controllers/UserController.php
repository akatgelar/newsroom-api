<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserLevel;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/user",
     *     tags={"User"},
     *     summary="",
     *     description="Get all data",
     *     operationId="user_get_all",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="per_page",
     *          description="Per_page value is number. ex : ?per_page=10",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          description="Page value is number. ex : ?page=2",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="sort",
     *          description="Sort value is string with rule column-name:order. ex : ?sort=id:asc",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="where",
     *          description="Where value is object. ex : ?where={'name':'john', 'dob':'1990-12-31'}",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="where",
     *          description="Where value is object. ex : ?where={'name':'john', 'dob':'1990-12-31'}",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="count",
     *          description="Count value is boolean. ex : ?count=true",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Get Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function getAll(Request $request)
    {
        $where = $request->has('where') ? $request->get('where') : '{}';
        $sort = $request->has('sort') ? $request->get('sort') : 'users.id:asc';
        $per_page = $request->has('per_page') ? $request->get('per_page') : 10;
        $page = $request->has('page') ? $request->get('page') : 1;
        $count = $request->has('count') ? $request->get('count') : false;
        $search = $request->has('search') ? $request->get('search') : '';

        $sort = explode(':', $sort);
        $where = str_replace("'", "\"", $where);
        $where = json_decode($where, true);

        if ($count == true) {

            $data = [];
            $data['count'] = $this->getCount($where, $search);

            $result = [];
            $result['success'] = true;
            $result['message'] = 'Get Data Successfull';
            $result['data'] = $data;

            return response()->json($result, 200);
        }
        else {
            $query = User::where([['users.id', '>', '0']]);

            if ($where) {
                foreach ($where as $key => $value) {
                    $query = $query->where([[$key, '=', $value]]);
                }
            }

            if ($search) {
                $query = $query->Where([['email', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['phone', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['username', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['nama', 'like', '%' . $search . '%']]);
            }

            $query = $query
                ->orderBy($sort[0], $sort[1])
                ->limit($per_page)
                ->offset(($page-1) * $per_page)
                ->get(['users.*'])
                ->toArray();

            $queryFinal = [];
            foreach ($query as $qry) {
                $temp = $qry;

                $user_level = UserLevel::select('name')->where('id', '=', $qry['user_level_id'])->get();
                if (count($user_level) > 0) {
                    $temp['user_level_name'] = $user_level[0]['name'];
                } else {
                    $temp['user_level_name'] = null;
                }

                unset($temp['api_token']);
                ksort($temp);

                array_push($queryFinal, $temp);
            };

            $result = [];
            $result['success'] = true;
            $result['message'] = 'Get Data Successfull';
            $result['data'] = $queryFinal;
            $result['pagination'] = [
                'page' => $page,
                'per_page' => $per_page,
                'total_data' => $this->getCount($where, $search),
                'total_page' => (fmod(($this->getCount($where, $search) / $per_page), 1) ? (int)($this->getCount($where, $search) / $per_page) + 1 : ($this->getCount($where, $search) / $per_page))
            ];

            return response()->json($result, 200);
        }
    }

    function getCount($where, $search)
    {
        $query = User::where([['users.id', '>', '0']]);

        if ($where) {
            foreach ($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        if ($search) {
            $query = $query->Where([['email', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['phone', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['username', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['nama', 'like', '%' . $search . '%']]);
        }

        $query = $query->count('users.id');

        return $query;
    }

    /**
     * @OA\Get(
     *     path="/user/{id}",
     *     tags={"User"},
     *     summary="",
     *     description="Get data by id",
     *     operationId="user_get_by_id",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="id",
     *          description="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Get Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function getById($id)
    {
        $query = User::find($id);

        $user_level = UserLevel::select('name')->where('id', '=', $query['user_level_id'])->get();
        if (count($user_level) > 0) {
            $query['user_level_name'] = $user_level[0]['name'];
        } else {
            $query['user_level_name'] = null;
        }

        unset($query['api_token']);
        $query = json_decode(json_encode($query), true);
        ksort($query);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }

    /**
     * @OA\Post(
     *     path="/user",
     *     tags={"User"},
     *     summary="",
     *     description="Insert data",
     *     operationId="user_insert",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Insert Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function insert(Request $request)
    {
        $userAvailable = $this->checkAccount($request);

        if ($userAvailable == true) {
            $data = $request->all();
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
                // $data['password_plain'] = $data['password'];
            }
            if (isset($data['pin'])) {
                $data['pin'] = bcrypt($data['pin']);
            }

            $query = User::create($data);
            $query = json_decode(json_encode($query), true);
            ksort($query);

            $result = [];
            $result['success'] = true;
            $result['message'] = 'Insert Data Successfull';
            $result['data'] = $query;

            // insert log
            $header = $request->header('Authorization');
            $token = substr($header, 7);
            app()->call('App\Http\Controllers\LogController@InsertLog', [$token, 'INSERT', 'User', $result['data']['id']]);

            return response()->json($result, 201);
        } else {
            $result['success'] = false;
            $result['message'] = 'Username / Email / Phone already exist in PR Newsroom Account';
            $result['data'] = [];

            return response()->json($result, 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/user/{id}",
     *     tags={"User"},
     *     summary="",
     *     description="Update data",
     *     operationId="user_update",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="id",
     *          description="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Update Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $data = $request->all();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        if (isset($data['pin'])) {
            $data['pin'] = bcrypt($data['pin']);
        }

        $query = User::findOrFail($id);
        $query->update($data);
        $query = json_decode(json_encode($query), true);
        ksort($query);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Update Data Successfull';
        $result['data'] = $query;

        // insert log
        $header = $request->header('Authorization');
        $token = substr($header, 7);
        app()->call('App\Http\Controllers\LogController@InsertLog', [$token, 'UPDATE', 'User', $result['data']['id']]);

        return response()->json($result, 200);
    }

    /**
     * @OA\Delete(
     *     path="/user/{id}",
     *     tags={"User"},
     *     summary="",
     *     description="Delete data",
     *     operationId="user_delete",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="id",
     *          description="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="number"
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Delete Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function delete($id, Request $request)
    {
        $query = User::findOrFail($id);
        $query->delete();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Delete Data Successfull';
        $result['data'] = [];

        // insert log
        $header = $request->header('Authorization');
        $token = substr($header, 7);
        app()->call('App\Http\Controllers\LogController@InsertLog', [$token, 'DELETE', 'User', $id]);

        return response()->json($result, 200);
    }

    /**
     * @OA\Profile(
     *     path="/profile",
     *     tags={"User"},
     *     summary="",
     *     description="Profile",
     *     operationId="user_profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Get Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function profile()
    {
        $userId = Auth::user()->id;

        $query = User::find($userId);

        $user_level = UserLevel::select('name')->where('id', '=', $query['user_level_id'])->get();
        if (count($user_level) > 0) {
            $query['user_level_name'] = $user_level[0]['name'];
        } else {
            $query['user_level_name'] = null;
        }

        unset($query['api_token']);
        $query = json_decode(json_encode($query), true);
        ksort($query);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }

    public function checkAccount($request)
    {
        try {
            $query1 = User::where([['email', '=', $request['email']]]);
            $count1 = $query1->count('id');
            if ($count1 >= 1) {
                return false;
            } else {
                $query2 = User::Where([['username', '=', $request['username']]]);
                $count2 = $query2->count('id');
                if ($count2 >= 1) {
                    return false;
                } else {
                    return true;
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
