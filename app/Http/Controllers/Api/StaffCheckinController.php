<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\StaffCheckin;
use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StaffCheckinController extends ApiBaseController
{
    public function checkIn(Request $request)
    {
        $company = company();
        $loggedInUser = user();
        $warehouse = warehouse();
        if ($request->type == "mobile") {
            $ip = $request->ip;
            $location_details = $request->location_details;
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
            // // Log::info($request->headers->get('User-Agent'));
            $location_details = $request->headers->get('User-Agent');
        }
        $existingCheckin = StaffCheckin::where('user_id', $loggedInUser->id)
            ->where('date', $request->date)
            ->first();

        if ($existingCheckin) {
            // // Log::info($request);
            // If there's an existing check-in record, update it with check-out details
            $existingCheckin->update([
                'check_out_time' => $request->time,
                'hours' => $this->calculateWorkingHours($existingCheckin->check_in_time, $request->time),
                'check_out_ip' => $ip,
                'check_out_location_details' => $location_details,
                'status' => 1,
            ]);

            return response()->json(['message' => 'Check-out updated successfully', 'data' => $existingCheckin], 200);
        } else {
            $staffCheckin = new StaffCheckin();
            $staffCheckin['company_id'] = $company->id;
            $staffCheckin['warehouse_id'] = $warehouse->id;
            $staffCheckin['user_id'] = $loggedInUser->id;
            $staffCheckin['date'] = $request->date;
            $staffCheckin['check_in_time'] = $request->time;
            $staffCheckin['check_in_ip'] = $ip;
            $staffCheckin['created_by'] = $loggedInUser->id;
            $staffCheckin['check_in_location_details'] = $location_details;
            $staffCheckin['type'] = $request->type;

            $staffCheckin->save();

            return response()->json(['message' => 'New check-in record created successfully', 'data' => $staffCheckin], 201);
        }
    }

    private function calculateWorkingHours($checkInTime, $checkOutTime)
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);

        // Calculate the difference between check-in and check-out times in seconds
        $diffInSeconds = $checkOut->diffInSeconds($checkIn);

        // Convert the difference to hours, minutes, and seconds
        $hours = round((abs($diffInSeconds - 1800)) / 3600, 0); // 1 hour = 3600 seconds
        $minutes = ($diffInSeconds % 3600) / 60; // 1 minute = 60 seconds

        // Combine hours, minutes, and seconds to get the total hours with more accurate rounding
        $totalHours = $hours . '.' . $minutes;

        return $totalHours;
    }



    public function getCheckIn(Request $request)
    {
        $company = company();
        $loggedInUser = user();
        $warehouse = warehouse();
        $currentDate = Carbon::now()->format('Y-m-d');

        // // Log::info($currentDate);


        $staffCheckin = StaffCheckin::where("warehouse_id", $warehouse->id)
            ->where('user_id', $loggedInUser->id)
            ->where('date', $currentDate)
            ->first();

        if ($staffCheckin) {
            return response()->json(['message' => 'Fetch Successful!', 'data' => $staffCheckin, 'status' => true], 200);
        } else {
            return response()->json(['message' => 'Fetch Successful!', 'data' => $staffCheckin, 'status' => false], 200);
        }
    }
}
