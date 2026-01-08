<?php

/**
 * Translation Keys Required for Excel Import Feature
 *
 * Add these keys to your language files:
 * - resources/lang/en/sw.php
 * - resources/lang/ar/sw.php
 */

return [
    // English translations (add to resources/lang/en/sw.php)
    'en' => [
        // Backend messages
        'excel_file_required' => 'Excel file is required',
        'excel_file_must_be_xlsx_or_xls' => 'The file must be an Excel file (.xlsx or .xls)',
        'excel_file_max_size_5mb' => 'Excel file size must not exceed 5MB',
        'import_completed' => 'Import completed',
        'successful' => 'successful',
        'failed' => 'failed',
        'validation_errors_in_excel' => 'Validation errors found in Excel file',
        'error_in_excel' => 'Error processing Excel file',
        'members_excel_add' => 'Import Members from Excel',

        // UI labels and buttons
        'upload_excel_file' => 'Upload Excel File',
        'select_excel_file' => 'Select Excel File',
        'upload_and_import' => 'Upload and Import',
        'instructions' => 'Instructions',
        'download_template_example' => 'Download Template Example',
        'allowed_types' => 'Allowed types',
        'max_size' => 'Max size',

        // Instructions section
        'excel_template_instructions' => 'Excel Template Instructions',
        'required_columns' => 'Required Columns',
        'optional_columns' => 'Optional Columns',
        'required' => 'Required',
        'date_format' => 'Date Format',
        'example' => 'Example',
        'gender_values' => 'Gender values',
        'status_values' => 'Status values',
        'discount_type_values' => 'Discount type values',

        // Statistics section
        'import_statistics' => 'Import Statistics',
        'total_rows' => 'Total Rows',
        'successful_rows' => 'Successful',
        'failed_rows' => 'Failed',
        'success_rate' => 'Success Rate',
        'error_details' => 'Error Details',
        'row_number' => 'Row #',
        'phone' => 'Phone',
        'error_message' => 'Error Message',

        // JavaScript messages
        'please_select_excel_file' => 'Please select an Excel file to upload',
        'invalid_file_type' => 'Invalid file type. Please upload .xlsx or .xls file only',
        'file_too_large' => 'File size exceeds 5MB. Please upload a smaller file',
        'uploading' => 'Uploading',
    ],

    // Arabic translations (add to resources/lang/ar/sw.php)
    'ar' => [
        // Backend messages
        'excel_file_required' => 'ملف Excel مطلوب',
        'excel_file_must_be_xlsx_or_xls' => 'يجب أن يكون الملف من نوع Excel (.xlsx أو .xls)',
        'excel_file_max_size_5mb' => 'يجب ألا يتجاوز حجم ملف Excel 5 ميجابايت',
        'import_completed' => 'اكتمل الاستيراد',
        'successful' => 'ناجح',
        'failed' => 'فاشل',
        'validation_errors_in_excel' => 'تم العثور على أخطاء التحقق في ملف Excel',
        'error_in_excel' => 'خطأ في معالجة ملف Excel',
        'members_excel_add' => 'استيراد الأعضاء من Excel',

        // UI labels and buttons
        'upload_excel_file' => 'رفع ملف Excel',
        'select_excel_file' => 'اختر ملف Excel',
        'upload_and_import' => 'رفع واستيراد',
        'instructions' => 'التعليمات',
        'download_template_example' => 'تحميل نموذج القالب',
        'allowed_types' => 'الأنواع المسموحة',
        'max_size' => 'الحجم الأقصى',

        // Instructions section
        'excel_template_instructions' => 'تعليمات قالب Excel',
        'required_columns' => 'الأعمدة المطلوبة',
        'optional_columns' => 'الأعمدة الاختيارية',
        'required' => 'مطلوب',
        'date_format' => 'تنسيق التاريخ',
        'example' => 'مثال',
        'gender_values' => 'قيم الجنس',
        'status_values' => 'قيم الحالة',
        'discount_type_values' => 'قيم نوع الخصم',

        // Statistics section
        'import_statistics' => 'إحصائيات الاستيراد',
        'total_rows' => 'إجمالي الصفوف',
        'successful_rows' => 'الناجحة',
        'failed_rows' => 'الفاشلة',
        'success_rate' => 'معدل النجاح',
        'error_details' => 'تفاصيل الأخطاء',
        'row_number' => 'رقم الصف',
        'phone' => 'الهاتف',
        'error_message' => 'رسالة الخطأ',

        // JavaScript messages
        'please_select_excel_file' => 'يرجى اختيار ملف Excel للرفع',
        'invalid_file_type' => 'نوع ملف غير صالح. يرجى رفع ملف .xlsx أو .xls فقط',
        'file_too_large' => 'حجم الملف يتجاوز 5 ميجابايت. يرجى رفع ملف أصغر',
        'uploading' => 'جاري الرفع',
    ],
];

/**
 * USAGE INSTRUCTIONS:
 *
 * 1. Open: resources/lang/en/sw.php
 * 2. Add the English translations from above
 *
 * 3. Open: resources/lang/ar/sw.php
 * 4. Add the Arabic translations from above
 *
 * Example format in sw.php:
 *
 * return [
 *     // ... existing translations ...
 *
 *     // Excel Import Feature
 *     'excel_file_required' => 'Excel file is required',
 *     'excel_file_must_be_xlsx_or_xls' => 'The file must be an Excel file (.xlsx or .xls)',
 *     // ... etc
 * ];
 */
