<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PackageTypePricing;
use App\Models\Vendor;
use Illuminate\Http\Request;


class PackageOrderController extends Controller
{
    //
    public function summary(Request $request)
    {
        //
        $packageTypePricing = PackageTypePricing::where('vendor_id', 1)
            ->where('package_type_id', $request->package_type_id)->first();


        //calculation time
        $tax = 0;
        $sizeAmount = 0;
        // $distanceAmount = 0;
        $totalAmount = 0;
        $productPrice = $request->productPrice;
        //calculate the weigth price
        if ($packageTypePricing->price_per_kg) {
            if($request->weight > 0 && $request->weight <= 1){
                $sizeAmount = 30;
            }
            else if($request->weight > 1 && $request->weight <= 2){
                $sizeAmount = 60;
            }
            else if($request->weight > 2 && $request->weight <= 3){
                $sizeAmount = 90;
            }
            else if($request->weight > 3 && $request->weight <= 4){
                $sizeAmount = 120;
            }
            else if($request->weight > 4 && $request->weight <= 5){
                $sizeAmount = 150;
            }
            else if($request->weight > 5){
            return $sizeAmount = 220;
            }
        } else {
            $sizeAmount = $packageTypePricing->size_price;
        }

       $sizeAmount += $packageTypePricing->base_price;
        $totalAmount = $sizeAmount + $productPrice;

        return response()->json([
            "delivery_fee" => $sizeAmount,
            "package_type_fee" => $productPrice,
            "total" => $totalAmount,
        ]); 
    }  
}
