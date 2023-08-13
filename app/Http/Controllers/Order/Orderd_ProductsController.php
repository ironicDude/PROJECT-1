<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderedProduct;

class Orderd_ProductsController extends Controller
{
    
public function create_orderd_product(Request $request, $orderId)
{
    $productsData = $request->input('products'); // قم بجلب بيانات المنتجات من الطلب

    // foreach ($productsData as $productData) {
    //     // إنشاء منتج مطلوب في الفاتورة
    //     $invoiceProduct = new OrderedProduct([
    //         'order_id' => $orderId,
    //         'dated_product_id' => $productData['dated_product_id'],
    //         'quantity' => $productData['quantity'],
    //         'subtotal' => $productData['subtotal'],
    //     ]);

    //     $invoiceProduct->save();
    // }
    foreach ($productsData as $productData) {
        // إنشاء منتج في الفاتورة
        $invoiceProduct = new OrderedProduct([
            'order_id' => $orderId,
            'dated_product_id' => $productData['dated_product_id'],
            'quantity' => $productData['quantity'],
            'subtotal' => $productData['subtotal'],
            'is_deleted' => false,
        ]);

        $invoiceProduct->save();
    }

    return response()->json(['message' => 'تم إنشاء الفاتورة بنجاح.']);

    return response()->json(['message' => 'تم إضافة المنتجات المطلوبة للفاتورة بنجاح.']);
}
public function update_orderd_product(Request $request, $productId)
{
    $product = OrderedProduct::findOrFail($productId);

    $product->update([
        // 'dated_product_id' => $request->input('dated_product_id'),
        'quantity' => $request->input('quantity'),
        // 'subtotal' => $request->input('subtotal'),
    ]);

    return response()->json(['message' => 'تم تحديث بيانات المنتج بنجاح.']);
}
public function destroy($productId)
{
    // $product = OrderedProduct::findOrFail($productId);
    
    // return response()->json(['message' => 'تم حذف المنتج من الفاتورة بنجاح.']);
    $invoiceProduct = OrderedProduct::findOrFail($productId);
    $invoiceProduct ->delete();

    // تحديث الحقل "is_deleted" للإشارة إلى المنتج المحذوف
    $invoiceProduct->update(['is_deleted' => true]);

    return response()->json(['message' => 'تم حذف المنتج من الفاتورة بنجاح.']);
}


}