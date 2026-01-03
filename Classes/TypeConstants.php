<?php

namespace Modules\Software\Classes;

class TypeConstants {
    /*
     * type:
     * 0: new membership client
     * 1: renew membership client
     * 2: edit membership client
     * 3: delete membership client
     * 4: new activity
     * 5: edit activity
     * 6: delete activity
     * 7: new block client
     * 8: edit block client
     * 9: delete block client
     * 10: new non client
     * 11: edit non client
     * 12: delete non client
     * 13: new membership
     * 14: edit membership
     * 15: delete membership
     * 16: new user
     * 17: edit user
     * 18: delete user
     * 19: send notification
     * 20: moneybox: add
     * 21: moneybox: sub
     * 22: moneybox: sub earning
     *
     * 23: export: PDF Activity
     * 24: export: Excel Activity
     * 25: export: PDF block client
     * 26: export: Excel block client
     * 27: export: PDF membership client
     * 28: export: Excel membership client
     * 29: export: PDF non client
     * 30: export: Excel non client
     * 31: export: PDF Moneybox report
     * 32: export: Excel Moneybox report
     * 33: export: PDF Membership
     * 34: export: Excel Membership
     * 35: export: PDF User
     * 36: export: Excel User
     * 41: export: PDF PT Subscription
     * 42: export: Excel PT Subscription
     * 43: export: PDF PT Class
     * 44: export: Excel PT Class
     * 45: export: PDF PT Trainer
     * 46: export: Excel PT Trainer
     * 47: export: PDF PT Member
     * 48: export: Excel PT Member
     *
     * 37: scan member
     *
     *
     * 49: new pt subscription
     * 50: edit pt subscription
     * 51: delete pt subscription
     *
     * 52: new pt class
     * 53: edit pt class
     * 54: delete pt class
     *
     * 55: new pt trainer
     * 56: edit pt trainer
     * 57: delete pt trainer
     *
     * 58: new pt member
     * 59: edit pt member
     * 60: delete pt member
     *
     *
     */

    const CreateMember = 0;
    const RenewMember = 1;
    const EditMember = 2;
    const DeleteMember = 3;

    const CreateActivity = 4;
    const EditActivity = 5;
    const DeleteActivity = 6;

    const CreateBlockMember = 7;
    const EditBlockMember = 8;
    const DeleteBlockMember = 9;

    const CreateNonMember = 10;
    const EditNonMember = 11;
    const DeleteNonMember = 12;

    const CreateSubscription = 13;
    const EditSubscription = 14;
    const DeleteSubscription = 15;

    const CreateUser = 16;
    const EditUser = 17;
    const DeleteUser = 18;

    const SendToUsers = 19;

    const CreateMoneyBoxAdd = 20;
    const CreateMoneyBoxWithdraw = 21;
    const CreateMoneyBoxWithdrawEarnings = 22;
    const DeleteMoneyBox = 185;
    const RestoreMoneyBox = 186;


    const ExportActivityPDF = 23;
    const ExportActivityExcel = 24;
    const ExportBlockMemberPDF = 25;
    const ExportBlockMemberExcel = 26;
    const ExportNonMemberPDF = 29;
    const ExportNonMemberExcel = 29;
    const ExportSubscriptionPDF = 33;
    const ExportSubscriptionExcel = 34;
    const ExportMemberPDF = 27;
    const ExportMemberExcel = 28;
    const ExportUserPDF = 35;
    const ExportUserExcel = 36;
    const ExportMoneyboxPDF = 31;
    const ExportMoneyboxExcel = 32;

    const ScanMember = 37;
    const FreezeMember = 38;
    const CreateMemberPayAmountRemainingForm = 39;
    const GenerateBarcode = 40;


    const ExportPTSubscriptionPDF = 41;
    const ExportPTSubscriptionExcel = 42;
    const ExportPTClassPDF = 43;
    const ExportPTClassExcel = 44;
    const ExportPTTrainerPDF = 45;
    const ExportPTTrainerExcel = 46;
    const ExportPTMemberPDF = 47;
    const ExportPTMemberExcel = 48;



    const CreatePTSubscription = 49;
    const EditPTSubscription = 50;
    const DeletePTSubscription = 51;


    const CreatePTClass = 52;
    const EditPTClass = 53;
    const DeletePTClass = 54;


    const CreatePTTrainer = 55;
    const EditPTTrainer = 56;
    const DeletePTTrainer = 57;


    const CreatePTMember = 58;
    const EditPTMember = 59;
    const DeletePTMember = 60;
    const ScanPTMember = 61;


    const CreateStoreProduct = 62;
    const EditStoreProduct = 63;
    const DeleteStoreProduct = 64;

    const ExportStoreProductPDF = 65;
    const ExportStoreProductExcel = 66;

    const CreateStoreOrder = 67;
    const EditStoreOrder = 68;
    const DeleteStoreOrder = 69;

    const ExportStoreOrderPDF = 70;
    const ExportStoreOrderExcel = 71;


    const CreateTrainingMember = 72;
    const EditTrainingMember = 73;
    const DeleteTrainingMember = 74;

    const ExportTrainingMemberPDF = 75;
    const ExportTrainingMemberExcel = 76;

    const CreateTrainingPlan = 77;
    const EditTrainingPlan = 78;
    const DeleteTrainingPlan = 79;

    const ExportTrainingPlanPDF = 80;
    const ExportTrainingPlanExcel = 81;

    const CreateTrainingTrack = 82;
    const EditTrainingTrack = 83;
    const DeleteTrainingTrack = 84;

    const ExportTrainingTrackPDF = 85;
    const ExportTrainingTrackExcel = 86;


    const ExportPotentialMemberPDF = 87;
    const ExportPotentialMemberExcel = 88;


    const CreatePotentialMember = 89;
    const EditPotentialMember = 90;
    const DeletePotentialMember = 91;


    const ExportBannerPDF = 92;
    const ExportBannerExcel = 93;

    const CreateBanner = 94;
    const EditBanner = 95;
    const DeleteBanner = 96;


    const CreatePTMemberPayAmountRemainingForm = 97;
    const RenewPTMember = 98;


    const PayPTTrainerCommission = 99;
    const EditPTTrainerSchedule = 100;


    const CreateStorePurchaseOrder = 101;
    const DeleteStorePurchaseOrder = 102;
    const UploadContractFileMember = 103;
    const UploadContractFileNonMember = 104;
    const UploadContractFilePTMember = 105;


    const CreatePaymentType = 106;
    const EditPaymentType = 107;
    const DeletePaymentType = 108;


    const CreateGroupDiscount = 109;
    const EditGroupDiscount = 110;
    const DeleteGroupDiscount = 111;


    const CreateSaleChannel = 112;
    const EditSaleChannel = 113;
    const DeleteSaleChannel = 114;

    const CreateStoreGroup = 115;
    const EditStoreGroup = 116;
    const DeleteStoreGroup = 117;

    const CreateMoneyBoxType = 118;
    const EditMoneyBoxType = 119;
    const DeleteMoneyBoxType = 120;



    const CreateTrainingTask = 121;
    const EditTrainingTask = 122;
    const DeleteTrainingTask = 123;
    const ExportTrainingTaskPDF = 124;
    const ExportTrainingTaskExcel = 125;



    const CreateTrainingFile = 126;
    const EditTrainingFile = 127;
    const DeleteTrainingFile = 128;
    const ExportTrainingFilePDF = 129;
    const ExportTrainingFileExcel = 130;



    const CreateTrainingMedicine = 131;
    const EditTrainingMedicine = 132;
    const DeleteTrainingMedicine = 133;
    const ExportTrainingMedicinePDF = 134;
    const ExportTrainingMedicineExcel = 135;


    const DeleteReservationMember = 136;


    const UploadSignatureFileMember = 137;
    const UploadSignatureFileNonMember = 138;
    const UploadSignatureFilePTMember = 139;


    const CreateCategory = 140;
    const EditCategory = 141;
    const DeleteCategory = 142;

    const ExportCategoryPDF = 143;
    const ExportCategoryExcel = 145;

    const ExportTodayMemberPDF = 146;
    const ExportTodayMemberExcel = 147;

    const ExportTodayNonMemberPDF = 148;
    const ExportTodayNonMemberExcel = 149;

    const ExportTodayPTMemberPDF = 150;
    const ExportTodayPTMemberExcel = 151;

    const ExportExpireMemberPDF = 152;
    const ExportExpireMemberExcel = 153;

    const ExportSubscriptionMemberPDF = 154;
    const ExportSubscriptionMemberExcel = 155;

    const ExportPTSubscriptionMemberPDF = 156;
    const ExportPTSubscriptionMemberExcel = 157;

    const EditEventNotification = 158;
    const AddCreditAmount = 159;
    const RefundCreditAmount = 160;


    const ExportRenewMemberPDF = 161;
    const ExportRenewMemberExcel = 162;

    const CreateStoreCategory = 163;
    const EditStoreCategory = 164;
    const DeleteStoreCategory = 165;
    const ExportStoreCategoryPDF = 166;
    const ExportStoreCategoryExcel = 167;   
    
    const CreateSubscriptionCategory = 168;
    const EditSubscriptionCategory = 169;
    const DeleteSubscriptionCategory = 170;
    const ExportSubscriptionCategoryPDF = 171;
    const ExportSubscriptionCategoryExcel = 172;
    
    const CreatePermissionGroup = 173;
    const EditPermissionGroup = 174;
    const DeletePermissionGroup = 175;
    const CreateEmployeeTransaction = 176;
    const EditEmployeeTransaction = 177;
    const DeleteEmployeeTransaction = 178;

    const ExportUserAttendeesExcel = 179;
    const ExportUserAttendeesPDF = 180;
    const ExportStoreExcel = 181;
    const ExportStorePDF = 182;
    const ExportOnlinePaymentExcel = 183;
    const ExportOnlinePaymentPDF = 184;

    const Add = 0;
    const Sub = 1;
    const SubEarning = 2;

    const Found = 1;
    const NotFound = 0;

    const Active = 0;
    const Freeze = 1;
    const Expired = 2;
    const Coming = 3;
    const BarcodeType = 'C128';
    const QRCodeType = 'QRCODE';

    const CountryPhoneCode = '+2';
    const EGYPT_PHONE_PREFIX = '002';
    const KUWAIT_PHONE_PREFIX = '00965';

    const TRAINING_PLAN_TYPE = 1;
    const DIET_PLAN_TYPE = 2;

    const AMOUNT_REMAINING_STATUS_TURE = 1;
    const AMOUNT_REMAINING_STATUS_FALSE = 2;

    const WA_MAX_USER = 50;
    const WA_MAX_MESSAGE = 1000;
    const WA_ULTRA_MAX_MESSAGE = 100;

    const CASH_PAYMENT = 0;
    const ONLINE_PAYMENT = 1;
    const BANK_TRANSFER_PAYMENT = 2;

    const CASH_RECEIPT = 0;
    const PROMISSORY_RECEIPT = 1;
    const INSURANCE_PAYMENT = 2;
    const EXPENSES_RECEIPT = 3;
    const TRANSFER_RECEIPT = 4; //Transfer from the fund to the bank
    const BALANCE_PAYMENT = 5; //Bank balance transfer

    const ZK_ACTIVE_MEMBER = 0;
    const ZK_NEW_MEMBER = 1;
    const ZK_EXPIRE_MEMBER = 2;
    const ZK_SET_MEMBER = 3;

    const TAX_TRANSACTION_SALES = 1;
    const TAX_TRANSACTION_REFUND = 2;

    const RENEW_MEMBERSHIPS_MAX_NUM = 2;
    const FEMALE = 2;
    const MALE = 1;

    const ATTENDANCE_TYPE_PT = 1;
    const ATTENDANCE_TYPE_GYM = 0;
    const ATTENDANCE_TYPE_ACTIVITY = 2;


    const NOTIFICATION_EXTERNAL_URL = 1;
    const NOTIFICATION_GENERAL_MESSAGE = 2;

    const PAYPAL_TRANSACTION_FEES = 2;
    const PAYMOB_TRANSACTION_FEES = 3;
    const SUCCESS = 1;
    const FAILURE = 2;

    const YES = 1;
    const NO = 2;
}
