<?php

namespace App\Http\Controllers;

use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TempImageController extends Controller
{
    public function store(Request $request){
        //vaidation
        $validator = Validator::make($request->all(),[
            'image' => 'required|image'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'please fix errors',
                'errors' => $validator->errors()
            ]);
        }

        //upload image
        $image = $request->image;

        $ext = $image->getClientOriginalExtension();
        $imageName = time().'.'.$ext;

        //store image info in database
        $tempImage = new TempImage();
        $tempImage->name = $imageName;
        $tempImage->save();

        //move image in temp drc
        $image->move(public_path('uploads/temp'), $imageName);

        return response()->json([
            'status' => true,
            'message' => 'image uploaded successfully',
            'image' => $tempImage
        ]);
    }
}
