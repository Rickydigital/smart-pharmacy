<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\District;
use App\Models\Region;
use App\Models\Street;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $countries,
        ]);
    }

    public function regions(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
        ]);

        $regions = Region::query()
            ->where('country_id', $request->integer('country_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $regions,
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => ['required', 'integer', 'exists:regions,id'],
        ]);

        $districts = District::query()
            ->where('region_id', $request->integer('region_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $districts,
        ]);
    }

    public function wards(Request $request): JsonResponse
    {
        $request->validate([
            'district_id' => ['required', 'integer', 'exists:districts,id'],
        ]);

        $wards = Ward::query()
            ->where('district_id', $request->integer('district_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $wards,
        ]);
    }

    public function streets(Request $request): JsonResponse
    {
        $request->validate([
            'ward_id' => ['required', 'integer', 'exists:wards,id'],
        ]);

        $streets = Street::query()
            ->where('ward_id', $request->integer('ward_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $streets,
        ]);
    }

    public function streetShow(Street $street): JsonResponse
    {
        $street->load('ward.district.region.country');

        return response()->json([
            'data' => [
                'id' => $street->id,
                'name' => $street->name,
                'full_name' => $street->full_name,
                'ward' => $street->ward?->name,
                'district' => $street->ward?->district?->name,
                'region' => $street->ward?->district?->region?->name,
                'country' => $street->ward?->district?->region?->country?->name,
            ],
        ]);
    }
}