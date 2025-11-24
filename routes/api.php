<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\InitialController;
use App\Http\Controllers\Auth\PusherController;
use App\Http\Controllers\App\FormDataController;
use App\Http\Controllers\Role\RoleListController;
use App\Http\Controllers\User\UserListController;


use App\Http\Controllers\User\UserShowController;
use App\Http\Controllers\Auth\ActAsRoleController;
use App\Http\Controllers\User\UserStoreController;
use App\Http\Controllers\Auth\OnboardingController;

use App\Http\Controllers\MyEshow\MyEshowController;
use App\Http\Controllers\User\UserActiveController;
use App\Http\Controllers\User\UserByRoleController;
use App\Http\Controllers\User\UserDeleteController;
use App\Http\Controllers\User\UserUpdateController;
use App\Http\Controllers\Auth\ActAsCompanyController;
use App\Http\Controllers\Company\MyCompanyController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Service\ServiceEditController;
use App\Http\Controllers\Service\ServiceListController;
use App\Http\Controllers\Service\ServiceShowController;
use App\Http\Controllers\Session\SessionJoinController;

use App\Http\Controllers\Session\SessionShowController;
use App\Http\Controllers\Sponsor\SponsorListController;
use App\Http\Controllers\Service\ServiceStoreController;
use App\Http\Controllers\Sponsor\SponsorStoreController;
use App\Http\Controllers\Bookmark\BookmarkListController;
use App\Http\Controllers\Document\DocumentEditController;
use App\Http\Controllers\Document\DocumentListController;
use App\Http\Controllers\Service\ServiceDeleteController;
use App\Http\Controllers\Service\ServiceUpdateController;
use App\Http\Controllers\Sponsor\SponsorDeleteController;
use App\Http\Controllers\Sponsor\SponsorUpdateController;
use App\Http\Controllers\Bookmark\BookmarkStoreController;
use App\Http\Controllers\Company\CompanyAllListController;
use App\Http\Controllers\Document\DocumentStoreController;
use App\Http\Controllers\Auth\CurrentSessionDataController;
use App\Http\Controllers\Bookmark\BookmarkDeleteController;
use App\Http\Controllers\Bookmark\BookmarkToggleController;
use App\Http\Controllers\Community\CommunityListController;
use App\Http\Controllers\Document\DocumentDeleteController;
use App\Http\Controllers\Document\DocumentUpdateController;
use App\Http\Controllers\Auth\StopActingAsCompanyController;

// Session Controllers
use App\Http\Controllers\Community\CommunityMemberController;
use App\Http\Controllers\Company\Admin\CompanyEditController;
use App\Http\Controllers\Company\Admin\CompanyListController;
use App\Http\Controllers\Company\Admin\CompanyShowController;
use App\Http\Controllers\Connection\ConnectionListController;
use App\Http\Controllers\Sponsor\SponsorPublicListController;


use App\Http\Controllers\Company\Admin\CompanyStoreController;
use App\Http\Controllers\Category\Admin\CategoryListController;
use App\Http\Controllers\Company\Admin\CompanyDeleteController;
use App\Http\Controllers\Company\Admin\CompanyUpdateController;
use App\Http\Controllers\Connection\ConnectionCancelController;
use App\Http\Controllers\Connection\ConnectionDeleteController;
use App\Http\Controllers\Category\Admin\CategoryStoreController;
use App\Http\Controllers\Connection\ConnectionRequestController;
use App\Http\Controllers\Category\Admin\CategoryDeleteController;
use App\Http\Controllers\Category\Admin\CategoryUpdateController;
use App\Http\Controllers\Connection\ConnectionResponseController;
use App\Http\Controllers\Certificate\Admin\CertificateListController;
use App\Http\Controllers\Certificate\Admin\CertificateStoreController;
use App\Http\Controllers\Certificate\Admin\CertificateDeleteController;
use App\Http\Controllers\Certificate\Admin\CertificateUpdateController;
use App\Http\Controllers\Session\SessionListController as SessionListController;
use App\Http\Controllers\Company\CompanyListController as PublicCompanyListController;
use App\Http\Controllers\Company\CompanyShowController as PublicCompanyShowController;
use App\Http\Controllers\Product\ProductEditController as PublicProductEditController;
use App\Http\Controllers\Product\ProductListController as PublicProductListController;
use App\Http\Controllers\Product\ProductShowController as PublicProductShowController;

// Connection Controllers
use App\Http\Controllers\Product\ProductStoreController as PublicProductStoreController;
use App\Http\Controllers\Product\ProductDeleteController as PublicProductDeleteController;
use App\Http\Controllers\Product\ProductUpdateController as PublicProductUpdateController;
use App\Http\Controllers\Product\Admin\ProductEditController as AdminProductEditController;
use App\Http\Controllers\Product\Admin\ProductListController as AdminProductListController;
use App\Http\Controllers\Product\Admin\ProductShowController as AdminProductShowController;
use App\Http\Controllers\Product\Admin\ProductStoreController as AdminProductStoreController;
use App\Http\Controllers\Service\Admin\ServiceEditController as AdminServiceEditController;
use App\Http\Controllers\Service\Admin\ServiceListController as AdminServiceListController;
use App\Http\Controllers\Service\Admin\ServiceShowController as AdminServiceShowController;
use App\Http\Controllers\Session\Admin\SessionEditController as AdminSessionEditController;
use App\Http\Controllers\Session\Admin\SessionListController as AdminSessionListController;
use App\Http\Controllers\Service\Admin\ServiceStoreController as AdminServiceStoreController;
use App\Http\Controllers\Session\Admin\SessionStoreController as AdminSessionStoreController;
use App\Http\Controllers\Product\Admin\ProductUpdateController as AdminProductUpdateController;
use App\Http\Controllers\Service\Admin\ServiceDeleteController as AdminServiceDeleteController;
use App\Http\Controllers\Service\Admin\ServiceUpdateController as AdminServiceUpdateController;
use App\Http\Controllers\Session\Admin\SessionDeleteController as AdminSessionDeleteController;
use App\Http\Controllers\Session\Admin\SessionUpdateController as AdminSessionUpdateController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::group([ 'middleware' => [ 'auth:sanctum', 'check.status' ] ], function() {
    
    Route::group(['prefix' => 'auth'], function() {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get('/current', CurrentSessionDataController::class);

        Route::post('/act-as-role/{role}', ActAsRoleController::class);
    });

    // Push auth
    Route::post('pusher', PusherController::class);

    // App 
    Route::group([ 'prefix' => 'dashboard' ], function() {
        Route::group([ 'prefix' => 'admin' ], function() {
            Route::get('/overview', OverviewController::class);
        });

    });

    // App 
    Route::group([ 'prefix' => 'app' ], function() {
        Route::get('initial', InitialController::class);

        Route::group([ 'prefix' => 'form-data' ], function() {
            Route::get('certificates/{type}', [FormDataController::class, 'certificates']);
            Route::get('categories/{type}', [FormDataController::class, 'categories']);
            Route::get('countries', [FormDataController::class, 'countries']);
            Route::get('users', [FormDataController::class, 'users']);
            Route::get('multiple-categories', [FormDataController::class, 'multipleCategories']);
        });
    });

    Route::group([ 'prefix' => 'me' ], function() {
        Route::get('/companies', MyCompanyController::class);
        Route::post('/act-as-company/{company_id}', ActAsCompanyController::class);
        Route::post('/stop-acting-as-company', StopActingAsCompanyController::class);
    });

    Route::group([ 'prefix' => 'my-eshow' ], function() {
        Route::get('/bookmarked-companies', [MyEshowController::class, 'myBookmarkedCompanies']);
        Route::get('/bookmarked-products', [MyEshowController::class, 'myBookmarkedProducts']);
        Route::get('/bookmarked-services', [MyEshowController::class, 'myBookmarkedServices']);
        Route::get('/bookmarked-sessions', [MyEshowController::class, 'myBookmarkedSessions']);
        Route::get('/connections', [MyEshowController::class, 'myConnections']);
        Route::get('/sessions', [MyEshowController::class, 'mySessions']);
    });

    // Admin 
    Route::group([ 'prefix' => 'admin' ], function() {
        Route::group([ 'prefix' => 'companies' ], function() {
            Route::get('/', CompanyListController::class);
            Route::post('/', CompanyStoreController::class);
            Route::get('/all', CompanyAllListController::class);
            Route::get('/{id}/edit', CompanyEditController::class);
            Route::post('/{id}', CompanyUpdateController::class);
            Route::delete('/{id}', CompanyDeleteController::class);
            Route::get('/{id}', CompanyShowController::class);
        });

        Route::group([ 'prefix' => 'categories' ], function() {
            Route::get('/', CategoryListController::class);
            Route::post('/', CategoryStoreController::class);
            Route::post('/{id}', CategoryUpdateController::class);
            Route::delete('/{id}', CategoryDeleteController::class);
        });

        Route::group([ 'prefix' => 'certificates' ], function() {
            Route::get('/', CertificateListController::class);
            Route::post('/', CertificateStoreController::class);
            Route::post('/{id}', CertificateUpdateController::class);
            Route::delete('/{id}', CertificateDeleteController::class);
        });

        Route::group([ 'prefix' => 'products' ], function() {
            Route::get('/', AdminProductListController::class);
            Route::post('/', AdminProductStoreController::class);
            Route::get('/{id}', AdminProductShowController::class);
            Route::get('/{id}/edit', AdminProductEditController::class);
            Route::post('/{id}', AdminProductUpdateController::class);
        });

        Route::group([ 'prefix' => 'sponsors' ], function() {
            Route::get('/', SponsorListController::class);
            Route::post('/', SponsorStoreController::class);
            Route::post('/{id}', SponsorUpdateController::class);
            Route::delete('/{id}', SponsorDeleteController::class);
        });

        Route::group([ 'prefix' => 'sessions' ], function() {
            Route::get('/', AdminSessionListController::class);
            Route::get('/{id}/edit', AdminSessionEditController::class);
            Route::post('/', AdminSessionStoreController::class);
            Route::post('/{id}', AdminSessionUpdateController::class);
            Route::delete('/{id}', AdminSessionDeleteController::class);
        });

        Route::group([ 'prefix' => 'services' ], function() {
            Route::get('/', AdminServiceListController::class);
            Route::post('/', AdminServiceStoreController::class);
            Route::get('/{id}/edit', AdminServiceEditController::class);
            Route::get('/{id}', AdminServiceShowController::class);
            Route::post('/{id}', AdminServiceUpdateController::class);
            Route::delete('/{id}', AdminServiceDeleteController::class);
        });
    });

    // Documents 
    Route::group([ 'prefix' => 'documents' ], function() {
        Route::post('/', DocumentStoreController::class);
        Route::get('/', DocumentListController::class);
        Route::get('/{id}/edit', DocumentEditController::class);
        Route::post('/{id}', DocumentUpdateController::class);
        Route::delete('/{id}', DocumentDeleteController::class);
    });

    // Services
    Route::group([ 'prefix' => 'services' ], function() {
        Route::get('/', ServiceListController::class);
        Route::post('/', ServiceStoreController::class);
        Route::get('/{id}/edit', ServiceEditController::class);
        Route::get('/{id}', ServiceShowController::class);
        Route::post('/{id}', ServiceUpdateController::class);
        Route::delete('/{id}', ServiceDeleteController::class);
    });

    // Products 
    Route::group([ 'prefix' => 'products' ], function() {
        Route::get('/', PublicProductListController::class);
        Route::post('/', PublicProductStoreController::class);
        Route::get('/{id}/edit', PublicProductEditController::class);
        Route::post('/{id}', PublicProductUpdateController::class);
        Route::delete('/{id}', PublicProductDeleteController::class);
        Route::get('/{id}', PublicProductShowController::class);
    });

    // Users 
    Route::group([ 'prefix' => 'users' ], function() {
        Route::get('/', UserListController::class);
        Route::get('/by-roles', UserByRoleController::class);
        Route::post('/', UserStoreController::class);
        Route::get('/{id}', UserShowController::class);
        Route::post('/{id}', UserUpdateController::class);
        Route::post('/{id}/active', UserActiveController::class);
        Route::delete('/{id}', UserDeleteController::class);
    });

    // Companies 
    Route::group([ 'prefix' => 'companies' ], function() {
        Route::get('/', PublicCompanyListController::class);
        Route::get('/{id}', PublicCompanyShowController::class);
        Route::get('/{id}/services', [PublicCompanyShowController::class, 'getCompanyServices']);
        Route::get('/{id}/products', [PublicCompanyShowController::class, 'getCompanyProducts']);
    });

    // Roles 
    Route::group([ 'prefix' => 'roles' ], function() {
        Route::get('/', RoleListController::class);
    });

    // Community 
    Route::group([ 'prefix' => 'community' ], function() {
        Route::get('/', CommunityListController::class);
        Route::get('/{id}', CommunityMemberController::class);
    });

    // Sponsors
    Route::group([ 'prefix' => 'sponsors' ], function() {
        Route::get('/', SponsorPublicListController::class);
    });

    // Sessions 
    Route::group([ 'prefix' => 'sessions' ], function() {
        Route::get('/', SessionListController::class);
        Route::get('/upcoming', [SessionListController::class, 'upcoming']);
        Route::get('/past', [SessionListController::class, 'past']);
        Route::get('/{id}', SessionShowController::class);
        Route::post('/{sessionId}/join', SessionJoinController::class);
    });

    // Notifications
    Route::group([ 'prefix' => 'notifications' ], function() {
        Route::get('/', [App\Http\Controllers\API\NotificationController::class, 'index']);
        Route::get('/unread-count', [App\Http\Controllers\API\NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [App\Http\Controllers\API\NotificationController::class, 'markAllAsRead']);
        Route::post('/{notification}/mark-read', [App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
        Route::post('/{notification}/mark-unread', [App\Http\Controllers\API\NotificationController::class, 'markAsUnread']);
        Route::delete('/{notification}', [App\Http\Controllers\API\NotificationController::class, 'destroy']);
    });

    // Connections 
    Route::group([ 'prefix' => 'connections' ], function() {
        Route::post('/request', ConnectionRequestController::class);
        Route::post('/{connection}/response', ConnectionResponseController::class);
        Route::post('/{connection}/cancel', ConnectionCancelController::class);
        Route::get('/', ConnectionListController::class);
        Route::delete('/{connection}', ConnectionDeleteController::class);
    });

    // Bookmarks 
    Route::group([ 'prefix' => 'bookmarks' ], function() {
        Route::get('/', BookmarkListController::class);
        Route::post('/', BookmarkStoreController::class);
        Route::put('/toggle', BookmarkToggleController::class);
        Route::delete('/{bookmark}', BookmarkDeleteController::class);
    });

    // Onboarding 
    Route::group([ 'prefix' => 'onboarding' ], function() {
        Route::get('/', OnboardingController::class);
        Route::post('/', [OnboardingController::class, 'update']);
    });

    // Messaging
    Route::group([ 'prefix' => 'messaging' ], function() {
        // Conversations
        Route::get('/conversations', [App\Http\Controllers\API\ConversationController::class, 'index']);
        Route::get('/conversations/direct/{user}', [App\Http\Controllers\API\ConversationController::class, 'getDirectConversation']);
        Route::get('/conversations/session/{session}', [App\Http\Controllers\API\ConversationController::class, 'getSessionConversation']);
        Route::get('/conversations/company/{company}', [App\Http\Controllers\API\ConversationController::class, 'getCompanyConversation']);
        Route::get('/conversations/{conversation}/messages', [App\Http\Controllers\API\ConversationController::class, 'getMessages']);
        Route::post('/conversations/{conversation}/read', [App\Http\Controllers\API\ConversationController::class, 'markAsRead']);
        Route::get('/conversations/{conversation}/participants', [App\Http\Controllers\API\ConversationController::class, 'getParticipants']);
        Route::get('/conversations/{conversation}/unread-count', [App\Http\Controllers\API\ConversationController::class, 'getUnreadCount']);
        
        // Messages
        Route::post('/conversations/{conversation}/messages/text', [App\Http\Controllers\API\MessageController::class, 'sendTextMessage']);
        Route::post('/conversations/{conversation}/messages/file', [App\Http\Controllers\API\MessageController::class, 'sendFileMessage']);
        
        // Call-related messages
        Route::post('/conversations/{conversation}/messages/missed-call', [App\Http\Controllers\API\MessageController::class, 'sendMissedCallMessage']);
        Route::post('/conversations/{conversation}/messages/video-call-request', [App\Http\Controllers\API\MessageController::class, 'sendVideoCallRequest']);
        Route::post('/conversations/{conversation}/messages/voice-call-request', [App\Http\Controllers\API\MessageController::class, 'sendVoiceCallRequest']);
        Route::post('/conversations/{conversation}/messages/call-ended', [App\Http\Controllers\API\MessageController::class, 'sendCallEndedMessage']);
        Route::post('/conversations/{conversation}/messages/call-rejected', [App\Http\Controllers\API\MessageController::class, 'sendCallRejectedMessage']);
        Route::post('/conversations/{conversation}/messages/call-accepted', [App\Http\Controllers\API\MessageController::class, 'sendCallAcceptedMessage']);
        
        Route::delete('/messages/{message}', [App\Http\Controllers\API\MessageController::class, 'deleteMessage']);
        Route::get('/conversations/{conversation}/unread-count', [App\Http\Controllers\API\MessageController::class, 'getUnreadCount']);
    });

    // Video Calls
    Route::group([ 'prefix' => 'video-calls' ], function() {
        // Room Management
        Route::post('/rooms', [App\Http\Controllers\API\VideoCallRoomController::class, 'store']);
        Route::get('/rooms/{roomId}', [App\Http\Controllers\API\VideoCallRoomController::class, 'show']);
        Route::post('/rooms/{roomId}/join', [App\Http\Controllers\API\VideoCallRoomController::class, 'join']);
        Route::post('/rooms/{roomId}/leave', [App\Http\Controllers\API\VideoCallRoomController::class, 'leave']);
        Route::post('/rooms/{roomId}/end', [App\Http\Controllers\API\VideoCallRoomController::class, 'end']);
        Route::get('/rooms/{roomId}/participants', [App\Http\Controllers\API\VideoCallRoomController::class, 'participants']);
        
        // Call Management
        Route::post('/calls/initiate', [App\Http\Controllers\API\VideoCallController::class, 'initiate']);
        Route::post('/calls/{callId}/accept', [App\Http\Controllers\API\VideoCallController::class, 'accept']);
        Route::post('/calls/{callId}/reject', [App\Http\Controllers\API\VideoCallController::class, 'reject']);
        Route::post('/calls/{callId}/end', [App\Http\Controllers\API\VideoCallController::class, 'end']);
        Route::get('/calls/{callId}', [App\Http\Controllers\API\VideoCallController::class, 'show']);
    });
    
});