<?php
use App\Http\Controllers\Api\AdminSubscriptionController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TransportQuoteController;
use App\Http\Controllers\Api\TransporterVehicleController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==================== AUTH & PUBLIC ROUTES ====================
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductListingController;
use App\Http\Controllers\Api\CommodityCategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\LivestockListingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProviderProfileController;
use App\Http\Controllers\Api\ServicePackageController;
use App\Http\Controllers\Api\MachineryCategoryController;
use App\Http\Controllers\Api\MachineryBrandController;
use App\Http\Controllers\Api\MachineryModelController;
use App\Http\Controllers\Api\MachineryListingController;
use App\Http\Controllers\Api\MachineryBookingController;
use App\Http\Controllers\Api\MachineryReviewController;
use App\Http\Controllers\Api\ExtensionOfficerController;
use App\Http\Controllers\Api\ResearchInstitutionController;
use App\Http\Controllers\Api\ResearcherController;
use App\Http\Controllers\Api\ResearchPublicationController;
use App\Http\Controllers\Api\DemonstrationFarmController;
use App\Http\Controllers\Api\DiseaseController;
use App\Http\Controllers\Api\DiseaseOutbreakController;
use App\Http\Controllers\Api\OrganisationInvitationController;

// ==================== PROTECTED ROUTES ====================
use App\Http\Controllers\Api\FarmerProfileController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OfferDecisionController;
use App\Http\Controllers\Api\LogisticsController;
use App\Http\Controllers\Api\TransportAssignmentController;
use App\Http\Controllers\Api\DeliveryUpdateController;
use App\Http\Controllers\Api\PaymentController; 
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\AdminWithdrawalController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminModerationController;
use App\Http\Controllers\Api\InputListingController;
use App\Http\Controllers\Api\AdminReportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\ShoppingCartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\FinanceDashboardController;
use App\Http\Controllers\Api\FinanceReportController;
use App\Http\Controllers\Api\LoanApplicationController;
use App\Http\Controllers\Api\LoanProductController;
use App\Http\Controllers\Api\NmbBankController;
use App\Http\Controllers\Api\FinanceDossierController;
use App\Http\Controllers\Api\AdminBankLinkController;
use App\Http\Controllers\Api\NmbOAuthController;
use App\Http\Controllers\Api\FinancialInstitutionController;
use App\Http\Controllers\Api\InsuranceProductController;
use App\Http\Controllers\Api\KnowledgeInstitutionController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\TrainingCourseController;
use App\Http\Controllers\Api\AIAdvisorController;
use App\Http\Controllers\Api\KnowledgeHubController;
use App\Http\Controllers\Api\GovernmentAnnouncementController;
use App\Http\Controllers\Api\SubsidyProgramController;
use App\Http\Controllers\Api\SubsidyApplicationController;
use App\Http\Controllers\Api\AgriculturalRegistrationController;
use App\Http\Controllers\Api\AgriculturalStatisticController;
use App\Http\Controllers\Api\GovernmentDashboardController;
use App\Http\Controllers\Api\AnalyticsController;

use App\Http\Controllers\Api\AdvisoryRequestController;
use App\Http\Controllers\Api\FarmVisitController;
use App\Http\Controllers\Api\ExtensionOfficerRatingController;
use App\Http\Controllers\Api\DiseaseReportController;
use App\Http\Controllers\Api\DiseaseDiagnosisController;
use App\Http\Controllers\Api\DiseaseVerificationController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\CropCalendarController;
use App\Http\Controllers\Api\CropController;
use App\Http\Controllers\Api\FarmerAdvisoryController;
use App\Http\Controllers\Api\FarmWeatherController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\Api\TaskChecklistController;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\FarmBoundaryController;
use App\Http\Controllers\Api\FieldBlockController;
use App\Http\Controllers\Api\CropCycleController;
use App\Http\Controllers\Api\FarmActivityController;
use App\Http\Controllers\Api\FarmWarehouseController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\WeatherStationController;
use App\Services\PesapalService;

// Public Auth
Route::get('/registration-roles', [AuthController::class, 'registrationRoles']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Weather Endpoint
Route::post('/weather/location', [WeatherController::class, 'byLocation']);

// Public Organisation Invitations
Route::post('/organisations/{organisation}/invite', [OrganisationInvitationController::class, 'store']);
Route::post('/organisation-invitations/accept', [OrganisationInvitationController::class, 'accept']);

// Public Subscriptions (Viewing Plans)
Route::get('/subscriptions/plans', [SubscriptionController::class, 'plans']);

// Public Product, Category & Livestock Browsing
Route::get('/commodity-categories', [CommodityCategoryController::class, 'index']);
Route::get('/commodities', [CommodityController::class, 'index']);
Route::get('/listings', [ProductListingController::class, 'index']);
Route::get('/listings/{id}', [ProductListingController::class, 'show']);

// Public Extension Officers Browsing
Route::get('/extension-officers', [ExtensionOfficerController::class, 'index']);
Route::get('/extension-officers/{id}', [ExtensionOfficerController::class, 'show']);

// Public Research & Demonstration Farm Browsing
Route::get('/research-institutions', [ResearchInstitutionController::class, 'index']);
Route::get('/research-institutions/{id}', [ResearchInstitutionController::class, 'show']);
Route::get('/researchers', [ResearcherController::class, 'index']);
Route::get('/researchers/{id}', [ResearcherController::class, 'show']);
Route::get('/research-publications', [ResearchPublicationController::class, 'index']);
Route::get('/research-publications/{id}', [ResearchPublicationController::class, 'show']);
Route::get('/demonstration-farms', [DemonstrationFarmController::class, 'index']);
Route::get('/demonstration-farms/{id}', [DemonstrationFarmController::class, 'show']);

// Knowledge Hub (public)
Route::get('/knowledge-hub', [KnowledgeHubController::class, 'index']);
Route::get('/knowledge-hub/search', [KnowledgeHubController::class, 'search']);
Route::get('/training-courses', [TrainingCourseController::class, 'index']);
Route::get('/financial-institutions', [FinancialInstitutionController::class, 'index']);
Route::get('/financial-institutions/categories', [FinancialInstitutionController::class, 'categories']);
Route::get('/financial-institutions/{id}', [FinancialInstitutionController::class, 'show']);
Route::get('/insurance-products', [InsuranceProductController::class, 'index']);

Route::get('/nmb/status', [NmbBankController::class, 'status']);

Route::get('/nmb/oauth/callback', [NmbOAuthController::class, 'callback']);
Route::get('/nmb/ping', [NmbOAuthController::class, 'ping']);

Route::get('/nmb/banks', [NmbBankController::class, 'banks']);

Route::get('/insurance-products/{id}', [InsuranceProductController::class, 'show']);
Route::get('/training-courses/{idOrSlug}', [TrainingCourseController::class, 'show']);

// Government – public
Route::get('/government/announcements', [GovernmentAnnouncementController::class, 'index']);
Route::get('/government/announcements/{idOrSlug}', [GovernmentAnnouncementController::class, 'show']);
Route::get('/subsidy-programs', [SubsidyProgramController::class, 'index']);
Route::get('/subsidy-programs/{subsidyProgram}', [SubsidyProgramController::class, 'show']);
Route::get('/agricultural-statistics', [AgriculturalStatisticController::class, 'index']);
Route::get('/agricultural-statistics/food-security', [AgriculturalStatisticController::class, 'foodSecurity']);
Route::get('/agricultural-statistics/time-series', [AgriculturalStatisticController::class, 'timeSeries']);

// Public Disease & Outbreak Browsing
Route::apiResource('diseases', DiseaseController::class)->only(['index', 'show']);
Route::apiResource('disease-outbreaks', DiseaseOutbreakController::class)->only(['index', 'show']);

// Public Machinery Metadata, Listing Browsing & Reviews
Route::apiResource('machinery-categories', MachineryCategoryController::class)->only(['index', 'show']);
Route::apiResource('machinery-brands', MachineryBrandController::class)->only(['index', 'show']);
Route::apiResource('machinery-models', MachineryModelController::class)->only(['index', 'show']);
Route::get('/machinery-featured', [MachineryListingController::class, 'featured']);
Route::get('/machinery/{machineryListing}/related', [MachineryListingController::class, 'related']);
Route::get('/machinery/{machineryListing}/review-summary', [MachineryReviewController::class, 'summary']);

// Public Livestock Routes
Route::get('/livestock', [LivestockListingController::class, 'index']);
Route::get('/livestock/{id}', [LivestockListingController::class, 'show']);
Route::get('/livestock/{id}/related', [LivestockListingController::class, 'related']);
Route::apiResource('livestock-listings', LivestockListingController::class)->only(['index', 'show']);

// Public Services & Provider Routes
Route::get('/services/stats', [ServiceController::class, 'stats']);
Route::get('/providers/{user}/services', [ServiceController::class, 'provider']);
Route::get('/provider/{id}', [ProviderProfileController::class, 'show']);
Route::get('/services/{service}/packages', [ServicePackageController::class, 'index']);

// Public: View offers on a listing
Route::get('/listings/{id}/offers', [OfferController::class, 'listingOffers']);

// Pesapal Callbacks (Must be public so Pesapal can communicate with your app)
Route::get('/payments/pesapal/callback', [PaymentController::class, 'callback']);
Route::any('/payments/pesapal/ipn', [PaymentController::class, 'ipn']); 

// ====================== PROTECTED ROUTES ======================
Route::middleware('auth:sanctum')->group(function () {

    // User & Authentication
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Farm, Boundary & Field Block Management
    Route::apiResource('farms', FarmController::class);

    // Transporter trucks & rates
    Route::get('/my-trucks', [TransporterVehicleController::class, 'index']);
    Route::post('/my-trucks', [TransporterVehicleController::class, 'store'])->middleware('subscription.active');
    Route::put('/my-trucks/{vehicle}', [TransporterVehicleController::class, 'update']);
    Route::delete('/my-trucks/{vehicle}', [TransporterVehicleController::class, 'destroy']);

    // Transport quotes after order
    Route::get('/orders/{order}/transport-quotes', [TransportQuoteController::class, 'forOrder']);
    Route::post('/orders/{order}/transport-quotes/select', [TransportQuoteController::class, 'select']);

    // Subscriptions (farmers exempt from listing gate)
    Route::get('/subscription-plans', [SubscriptionController::class, 'plans']);
    Route::get('/my-subscription', [SubscriptionController::class, 'mine']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);

    Route::apiResource('farm-boundaries', FarmBoundaryController::class);
    Route::apiResource('field-blocks', FieldBlockController::class);
    Route::get('/farms/{farm}/boundary', [FarmBoundaryController::class, 'show']);
    Route::post('/farms/{farm}/boundary', [FarmBoundaryController::class, 'store']);
    Route::delete('/farms/{farm}/boundary', [FarmBoundaryController::class, 'destroy']);
    Route::post('/field-blocks/{fieldBlock}/boundary', [FieldBlockController::class, 'updateBoundary']);

    Route::post('/farms/{farm}/documents', [DocumentController::class, 'upload']);

    // Crop Cycles & Farm Activities (Farm ERP)
    Route::apiResource('crop-cycles', CropCycleController::class);
    Route::post('/crop-cycles/{cropCycle}/harvest', [CropCycleController::class, 'harvest']);
    Route::apiResource('farm-activities', FarmActivityController::class);
    Route::get('/farm-activities-cost-summary', [FarmActivityController::class, 'costSummary']);

    // Warehouses & Inventory (Farm ERP)
    Route::apiResource('farm-warehouses', FarmWarehouseController::class);
    Route::apiResource('inventory-items', InventoryItemController::class);

    // Organisation Management
    Route::apiResource('organisations', OrganisationController::class);
    Route::get('/organisations/{organisation}/dashboard', [OrganisationController::class, 'dashboard']);
    Route::get('/organisations/{organisation}/members', [OrganisationController::class, 'members']);
    Route::post('/organisations/{organisation}/members', [OrganisationController::class, 'addMember']);
    Route::patch('/organisations/{organisation}/members/{member}', [OrganisationController::class, 'updateMember']);
    Route::delete('/organisations/{organisation}/members/{member}', [OrganisationController::class, 'removeMember']);
    Route::post('/organisations/{organisation}/verify', [OrganisationController::class, 'verify']);

    // Inventory & Stock Management
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::post('/stock-movements', [StockMovementController::class, 'store']);
    Route::get('/stock-movements/{stockMovement}', [StockMovementController::class, 'show']);
    Route::get('/inventory-items/{inventoryItem}/stock-balance', [StockMovementController::class, 'balance']);
    Route::get('/inventory-items/{inventoryItem}/stock-history', [StockMovementController::class, 'history']);
    Route::get('/inventory/low-stock', [StockMovementController::class, 'lowStock']);
    Route::get('/stock-movements-summary', [StockMovementController::class, 'summary']);

    // Weather Station Management
    Route::get('/weather-stations/{station}/latest-observation', [WeatherStationController::class, 'latestObservation']);
    Route::get('/weather-stations/{station}/alerts', [WeatherStationController::class, 'alerts']);
    Route::get('/weather-stations', [WeatherStationController::class, 'index']);
    Route::get('/weather-stations/{weather_station}', [WeatherStationController::class, 'show']);

    Route::middleware(['can:manage-weather-stations'])->group(function () {
        Route::post('/weather-stations', [WeatherStationController::class, 'store']);
        Route::put('/weather-stations/{weather_station}', [WeatherStationController::class, 'update']);
        Route::patch('/weather-stations/{weather_station}', [WeatherStationController::class, 'update']);
        Route::delete('/weather-stations/{weather_station}', [WeatherStationController::class, 'destroy']);
    });

    // Crop & Calendar Management
    Route::apiResource('crops', CropController::class);
    Route::apiResource('crop-calendars', CropCalendarController::class)->only(['index', 'store', 'show']);

    // Farmer Advisories
    Route::get('/farmer-advisories', [FarmerAdvisoryController::class, 'index']);
    Route::get('/farmer-advisories/{farmerAdvisory}', [FarmerAdvisoryController::class, 'show']);

    // Weather Services
    Route::get('/weather/{station}', [WeatherController::class, 'current']);
    Route::get('/farms/{farm}/weather', [FarmWeatherController::class, 'index']);

    // ==================== TASK MANAGEMENT ====================
    Route::prefix('tasks')->group(function () {
        // Fixed/special routes FIRST
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::get('/dashboard', [TaskController::class, 'dashboard']);

        Route::get('/reports/overdue', [TaskController::class, 'overdue']);
        Route::get('/reports/due-today', [TaskController::class, 'dueToday']);
        Route::get('/reports/upcoming', [TaskController::class, 'upcoming']);
        Route::get('/reports/statistics', [TaskController::class, 'statistics']);

        // Task-specific operations
        Route::post('/{task}/start', [TaskController::class, 'startTask']);
        Route::post('/{task}/complete', [TaskController::class, 'completeTask']);
        Route::post('/{task}/cancel', [TaskController::class, 'cancelTask']);
        Route::post('/{task}/reopen', [TaskController::class, 'reopenTask']);

        Route::post('/{task}/assign', [TaskController::class, 'assignUser']);
        Route::delete('/{task}/assignments/{userId}', [TaskController::class, 'removeAssignment']);
        Route::post('/{task}/accept-assignment', [TaskController::class, 'acceptAssignment']);

        Route::get('/{task}/assigned-users', [TaskController::class, 'assignedUsers']);

        Route::post('/{task}/reminders', [TaskController::class, 'addReminder']);
        Route::get('/{task}/progress', [TaskController::class, 'progress']);
        
        Route::delete('/reminders/{workflowReminder}', [TaskController::class, 'removeReminder']);
    });

    Route::apiResource('tasks', TaskController::class);

    // Task Sub-resources (Comments & Checklists)
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store']);
    Route::post('/tasks/{task}/checklist', [TaskChecklistController::class, 'store']);
    Route::patch('/task-checklists/{taskChecklist}/complete', [TaskChecklistController::class, 'complete']);

    // ==================== WORKFLOW MANAGEMENT ====================
    Route::prefix('workflows')->group(function () {
        // Fixed/static routes FIRST
        Route::get('/', [WorkflowController::class, 'index']);
        Route::post('/', [WorkflowController::class, 'store']);
        Route::get('/statistics', [WorkflowController::class, 'statistics']);

        // Workflow instance actions
        Route::post('/instances/{instanceId}/next-step', [WorkflowController::class, 'advanceToNextStep']);
        Route::post('/instances/{instanceId}/complete', [WorkflowController::class, 'completeInstance']);
        Route::post('/instances/{instanceId}/cancel', [WorkflowController::class, 'cancelInstance']);
        Route::post('/instances/{instanceId}/restart', [WorkflowController::class, 'restartInstance']);

        // Workflow step actions
        Route::put('/steps/{stepId}', [WorkflowController::class, 'updateStep']);
        Route::patch('/steps/{stepId}', [WorkflowController::class, 'updateStep']);
        Route::delete('/steps/{stepId}', [WorkflowController::class, 'destroyStep']);

        // Workflow schedule actions
        Route::put('/schedules/{scheduleId}', [WorkflowController::class, 'updateSchedule']);
        Route::patch('/schedules/{scheduleId}', [WorkflowController::class, 'updateSchedule']);
        Route::delete('/schedules/{scheduleId}', [WorkflowController::class, 'destroySchedule']);

        // Dynamic /{workflow} parameter routes LAST
        Route::get('/{workflow}', [WorkflowController::class, 'show']);
        Route::put('/{workflow}', [WorkflowController::class, 'update']);
        Route::patch('/{workflow}', [WorkflowController::class, 'update']);
        Route::delete('/{workflow}', [WorkflowController::class, 'destroy']);

        Route::post('/{workflow}/activate', [WorkflowController::class, 'activate']);
        Route::post('/{workflow}/deactivate', [WorkflowController::class, 'deactivate']);
        Route::post('/{workflow}/instances', [WorkflowController::class, 'startInstance']);
        Route::post('/{workflow}/steps', [WorkflowController::class, 'storeStep']);
        Route::post('/{workflow}/schedules', [WorkflowController::class, 'storeSchedule']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show']);

    // Advisory Requests & Extension Officer Services
    Route::post('/advisory-requests', [AdvisoryRequestController::class, 'store']);
    Route::get('/my-advisory-requests', [AdvisoryRequestController::class, 'myRequests']);
    Route::put('/advisory-requests/{id}', [AdvisoryRequestController::class, 'update']);
    Route::post('/farm-visits', [FarmVisitController::class, 'store']);
    Route::post('/extension-ratings', [ExtensionOfficerRatingController::class, 'store']);

    // Disease Management System
    Route::get('/disease-reports/{report}/recommendations', [DiseaseController::class, 'recommendations']);
    Route::apiResource('diseases', DiseaseController::class);
    Route::apiResource('disease-reports', DiseaseReportController::class);
    Route::apiResource('disease-diagnoses', DiseaseDiagnosisController::class);
    Route::apiResource('disease-verifications', DiseaseVerificationController::class);
    Route::apiResource('disease-outbreaks', DiseaseOutbreakController::class)->except(['index', 'show']);

    // Protected Research & Demonstration Farm Management
    Route::apiResource('research-institutions', ResearchInstitutionController::class)->except(['index', 'show']);
    Route::apiResource('researchers', ResearcherController::class)->except(['index', 'show']);
    Route::apiResource('research-publications', ResearchPublicationController::class)->except(['index', 'show']);
    Route::apiResource('demonstration-farms', DemonstrationFarmController::class)->except(['index', 'show']);

    // Analytics & Reports
    Route::prefix('analytics')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index']);
        Route::get('/platform', [AnalyticsController::class, 'platformOverview']);
        Route::get('/marketplace', [AnalyticsController::class, 'marketplace']);
        Route::get('/farm-production', [AnalyticsController::class, 'farmProduction']);
        Route::get('/forecast', [AnalyticsController::class, 'forecast']);
        Route::get('/esg', [AnalyticsController::class, 'esg']);
        Route::get('/farmer-dashboard', [AnalyticsController::class, 'farmerDashboard']);
    });

    // Government module
    Route::get('/government/dashboard', [GovernmentDashboardController::class, 'index']);
    Route::apiResource('government/announcements', GovernmentAnnouncementController::class)->except(['index', 'show']);
    Route::apiResource('subsidy-programs', SubsidyProgramController::class)->except(['index', 'show']);
    Route::apiResource('subsidy-applications', SubsidyApplicationController::class)->only(['index', 'store', 'show']);
    Route::post('/subsidy-applications/{subsidyApplication}/submit', [SubsidyApplicationController::class, 'submit']);
    Route::post('/subsidy-applications/{subsidyApplication}/review', [SubsidyApplicationController::class, 'review']);
    Route::post('/subsidy-applications/{subsidyApplication}/disburse', [SubsidyApplicationController::class, 'disburse']);
    Route::apiResource('agricultural-registrations', AgriculturalRegistrationController::class)->only(['index', 'store', 'show']);
    Route::post('/agricultural-registrations/{agriculturalRegistration}/review', [AgriculturalRegistrationController::class, 'review']);
    Route::post('/agricultural-statistics', [AgriculturalStatisticController::class, 'store']);

    // Knowledge – Training & AI Advisor
    Route::apiResource('training-courses', TrainingCourseController::class)->except(['index', 'show']);
    Route::post('/training-courses/{trainingCourse}/enroll', [TrainingCourseController::class, 'enroll']);
    Route::post('/training-courses/{trainingCourse}/pay', [TrainingCourseController::class, 'initiatePayment']);
    Route::post('/training-courses/{trainingCourse}/progress', [TrainingCourseController::class, 'updateProgress']);
    Route::get('/my-course-enrollments', [TrainingCourseController::class, 'myEnrollments']);

    Route::get('/ai-recommendations', [AIAdvisorController::class, 'index']);
    Route::post('/ai-recommendations/generate', [AIAdvisorController::class, 'generate']);
    Route::get('/ai-recommendations/{aiRecommendation}', [AIAdvisorController::class, 'show']);
    Route::post('/ai-recommendations/{aiRecommendation}/accept', [AIAdvisorController::class, 'accept']);

    // Finance Dashboard & Reports
    Route::get('/finance/dashboard', [FinanceDashboardController::class, 'index']);
    Route::get('/finance/reports', [FinanceReportController::class, 'index']);

    // Loan Products
    Route::apiResource('loan-products', LoanProductController::class);
    Route::get('/finance/institution', [FinancialInstitutionController::class, 'mine']);
    Route::post('/finance/institution', [FinancialInstitutionController::class, 'store']);
    Route::get('/finance/dashboard', [FinancialInstitutionController::class, 'dashboard']);
    Route::apiResource('insurance-products', InsuranceProductController::class)->except(['index', 'show']);
    Route::post('/insurance-products/{id}/apply', [InsuranceProductController::class, 'apply']);
    Route::get('/my-insurance-applications', [InsuranceProductController::class, 'myApplications']);
    Route::get('/knowledge/institution', [KnowledgeInstitutionController::class, 'mine']);
    Route::post('/knowledge/institution', [KnowledgeInstitutionController::class, 'store']);
    Route::get('/knowledge/dashboard', [KnowledgeInstitutionController::class, 'dashboard']);

    // Loan Applications (CRUD + workflow)
    Route::apiResource('loan-applications', LoanApplicationController::class);

    // NMB Open Banking (agri-fintech)
    
    Route::get('/finance-dossier', [FinanceDossierController::class, 'mine']);
    Route::get('/finance-dossier/{user}', [FinanceDossierController::class, 'show']);

    Route::prefix('nmb')->group(function () {
        Route::get('/bank', [NmbBankController::class, 'bank']);
        Route::get('/accounts', [NmbBankController::class, 'accounts']);
        Route::get('/balances', [NmbBankController::class, 'balances']);
        Route::get('/transactions', [NmbBankController::class, 'transactions']);
        Route::get('/transaction-request-types', [NmbBankController::class, 'transactionRequestTypes']);
        Route::post('/customers', [NmbBankController::class, 'createCustomer']);
        Route::post('/counterparties', [NmbBankController::class, 'createCounterparty']);
        Route::post('/pay', [NmbBankController::class, 'pay']);
        Route::post('/link-account', [NmbBankController::class, 'linkAccount']);
        Route::get('/my-links', [NmbBankController::class, 'myLinks']);
    });

    Route::prefix('loan-applications')->group(function () {
        Route::post('/{loanApplication}/submit', [LoanApplicationController::class, 'submit']);
        Route::post('/{loanApplication}/review', [LoanApplicationController::class, 'review']);
        Route::post('/{loanApplication}/approve', [LoanApplicationController::class, 'approve']);
        Route::post('/{loanApplication}/reject', [LoanApplicationController::class, 'reject']);
        Route::post('/{loanApplication}/disburse', [LoanApplicationController::class, 'disburse']);
        Route::post('/{loanApplication}/generate-schedule', [LoanApplicationController::class, 'generateSchedule']);
        Route::post('/{loanApplication}/complete', [LoanApplicationController::class, 'complete']);
    });

    // Protected Subscriptions Management
    Route::prefix('subscriptions')->group(function () {
        Route::get('/current', [SubscriptionController::class, 'current']);
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
    });

    // Farmer Profile
    Route::post('/farmer/profile', [FarmerProfileController::class, 'store']);
    Route::get('/farmer/profile', [FarmerProfileController::class, 'show']);

    // Provider Profile
    Route::get('/provider/profile', [ProviderProfileController::class, 'myProfile']);
    Route::put('/provider/profile', [ProviderProfileController::class, 'update']);

    // Product Listings (Farmers)
    Route::get('/my-listings', [ProductListingController::class, 'myListings']);
    Route::post('/listings', [ProductListingController::class, 'store'])->middleware('subscription.listing');
    Route::put('/listings/{id}', [ProductListingController::class, 'update']);
    Route::delete('/listings/{id}', [ProductListingController::class, 'destroy']);

    // Shopping Cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [ShoppingCartController::class, 'index']);
        Route::post('/add', [ShoppingCartController::class, 'add']);
        Route::put('/update/{item}', [ShoppingCartController::class, 'update']);
        Route::delete('/remove/{item}', [ShoppingCartController::class, 'remove']);
        Route::delete('/clear', [ShoppingCartController::class, 'clear']);
    });

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{wishlist}', [WishlistController::class, 'destroy']);
    });

    // Machinery Listings & Bookings
    Route::get('/machinery-my-listings', [MachineryListingController::class, 'myListings']);
    Route::post('/machinery', [MachineryListingController::class, 'store'])->middleware('subscription.listing');
    Route::put('/machinery/{machineryListing}', [MachineryListingController::class, 'update']);
    Route::patch('/machinery/{machineryListing}', [MachineryListingController::class, 'update']);
    Route::delete('/machinery/{machineryListing}', [MachineryListingController::class, 'destroy']);
    Route::get('/my-input-listings', [InputListingController::class, 'myListings']);
    Route::apiResource('input-listings', InputListingController::class)->except(['index', 'show', 'store']);
    Route::post('/input-listings', [InputListingController::class, 'store'])->middleware('subscription.listing');

    Route::get('/machinery-owner-bookings', [MachineryBookingController::class, 'ownerBookings']);
    Route::post('/machinery-bookings/{machineryBooking}/accept', [MachineryBookingController::class, 'accept']);
    Route::post('/machinery-bookings/{machineryBooking}/reject', [MachineryBookingController::class, 'reject']);
    Route::post('/machinery-bookings/{machineryBooking}/dispatch', [MachineryBookingController::class, 'dispatch']);
    Route::post('/machinery-bookings/{machineryBooking}/start', [MachineryBookingController::class, 'start']);
    Route::post('/machinery-bookings/{machineryBooking}/complete', [MachineryBookingController::class, 'complete']);
    Route::post('/machinery-bookings/{machineryBooking}/cancel', [MachineryBookingController::class, 'cancel']);
    Route::apiResource('machinery-bookings', MachineryBookingController::class);

    // Machinery Reviews
    Route::apiResource('machinery-reviews', MachineryReviewController::class);

    // Protected Livestock Listing Routes
    Route::apiResource('livestock-listings', LivestockListingController::class)->except(['index', 'show']);

    // Protected Services & Packages Routes
    Route::get('/provider/services', [ServiceController::class, 'myServices']);
    Route::post('/services', [ServiceController::class, 'store'])->middleware('subscription.listing');
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::patch('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    Route::get('/my-service-packages', [ServicePackageController::class, 'myPackages']);
    Route::post('/service-packages', [ServicePackageController::class, 'store']);
    Route::put('/service-packages/{id}', [ServicePackageController::class, 'update']);
    Route::delete('/service-packages/{id}', [ServicePackageController::class, 'destroy']);

    // Service Bookings
    Route::prefix('service-bookings')->group(function () {
        Route::get('/', [ServiceBookingController::class, 'index']);
        Route::post('/', [ServiceBookingController::class, 'store']);
        Route::get('/provider', [ServiceBookingController::class, 'providerBookings']);
        Route::get('/{id}', [ServiceBookingController::class, 'show']);
        Route::put('/{id}/accept', [ServiceBookingController::class, 'accept']);
        Route::put('/{id}/reject', [ServiceBookingController::class, 'reject']);
        Route::put('/{id}/on-the-way', [ServiceBookingController::class, 'onTheWay']);
        Route::put('/{id}/start', [ServiceBookingController::class, 'start']);
        Route::put('/{id}/complete', [ServiceBookingController::class, 'complete']);
        Route::put('/{id}/cancel', [ServiceBookingController::class, 'cancel']);
    });

    // Orders & Delivery Confirmation
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    Route::post('/orders/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery']);

    // Offers
    Route::post('/offers', [OfferController::class, 'store']);
    Route::post('/offers/{id}/accept', [OfferDecisionController::class, 'accept']);
    Route::post('/offers/{id}/reject', [OfferDecisionController::class, 'reject']);

    // Logistics
    Route::post('/logistics', [LogisticsController::class, 'create']);
    Route::post('/logistics/{id}/assign', [LogisticsController::class, 'assign']);
    Route::patch('/logistics/{id}/status', [LogisticsController::class, 'updateStatus']);

    // Transport / Dispatch
    Route::post('/dispatch/assign', [TransportAssignmentController::class, 'assign']);

    Route::get('/dispatch/{id}', [TransportAssignmentController::class, 'show']);

    // Delivery Updates & Tracking
    Route::post('/delivery-updates', [DeliveryUpdateController::class, 'store']);
    Route::get('/transport-assignments/{id}/tracking', [DeliveryUpdateController::class, 'tracking']);

    // Payments
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::post('/payments/{id}/paid', [PaymentController::class, 'markPaid']);
    Route::post('/payments/confirm-return', [PaymentController::class, 'confirmReturn']);

    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'show']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::get('/summary', [WalletController::class, 'summary']);
        Route::post('/withdraw', [WithdrawalController::class, 'store']);
    });

    // Chart of Accounts & Journal Entries (Farm Financial Engine)
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('journal-entries', JournalEntryController::class)->only(['index', 'store', 'show', 'destroy']);

    Route::apiResource('withdrawals', WithdrawalController::class)->only(['index', 'store']);

// ==================== PLATFORM ADMINISTRATOR ROUTES ====================
    Route::prefix('admin')->group(function () {

        Route::get('/subscription-plans', [AdminSubscriptionController::class, 'plansIndex']);
        Route::post('/subscription-plans', [AdminSubscriptionController::class, 'plansStore']);
        Route::put('/subscription-plans/{plan}', [AdminSubscriptionController::class, 'plansUpdate']);
        Route::delete('/subscription-plans/{plan}', [AdminSubscriptionController::class, 'plansDestroy']);
        Route::get('/user-subscriptions', [AdminSubscriptionController::class, 'userIndex']);
        Route::post('/user-subscriptions', [AdminSubscriptionController::class, 'userStore']);
        Route::put('/user-subscriptions/{userSubscription}', [AdminSubscriptionController::class, 'userUpdate']);
        Route::delete('/user-subscriptions/{userSubscription}', [AdminSubscriptionController::class, 'userDestroy']);

        Route::get('/bank-links', [AdminBankLinkController::class, 'index']);
        Route::post('/bank-links/{userBankLink}/verify', [AdminBankLinkController::class, 'verify']);
        Route::post('/bank-links/{userBankLink}/unverify', [AdminBankLinkController::class, 'unverify']);
  // admin check in controllers (users.role column)
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::patch('/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
        Route::get('/listings', [AdminDashboardController::class, 'listings']);
        Route::get('/orders', [AdminDashboardController::class, 'orders']);
        Route::get('/payments', [AdminDashboardController::class, 'payments']);
        Route::get('/transporters', [AdminDashboardController::class, 'transporters']);
        
        Route::get('/reports', [AdminReportController::class, 'index']);

        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
        Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve']);
        Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject']);

        // Admin Logistics Route
        Route::get('/moderation/products', [AdminModerationController::class, 'productListings']);
        Route::put('/moderation/products/{id}', [AdminModerationController::class, 'updateProductListing']);
        Route::delete('/moderation/products/{id}', [AdminModerationController::class, 'deleteProductListing']);
        Route::get('/moderation/services', [AdminModerationController::class, 'services']);
        Route::put('/moderation/services/{id}', [AdminModerationController::class, 'updateService']);
        Route::delete('/moderation/services/{id}', [AdminModerationController::class, 'deleteService']);
        Route::get('/moderation/machinery', [AdminModerationController::class, 'machinery']);
        Route::post('/moderation/machinery', [AdminModerationController::class, 'storeMachinery']);
        Route::put('/moderation/machinery/{id}', [AdminModerationController::class, 'updateMachinery']);
        Route::delete('/moderation/machinery/{id}', [AdminModerationController::class, 'deleteMachinery']);
        Route::get('/moderation/inputs', [AdminModerationController::class, 'inputs']);
        Route::put('/moderation/inputs/{id}', [AdminModerationController::class, 'updateInput']);
        Route::delete('/moderation/inputs/{id}', [AdminModerationController::class, 'deleteInput']);
        Route::get('/catalog/commodities', [AdminModerationController::class, 'commodities']);
        Route::post('/catalog/commodities', [AdminModerationController::class, 'storeCommodity']);
        Route::put('/catalog/commodities/{id}', [AdminModerationController::class, 'updateCommodity']);
        Route::delete('/catalog/commodities/{id}', [AdminModerationController::class, 'deleteCommodity']);
        Route::get('/catalog/crops', [AdminModerationController::class, 'crops']);
        Route::post('/catalog/crops', [AdminModerationController::class, 'storeCrop']);
        Route::put('/catalog/crops/{id}', [AdminModerationController::class, 'updateCrop']);
        Route::delete('/catalog/crops/{id}', [AdminModerationController::class, 'deleteCrop']);

        Route::get('/logistics-requests', function () {
            return \App\Models\LogisticsRequest::with('assignment')->get();
        });
    });
});

// ==================== PREMIUM PROTECTED ROUTES ====================

// Active Subscription Required
Route::middleware([
    'auth:sanctum',
    'subscription.active'
])->group(function () {
    Route::post('/featured-listings', [ProductListingController::class, 'storeFeatured']);
});

// Premium Analytics Feature
Route::middleware([
    'auth:sanctum',
    'subscription.feature:analytics'
])->group(function () {
    Route::get('/analytics', [AdminReportController::class, 'analytics']);
});

// Verified Provider Badge Feature
Route::middleware([
    'auth:sanctum',
    'subscription.feature:verified_badge'
])->group(function () {
    Route::post('/provider/verify-badge', [ProviderProfileController::class, 'applyVerifiedBadge']);
});

// Tender Access Feature
Route::middleware([
    'auth:sanctum',
    'subscription.feature:tenders'
])->group(function () {
    Route::get('/tenders', [OfferController::class, 'tenders']);
});

// Public services marketplace (read-only). Writes under auth:sanctum.
Route::get('/service-categories', [ServiceController::class, 'categories']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);

// Input listings (public index/show; write via auth group below if duplicated OK)
Route::get('/input-listings', [InputListingController::class, 'index']);
Route::get('/input-listings/{id}', [InputListingController::class, 'show']);


// Public machinery marketplace (read-only). Writes are under auth:sanctum above.
Route::get('/machinery', [MachineryListingController::class, 'index']);
Route::get('/machinery/{machineryListing}', [MachineryListingController::class, 'show']);

// ==================== TEMPORARY TEST ROUTES ====================
Route::get('/pesapal/test', function (PesapalService $pesapal) {
    return $pesapal->authenticate();
});

Route::get('/pesapal/register-ipn', function (PesapalService $pesapal) {
    return $pesapal->registerIpn();
});
