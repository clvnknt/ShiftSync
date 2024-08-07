<?php

namespace App\Jobs\ShiftJobs;

use App\Models\EmployeeAssignedShift;
use App\Models\EmployeeRecord;
use App\Models\EmployeeShiftRecord;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateEmployeeShiftRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employeeTimezone;

    /**
     * Create a new job instance.
     *
     * @param string $employeeTimezone The timezone of the employee
     */
    public function __construct($employeeTimezone)
    {
        $this->employeeTimezone = $employeeTimezone;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get the current date using the employee's timezone
            $currentDateLocal = Carbon::now($this->employeeTimezone)->startOfMonth();
            
            // Get the last day of the month
            $lastDayOfMonthLocal = Carbon::now($this->employeeTimezone)->endOfMonth();

            // Loop through each day until the end of the month
            while ($currentDateLocal->lte($lastDayOfMonthLocal)) {
                // Retrieve the employee records with the provided timezone
                $employees = EmployeeRecord::where('employee_timezone', $this->employeeTimezone)->get();

                // Loop through the employees and create shift records for their active assigned shifts
                foreach ($employees as $employee) {
                    $assignedShifts = $employee->assignedShifts()->where('is_active', true)->get();

                    foreach ($assignedShifts as $assignedShift) {
                        // Retrieve the shift schedule for the assigned shift
                        $shiftSchedule = $assignedShift->shiftSchedule;

                        // Calculate the shift end date based on the shift schedule's times and timezone
                        $shiftStartHour = Carbon::createFromFormat('H:i:s', $shiftSchedule->start_shift_time, $shiftSchedule->shift_timezone)->hour;
                        $shiftEndHour = Carbon::createFromFormat('H:i:s', $shiftSchedule->end_shift_time, $shiftSchedule->shift_timezone)->hour;

                        // Adjust end date if the end hour indicates it extends to the next day
                        $endDateLocal = ($shiftEndHour < $shiftStartHour) ?
                            $currentDateLocal->copy()->addDay() : $currentDateLocal;

                        // Convert local shift dates to UTC
                        $currentDateUTC = $currentDateLocal->copy()->setTimezone('UTC');
                        $endDateUTC = $endDateLocal->copy()->setTimezone('UTC');

                        // Check if there is no existing shift record for this assigned shift and date range
                        $existingShiftRecord = EmployeeShiftRecord::where('employee_assigned_shift_id', $assignedShift->id)
                            ->where('shift_date', $currentDateUTC)
                            ->where('end_shift_date', $endDateUTC)
                            ->exists();

                        // If no existing shift record found, create a new one
                        if (!$existingShiftRecord) {
                            // Create a new shift record for this assigned shift and date range
                            EmployeeShiftRecord::create([
                                'shift_date' => $currentDateUTC,
                                'end_shift_date' => $endDateUTC,
                                'employee_assigned_shift_id' => $assignedShift->id,
                                'employee_record_id' => $employee->id,
                                'start_shift' => null,
                                'start_lunch' => null,
                                'end_lunch' => null,
                                'end_shift' => null,
                            ]);

                            // Log shift record creation for debugging
                            Log::info('Shift record created for Employee ID: ' . $employee->id);
                        }
                    }
                }
                
                // Move to the next day
                $currentDateLocal->addDay();
            }

            // Output success message
            Log::info('Monthly shift records creation completed successfully.');
        } catch (\Exception $e) {
            // Log any exceptions for debugging
            Log::error('Exception occurred: ' . $e->getMessage());
            Log::error('An error occurred while creating monthly shift records.');
        }
    }
}