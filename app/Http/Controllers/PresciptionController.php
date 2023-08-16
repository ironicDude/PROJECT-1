<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\FileModel;
use App\Models\Prescription;
use App\Models\Order;
class PresciptionController extends Controller
{
    public function index()
    {
        // جلب جميع الوصفات
        $prescriptions = Prescription::all();
        
        return response()->json([
            'prescriptions' => $prescriptions
        ]);
    }
    public function show($order_id)
    {
        // البحث عن وصفة الطبية المرتبطة بالفاتورة
        $prescription = Prescription::where('order_id', $order_id)->get();
    
        // التحقق من وجود وصفة طبية
        if (!$prescription) {
            return response()->json([
                'message' => 'وصفة الطبية غير موجودة',
            ], 404);
        }
    
        return response()->json([
            'message' => 'جلب وصفة الطبية بنجاح',
            'prescription' => $prescription,
        ]);
    }
        // public function store(Request $request)
        // {
        //     // استلام بيانات الوصفة الطبية
        //     $file_name = $request->file('file_name');
        //     $order_id = $request->input('order_id');
        
        //     // التحقق من وجود فاتورة مطابقة
        //     $order = Order::find($order_id);
        //     if (!$order) {
        //         return response()->json([
        //             'message' => 'الفاتورة غير موجودة',
        //         ], 404);
        //     }
        //     $prescription = new Prescription;

        //     if ($request->hasFile('image')) {
        //         $file = $request->file('image');
        //         $file_name = time() . '_' . $file->getClientOriginalName();
        //         $file->storeAs('images', $file_name, 'public');
        //         $prescription->image = $file_name;
        
        //         // Set the order_id from the request
        //         $prescription->order_id = $request->input('order_id');
        
        //         $prescription->save();
        
        //         return response()->json(['message' => 'Prescription uploaded successfully']);
        //     }
        
        //     return response()->json(['message' => 'Prescription upload failed'], 400);
        
        // }
        public function store(Request $request)
        {
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // تحقق من صحة الصورة
            ]);
    
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                // $file_name = $request->file('file_name');
                $order_id = $request->input('order_id');
                $file_name = time() . '_' . $file->getClientOriginalName();
                Storage::disk('public')->putFileAs('uploads', $file, $file_name);
    
                // Store the file name or path in the database
                $fileModel = new Prescription();
                // $fileModel->name = $file_name;
                $fileModel->image = $file_name;
                $fileModel->order_id = $order_id;
                $fileModel->path = 'uploads/' . $file_name;
                $fileModel->save();
    
                return response()->json(['message' => 'Image uploaded successfully']);
            }
    
            return response()->json(['message' => 'Image upload failed'], 400);
        }
        // public function update(Request $request, $order_id)
        // {
            
        //     $fileModel = Prescription::find($order_id);
        //             if (!$fileModel) {
        //                 return response()->json(['message' => 'File not found'], 404);
        //             }
        //     $this->validate($request, [
        //         'new_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // تحقق من صحة الصورة الجديدة
        //     ]);
    
    
        //     if ($request->hasFile('new_image')) {
        //         // Delete the old file from the storage
        //         if ($fileModel->path) {
        //             Storage::disk('public')->delete($fileModel->path);
        //         }
    
        //         // Upload the new image
        //         $newFile = $request->file('new_image');
        //         $newFileName = time() . '_' . $newFile->getClientOriginalName();
        //         Storage::disk('public')->putFileAs('uploads', $newFile, $newFileName);
    
        //         // Update the file information in the database
        //         // $fileModel->image = $newFileName;
        //         // $fileModel->image = $file_name;
        //         $fileModel->path = 'uploads/' . $newFileName;
        //         $fileModel->save();
    
        //         return response()->json(['message' => 'File updated successfully']);
        //     }
    
        //     return response()->json(['message' => 'New image upload failed'], 400);
        // }
        public function update(Request $request, $id)
{
    $this->validate($request, [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif', // تحقق من صحة الصورة الجديدة
    ]);

    $fileModel = Prescription::findOrFail($id);

    if ($request->hasFile('image')) {
        // حذف الصورة القديمة من التخزين إذا كانت موجودة
        if ($fileModel->path) {
            Storage::disk('public')->delete($fileModel->path);
        }

        // تحميل الصورة الجديدة
        $newFile = $request->file('image');
        $newFileName = time() . '_' . $newFile->getClientOriginalName();
        // Storage::disk('public')->putFileAs('uploads', $newFile, $newFileName);

        // تحديث معلومات الصورة في قاعدة البيانات
        $fileModel->image = $newFileName;
        $fileModel->path = 'uploads/' . $newFileName;
        $fileModel->save();

        return response()->json(['message' => 'Image updated successfully']);
    }

    return response()->json(['message' => 'New image upload failed'], 400);
}

public function getImage($id)
{
    $imageData = DB::table('prescriptions')->where('id', $id)->first();

    if ($imageData) {
        $imagePath = $imageData->path; // افترض أن اسم العمود هو image_path
        $imageContents = Storage::disk('public')->get($imagePath);

        return response($imageContents)->header('Content-Type', 'image/jpeg'); // تستبدل نوع الصورة حسب النوع الصحيح
    }

    return response('Image not found', 404);
}

}
