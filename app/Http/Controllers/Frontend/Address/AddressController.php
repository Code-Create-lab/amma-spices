<?php

namespace App\Http\Controllers\Frontend\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Address\AddressStoreRequest;
use App\Http\Requests\Frontend\Address\AddressUpdateRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\Society;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->where('is_deleted', 0)->get();
        return view('frontend.address.index', [
            'addresses' => $addresses
        ]);
    }
    public function store(AddressStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $city = City::create([
                'city_name' => $request->city
            ]);
            $society = Society::create([
                'society_name' => $request->society,
                'city_id' => $city->id
            ]);
            Address::create([
                'type' => $request->type,
                'user_id' => Auth::id(),
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'city' => $request->city,
                'society' => $request->society,
                'city_id' => $city->id,
                'society_id' => $society->id,
                'house_no' => $request->house_no,
                'landmark' => $request->type,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'lat' => 0,
                'lng' => 0,
                'select_status' => 1,
                'added_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'receiver_email' => $request->receiver_email
            ]);
            DB::commit();
            return Redirect::route('dashboard_my_addresses')->with('success', 'Address Created Successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
    public function edit(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:address,uuid'
        ]);
        $address = Address::where('uuid', $request->address_id)->first();
        if (!empty($address)) {
            return Response::json([
                'success' => true,
                'address' => $address,
                'message' => 'Address Fetched Successfully'
            ]);
        }
        return Response::json([
            'success' => false,
            'message' => 'Address Not Found'
        ]);
    }
    public function update(AddressUpdateRequest $request)
    {
        try {

          
            $address = Address::where('uuid', $request->address_id)->first();
            if (!$address) {
                return Redirect::back()->with('error', 'Address not found');
            }
            $society = Society::where('society_id', $address->society_id)->first();
            $city = City::where('city_id', $address->city_id)->first();

            //   dd($society,$address, $city);

            if($society){

                $society->update([
                    'society_name' => $request->society
                ]);
            }
            $city->update([
                'city_name' => $request->city
            ]);
            $data = [
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'house_no' => $request->house_no,
                'society' => $request->society,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'type' => $request->type,
                'receiver_email' => $request->receiver_email
            ];
            $address->update($data);
            return Redirect::route('dashboard_my_addresses')->with('success', 'Address Updated Successfully');
        } catch (Exception $e) {
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
    public function delete(Request $request, $address_id)
    {
        $address = Address::where('uuid', $address_id)->first();
        $address->update([
            'is_deleted' => true
        ]);
        return Redirect::route('dashboard_my_addresses')->with('success', 'Address Deleted Successfully');
    }
}
