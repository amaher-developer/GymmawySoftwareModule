<?php

/**
 * CLEAN METHODS FOR GymMemberFrontController
 *
 * Replace the existing uploadExcel() and uploadExcelStore() methods in
 * GymMemberFrontController.php with these clean implementations.
 *
 * IMPORTANT: Delete the old methods completely (including all commented code)
 * and paste these methods in their place.
 */

/**
 * Show the Excel upload form
 *
 * @return \Illuminate\View\View
 */
public function uploadExcel()
{
    $title = trans('sw.members_excel_add');

    return view('software::Front.upload_excel', [
        'title' => $title
    ]);
}

/**
 * Process the uploaded Excel file and import members with subscriptions
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
 */
public function uploadExcelStore(Request $request)
{
    // Validate the uploaded file
    $validator = \Validator::make($request->all(), [
        'excel_data' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
    ], [
        'excel_data.required' => trans('sw.excel_file_required'),
        'excel_data.mimes' => trans('sw.excel_file_must_be_xlsx_or_xls'),
        'excel_data.max' => trans('sw.excel_file_max_size_5mb'),
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->all()
            ], 422);
        }

        return redirect(route('sw.uploadExcel'))
            ->withErrors($validator)
            ->withInput();
    }

    try {
        // Create import instance
        $import = new MembersSubscriptionsImport();

        // Import the Excel file
        Excel::import($import, $request->file('excel_data'));

        // Get import statistics
        $stats = $import->getStats();

        // Prepare response message
        $message = trans('sw.import_completed') . ': ' .
                   $stats['successful_rows'] . ' ' . trans('sw.successful') . ', ' .
                   $stats['failed_rows'] . ' ' . trans('sw.failed');

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $stats
            ]);
        }

        // Return redirect response for traditional form submissions
        if ($stats['failed_rows'] > 0) {
            return redirect(route('sw.uploadExcel'))
                ->with([
                    'warning' => $message,
                    'import_stats' => $stats
                ]);
        }

        return redirect(route('sw.uploadExcel'))
            ->with([
                'success' => $message,
                'import_stats' => $stats
            ]);

    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();
        $errors = [];

        foreach ($failures as $failure) {
            $errors[] = [
                'row_number' => $failure->row(),
                'attribute' => $failure->attribute(),
                'error_message' => implode(', ', $failure->errors())
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => trans('sw.validation_errors_in_excel'),
                'data' => [
                    'total_rows' => 0,
                    'successful_rows' => 0,
                    'failed_rows' => count($errors),
                    'errors' => $errors
                ]
            ], 422);
        }

        return redirect(route('sw.uploadExcel'))
            ->with([
                'error' => trans('sw.validation_errors_in_excel'),
                'import_stats' => [
                    'total_rows' => 0,
                    'successful_rows' => 0,
                    'failed_rows' => count($errors),
                    'errors' => $errors
                ]
            ]);

    } catch (\Exception $e) {
        \Log::error('Excel Import Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        $errorMessage = config('app.debug')
            ? $e->getMessage()
            : trans('sw.error_in_excel');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'data' => [
                    'total_rows' => 0,
                    'successful_rows' => 0,
                    'failed_rows' => 0,
                    'errors' => []
                ]
            ], 500);
        }

        return redirect(route('sw.uploadExcel'))
            ->with('error', $errorMessage);
    }
}
