<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index(){
        $blogs = Blog::orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => true,
            'data' => $blogs
        ]);
    }


    public function show($id){
        $blog = Blog::find($id);

        if ($blog == nuLL) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found',
            ]);
        }

        $blog['date'] = \Carbon\Carbon::parse($blog->created_at)->format('d M, Y');

        return response()->json([
            'status' => true,
            'data' => $blog,
        ]);
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'please fix the error',
                'errors' => $validator->errors()
            ]);
        }

        $blog = new Blog();
        $blog->title = $request->title;
        $blog->author = $request->author;
        $blog->description = $request->description;
        $blog->shortDesc = $request->shortDesc;
        $blog->save();

        //save image here
        $tempImage = TempImage::find($request->image_id);

        if($tempImage != null){
            $imageExtArray = explode('.',$tempImage->name);
            $ext = last($imageExtArray);
            $imageName = time().'-'.$blog->id.'.'.$ext;

            $blog -> image = $imageName;
            $blog->save();

            $sourcePath = public_path('uploads/temp/' .$tempImage->name);
            $destPath = public_path('uploads/blogs/' .$imageName);
            File::copy($sourcePath, $destPath);
        }

        return response()->json([
            'status' => true,
            'message' => 'blog added successfully',
            'data' => $blog
        ]);
    }


    public function update($id, Request $request){

        $blog = Blog::find($id);

        if ($blog == nuLL) {
            return response()->json([
                'status' => false,
                'message' => 'blog not found.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'please fix the error',
                'errors' => $validator->errors()
            ]);
        }

        $blog->title = $request->title;
        $blog->author = $request->author;
        $blog->description = $request->description;
        $blog->shortDesc = $request->shortDesc;
        $blog->save();

        //save image here
        $tempImage = TempImage::find($request->image_id);

        if($tempImage != null){
            //delete old image
            File::delete(public_path('uploads/blogs/' .$blog->image));

            $imageExtArray = explode('.',$tempImage->name);
            $ext = last($imageExtArray);
            $imageName = time().'-'.$blog->id.'.'.$ext;

            $blog -> image = $imageName;
            $blog->save();

            $sourcePath = public_path('uploads/temp/' .$tempImage->name);
            $destPath = public_path('uploads/blogs/' .$imageName);
            File::copy($sourcePath, $destPath);
        }

        return response()->json([
            'status' => true,
            'message' => 'blog updated successfully',
            'data' => $blog
        ]);
    }


    public function destroy($id){
        $blog = Blog::find($id);

        if ($blog == nuLL) {
            return response()->json([
                'status' => false,
                'message' => 'blog no found',
            ]);
        }

        //delete blog image first
        File::delete(public_path('uploads/blogs/' .$blog->image));

        //delete form database
        $blog->delete();

        return response()->json([
            'status' => true,
            'message' => 'blog deleted successfully',
        ]);
    }
}
