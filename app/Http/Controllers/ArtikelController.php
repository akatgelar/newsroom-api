<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Artikel;

class ArtikelController extends Controller
{

    /**
     * @OA\Get(
     *     path="/artikel",
     *     tags={"Artikel"},
     *     summary="",
     *     description="Get all data",
     *     operationId="artikel_get_all",
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
            $query = Artikel::where([['id','>','0']]);

            if($where){
                foreach($where as $key => $value) {
                    $query = $query->where([[$key, '=', $value]]);
                }
            }

            if($search){
                $query = $query->Where([['title', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['category', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['tags', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['desc_short', 'like', '%' . $search . '%']]);
                $query = $query->orWhere([['desc_long', 'like', '%' . $search . '%']]);
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

                unset($temp['desc_long']);
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
        $query = Artikel::where([['id','>','0']]);

        if($where){
            foreach($where as $key => $value) {
                $query = $query->where([[$key, '=', $value]]);
            }
        }

        if($search){
            $query = $query->Where([['title', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['category', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['tags', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['desc_short', 'like', '%' . $search . '%']]);
            $query = $query->orWhere([['desc_long', 'like', '%' . $search . '%']]);
        }

        $query = $query->count('id');

        return $query;
    }

    /**
     * @OA\Get(
     *     path="/artikel/{id}",
     *     tags={"Artikel"},
     *     summary="",
     *     description="Get data by id",
     *     operationId="artikel_get_by_id",
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
        $query = Artikel::find($id);
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
     *     path="/artikel",
     *     tags={"Artikel"},
     *     summary="",
     *     description="Insert data",
     *     operationId="artikel_insert",
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
        $data = $request->all();

        // insert slug
        $data['slug'] = str_replace(' ', '-', strtolower($data['title']));
        $count_slug = Artikel::where([['title', '=', $data['title']]])->count('id');
        if($count_slug > 0) {
            $count_slug += 1;
            $data['slug'] .= '-'. (string)$count_slug;
        }

        $query = Artikel::create($data);
        $query = json_decode(json_encode($query), true);
        ksort($query);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Insert Data Successfull';
        $result['data'] = $query;

        // insert log
        $header = $request->header('Authorization');
        $token = substr($header, 7);
        app()->call('App\Http\Controllers\LogController@InsertLog', [$token, 'INSERT', 'Artikel', $result['data']['id']]);

        return response()->json($result, 201);
    }

    /**
     * @OA\Put(
     *     path="/artikel/{id}",
     *     tags={"Artikel"},
     *     summary="",
     *     description="Update data",
     *     operationId="artikel_update",
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

        $query = Artikel::findOrFail($id);
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
        app()->call('App\Http\Controllers\LogController@InsertLog', [$token, 'UPDATE', 'Artikel', $result['data']['id']]);

        return response()->json($result, 200);
    }

    /**
     * @OA\Delete(
     *     path="/artikel/{id}",
     *     tags={"Artikel"},
     *     summary="",
     *     description="Delete data",
     *     operationId="artikel_delete",
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
        $query = Artikel::findOrFail($id);
        $query->delete();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Delete Data Successfull';
        $result['data'] = [];

        // insert log
        $header = $request->header('Authorization');
        $token = substr($header, 7);
        app()->call('App\Http\Controllers\LogController@InsertLog', [$token, 'DELETE', 'Artikel', $id]);

        return response()->json($result, 200);
    }

    public function getCategory(Request $request) {
        $search = $request->has('search') ? $request->get('search') : '';

        $query = Artikel::distinct();
        $query = $query->where([['category', '!=', 'null']]);

        if($search){
            $query = $query->Where([['category', 'like', '%' . $search . '%']]);
        }

        $query = $query
            ->orderBy('category', 'asc')
            ->get(['category'])
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

        return response()->json($result, 200);
    }
}
