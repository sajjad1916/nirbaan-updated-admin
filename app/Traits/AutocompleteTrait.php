<?php

namespace App\Traits;


use App\Models\Vendor;



trait AutocompleteTrait
{


   
    public $vendorIDS;
    public $vendorSearchClause = ['creator_id' => 0];
    public $selectedVendors;
  

    //vendors
    public function autocompleteVendorSelected($vendor)
    {
        try {

            if (count($this->vendorIDS ?? []) < 1) {
                $this->vendorIDS = [];
            }

            // 
            $vendorId = $vendor['id'];
            $newVendorIDs = $this->vendorIDS;
            if (!is_array($newVendorIDs)) {
                $newVendorIDs = $newVendorIDs->toarray();
            }
            //if product already exists
            if (!in_array($vendorId, $newVendorIDs)) {
                array_push($newVendorIDs, $vendorId);
            }
            $this->vendorIDS = $newVendorIDs;
            $this->selectedVendors = Vendor::whereIn('id', $this->vendorIDS)->get();
            //
        } catch (\Exception $ex) {
            logger("Error", [$ex]);
        }
    }

    //
    public function removeSelectedVendor($id)
    {
        $this->selectedVendors = $this->selectedVendors->reject(function ($element) use ($id) {
            return $element->id == $id;
        });

        //
        $this->vendorIDS = $this->selectedVendors->pluck('id') ?? [];
    }
}
