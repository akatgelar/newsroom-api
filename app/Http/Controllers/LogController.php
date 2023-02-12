<?php

namespace App\Http\Controllers;

use App\Log;
use App\MasterProvinsi;
use App\MasterKota;
use App\MasterKecamatan;
use App\MasterKelurahan;
use App\User;
use Illuminate\Http\Request;

class LogController extends Controller
{

    /**
     * @OA\Get(
     *     path="/log",
     *     tags={"Log"},
     *     summary="",
     *     description="Get all data",
     *     operationId="log_get_all",
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        $where = $request->has('where') ? $request->get('where') : '{}';
        $sort = $request->has('sort') ? $request->get('sort') : 'id:asc';
        $per_page = $request->has('per_page') ? $request->get('per_page') : 10;
        $page = $request->has('page') ? $request->get('page') : 1;
        $count = $request->has('count') ? $request->get('count') : false;
        $search = $request->has('search') ? $request->get('search') : '';

        $sort = explode(':', $sort);
        $where = str_replace("'", "\"", $where);
        $where = json_decode($where, true);

        if($count == true) {

            $data = [];
            $data['count'] = $this->getCount($where, $search);

            $result = [];
            $result['success'] = true;
            $result['message'] = 'Get Data Successfull';
            $result['data'] = $data;

            return response()->json($result, 200);
        }
        else {
            $query = Log::where([['id','>','0']]);

            if($where){
                foreach($where as $key => $value) {
                    $query = $query->where([[$key, '=', $value]]);
                }
            }

            if($search){
                $query = $query->Where([['object', 'like', '%' . $search . '%']]);
            }

            $query = $query
                ->orderBy($sort[0], $sort[1])
                ->limit($per_page)
                ->offset(($page-1) * $per_page)
                ->get()
                ->toArray();

            $queryFinal = [];
            foreach($query as $qry) {
                $temp = $qry;
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
        $query = Log::where([['id','>','0']]);

        if($where){
            foreach($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        if($search){
            $query = $query->Where([['object', 'like', '%' . $search . '%']]);
        }

        $query = $query->count('id');

        return $query;
    }

    /**
     * @OA\Get(
     *     path="/log/{id}",
     *     tags={"Log"},
     *     summary="",
     *     description="Get data by id",
     *     operationId="log_get_by_id",
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function getById($id)
    {
        $query = Log::find($id);
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
     *     path="/log",
     *     tags={"Log"},
     *     summary="",
     *     description="Insert data",
     *     operationId="log_insert",
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function Insert(Request $request)
    {
        $query = Log::create($request->all());
        $query = json_decode(json_encode($query), true);
        ksort($query);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Insert Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 201);
    }



    /**
     * @OA\Put(
     *     path="/log/{id}",
     *     tags={"Log"},
     *     summary="",
     *     description="Update data",
     *     operationId="log_update",
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function Update(Request $request, $id)
    {
        $query = Log::findOrFail($id);
        $query->update($request->all());
        $query = json_decode(json_encode($query), true);
        ksort($query);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Update Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }



    /**
     * @OA\Delete(
     *     path="/log/{id}",
     *     tags={"Log"},
     *     summary="",
     *     description="Delete data",
     *     operationId="log_delete",
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function Delete($id)
    {
        $query = Log::findOrFail($id);
        $query->delete();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Delete Data Successfull';
        $result['data'] = [];

        return response()->json($result, 200);
    }

    public function InsertLog($token, $action, $object, $object_id)
    {
        $user = User::where([['api_token','=',$token]])
        ->get()
        ->toArray();

        if($user){
            $data = [];
            $data['user_id'] = $user[0]['id'];
            $data['action'] = $action;
            $data['object'] = $object;
            $data['object_id'] = $object_id;
            $result = Log::create($data);
        }
    }

    public function StatByAction(Request $request)
    {
        $where = $request->has('where') ? $request->get('where') : '{}';

        $where = str_replace("'", "\"", $where);
        $where = json_decode($where, true);

        $query = Log::where([['id','>','0']]);

        if($where){
            foreach($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        $query = $query
            ->selectRaw('action, count(*) as total')
            ->groupBy('action')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }

    public function StatByObject(Request $request)
    {
        $where = $request->has('where') ? $request->get('where') : '{}';

        $where = str_replace("'", "\"", $where);
        $where = json_decode($where, true);

        $query = Log::where([['id','>','0']]);

        if($where){
            foreach($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        $query = $query
            ->selectRaw('object, count(*) as total')
            ->groupBy('object')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }

    public function StatByDate(Request $request)
    {
        $where = $request->has('where') ? $request->get('where') : '{}';

        $where = str_replace("'", "\"", $where);
        $where = json_decode($where, true);

        $query = Log::where([['logs.id','>','0']]);

        if($where){
            foreach($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        $query = $query
            ->selectRaw('date(created_at) as tanggal, count(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->limit(10)
            ->get()
            ->toArray();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }

    public function StatByUser(Request $request)
    {
        $where = $request->has('where') ? $request->get('where') : '{}';

        $where = str_replace("'", "\"", $where);
        $where = json_decode($where, true);

        $query = Log::where([['logs.id','>','0']]);

        if($where){
            foreach($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        $query = $query
            ->selectRaw('users.nama, count(*) as total')
            ->join('users', 'users.id', '=', 'logs.user_id')
            ->groupBy('users.nama')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $query;

        return response()->json($result, 200);
    }
}
