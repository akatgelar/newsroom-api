<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\FileResize;
use Image;

use Illuminate\Http\Request;

class UploadController extends Controller
{


    /**
     * @OA\Post(
     *     path="/upload",
     *     tags={"Upload"},
     *     summary="",
     *     description="Upload file",
     *     operationId="upload",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="documentType",
     *                      description="documentType",
     *                      type="file"
     *                  ),
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
     *                  "message"="Upload Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function upload(Request $request){

        $now = \Carbon\Carbon::now()->format('Y-m-d_H:i:s');
        $md5 = md5($now);

        $originalPath = public_path().'/uploads/';
        $originalImage= $request->file('file');
        $thumbnailImage = Image::make($originalImage);
        $thumbnailImage->save($originalPath.$md5.'.'.$originalImage->getClientOriginalExtension());
        $thumbnailPath = public_path().'/uploads/thumb/';
        $thumbnailImage->resize(500, null, function ($constraint) {$constraint->aspectRatio();});
        $thumbnailImage->save($thumbnailPath.$md5.'.'.$originalImage->getClientOriginalExtension());

        $upload = [];
        $upload['originalName'] = $originalImage->getClientOriginalName();
        $upload['mimeType'] = $originalImage->getClientOriginalExtension();
        $upload['fileSize'] = $originalImage->getSize();
        $upload['fileName'] = $md5.'.'.$originalImage->getClientOriginalExtension();
        $upload['path'] = '/uploads/' . $md5.'.'.$originalImage->getClientOriginalExtension();
        $upload['pathThumbnail'] = '/uploads/thumb/' . $md5.'.'.$originalImage->getClientOriginalExtension();

        ksort($upload);

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Get Data Successfull';
        $result['data'] = $upload;

        return response()->json($result, 200);

        return back()->with('success', 'Your images has been successfully Upload');

    }
}
