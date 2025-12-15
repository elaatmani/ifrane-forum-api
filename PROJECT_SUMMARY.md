# Project Overview

This repository houses the Laravel 10 backend for the FoodEshow B2B events marketplace. It orchestrates user onboarding, company/product/service catalogs, conference sessions, networking features, rich messaging, meeting notifications, and a production-ready video calling stack. The codebase follows standard Laravel conventions (MVC + service/repository layers) augmented with broadcasting, queued jobs, and external integrations to support real-time collaboration.

---

## Application Architecture

- **Framework:** Laravel 10 on PHP 8.1+, PSR-4 autoloading, artisan tooling, and Pint for formatting.
- **Layering:** Controllers → Service/Repository layer (`app/Services`, `app/Repositories`) → Eloquent models → migrations/seeders. Traits (e.g., `Bookmarkable`, `TrackHistoryTrait`) encapsulate reusable behaviors.
- **Request Handling:** Sanctum authenticated APIs dominate (`routes/api.php`), supplemented by `routes/web.php` for browser endpoints and specialized route files (`auth.php`, `channels.php`, `external.php`, `debug.php`).
- **Middleware & Policies:** Custom middleware such as `check.status`, plus policies (`app/Policies`) and observers manage authorization and model lifecycle rules.
- **Events & Broadcasting:** Extensive event classes (`app/Events`) and listeners keep messaging, notifications, and video-call state synchronized through Pusher channels.
- **Queues & Scheduling:** Jobs leverage Laravel queues (configured via `config/queue.php`). Commands under `app/Console/Commands` (e.g., video-call cleanup) handle scheduled maintenance.

---

## External Integrations

- **Sanctum** for stateless API auth and token management.
- **Spatie Laravel Permission** enabling role-based access, impersonation (`ActAsRoleController`, `ActAsCompanyController`), and fine-grained policy checks.
- **Pusher + Laravel Echo** for real-time broadcasting of chat messages, notifications, and video-call events on conversation/user-specific channels.
- **Whereby API** via `WherebyService` to create/join/end video rooms; includes mock fallback when env keys are absent.
- **Google Sheets (`revolution/laravel-google-sheets`)** for syncing configurable datasets or importing external resources.
- **Pusher authentication** endpoints ensure secure private channel subscriptions.

---

## Authentication, Accounts, and Roles

- `/api/auth/*` routes cover registration, login, password reset, email verification, and logout (`routes/auth.php`).
- Current-session endpoints return profile, active company context, permissions, and dashboard counts.
- **Impersonation flows:** admins can act as specific roles or companies to troubleshoot user issues, with explicit endpoints to start/stop impersonation.
- **Status enforcement:** middleware checks `users.status` (e.g., suspended accounts) before granting API access.

---

## Domain Modules & APIs

| Module | Responsibilities | Representative Controllers/Routes |
| --- | --- | --- |
| **Companies** | Admin CRUD, public listings, bookmarks, sponsorship ties, certifications | `CompanyListController`, `Admin\Company*` controllers, `/api/admin/companies`, `/api/companies` |
| **Products & Services** | Separate admin vs public namespaces, file uploads, categorization, history tracking | `Product\Admin*`, `Product\Product*`, `Service\Admin*`, `Service\Service*` |
| **Categories & Certificates** | Manage taxonomies used in forms and filters | `Category\Admin*`, `Certificate\Admin*`, `FormDataController` endpoints |
| **Sponsors** | Public sponsor showcase plus admin CRUD | `Sponsor*` controllers |
| **Sessions & Events** | Admin scheduling, public browsing, user enrollment (`SessionJoinController`), session history | `Session*` controllers in both namespaces |
| **Community & Connections** | Member directory, connection requests/responses/cancellations, connection lists | `CommunityListController`, `Connection*` controllers |
| **Bookmarks & My Eshow** | Bookmark toggles for companies/products/services/sessions plus personalized dashboards | `Bookmark*` controllers, `MyEshowController` |
| **Documents & Media** | Upload, edit, delete user/company documents with metadata | `Document*` controllers, storage disk configuration in `config/filesystems.php` |
| **Messaging & Conversations** | Threaded messaging, message metadata, call-related message types, unread counts | `MessagingService`, events under `app/Events`, `examples/call-messaging-examples.md` |
| **Video Calling** | Room lifecycle, call lifecycle, participant tracking, auto cleanup | `VideoCallService`, `VideoCallController`s, `VIDEO_CALLING_README.md` |
| **Notifications** | Meeting notifications, unread badge counts, policy-protected API endpoints | `NotificationService`, `notification-structure.md` |

---

## Messaging System Details

- Supports `text`, `file`, and specialized call lifecycle messages (`missed_call`, `video_call_request`, `voice_call_request`, `call_accepted`, `call_rejected`, `call_ended`, `system`), each with metadata builders in `CallMetadataHelper`.
- APIs under `/api/messaging/conversations/*` handle sending messages, uploading attachments, updating read status, and broadcasting events (`message.sent`).
- Echo listeners (documented in `examples/call-messaging-examples.md`) process message types to drive UI changes.
- Unread counters and notification badges are updated via events such as `NotificationCountUpdated` and `UnreadCountUpdated`.

---

## Video Calling Subsystem

- **Endpoints:** Room CRUD (`/api/video-calls/rooms*`) and call lifecycle (`/api/video-calls/calls*`) handle creation, join/leave, acceptance, rejection, ending, and detail retrieval.
- **Data tables:** `video_call_rooms`, `video_calls`, `video_call_participants` (see migrations) store persistent call state, duration, participants, and statuses.
- **Events:** `VideoCallInitiated`, `VideoCallAccepted`, `VideoCallRejected`, `VideoCallEnded`, `VideoCallParticipantJoined/Left` broadcast on conversation, user, and room channels.
- **Automation:** Missing responses trigger auto-expiration (60 seconds) and cleanup command `php artisan video-calls:cleanup`.
- **WherebyService:** Creates meetings, provides host/viewer URLs, handles expiry, and falls back to mock data locally.

---

## Notification Flows

- Meeting-related notification schemas live in `notification-structure.md`, covering invitations, reminders, and completion summaries with severity levels.
- Notifications dispatch via `SystemNotification` classes, broadcast on `user.{id}` channels, and persist in `user_notifications`.
- Policies (`UserNotificationPolicy`, `MeetingPolicy`) ensure only authorized actors can view/dismiss notifications.
- Demo endpoints under `/demo/*` let developers trigger sample notifications for QA.

---

## Data Layer, Seeders, and Factories

- **Models:** `app/Models` includes `Company`, `Product`, `Service`, `Sponsor`, `Session`, `Conversation`, `Message`, `User`, `UserProfile`, `UserNotification`, `History`, `VideoCall*`, `Document`, `Certificate`, `Category`, and more.
- **Relationships:** Models define rich associations (e.g., companies ↔ products/services, users ↔ companies via pivot, conversations ↔ messages, sessions ↔ sponsors). Traits encapsulate shared pivot logic.
- **Migrations:** 40+ migration files define schema for marketplace entities, role/permission tables, messaging, bookmarks, history, and video calls.
- **Seeders:** `database/seeders/*` pre-populate base data (countries, categories, certificates, companies, users, roles) ensuring demos and automated tests start with realistic datasets.
- **Factories:** `database/factories` (e.g., `UserFactory`) enable generating additional fixtures for unit/feature testing.

---

## Services, Helpers, and Utilities

- **Services:** `MessagingService`, `NotificationService`, `VideoCallService`, `Community` services encapsulate domain logic beyond controllers.
- **Repositories:** Contracts and Eloquent implementations (`app/Repositories/Contracts`, `app/Repositories/Eloquent`) abstract persistence operations for easier swapping/mocking.
- **Helpers:** `CallMetadataHelper`, `SearchHelper`, generic `helpers.php` functions support metadata construction, search queries, and formatting.
- **Providers:** Custom service providers register bindings, event listeners, broadcast channels, and repository implementations.

---

## Real-time & Background Processing

- **Broadcasting:** Configured through `config/broadcasting.php`, using Pusher as default. `routes/channels.php` defines private channel authorization callbacks.
- **Events/Listeners:** Messaging, notifications, and video calls each dispatch dedicated events that listeners translate into user-facing broadcasts.
- **Queues:** Jobs leverage queue connections defined in `config/queue.php`; Horizon or database queues can be configured depending on environment.
- **Scheduled Commands:** Cleanup and maintenance commands are registered in `app/Console/Kernel.php`.

---

## Configuration & Environment

- `.env` expects keys for Sanctum, database connection, AWS or local storage, mail, Pusher, Whereby (`WHEREBY_API_KEY`, `WHEREBY_SUBDOMAIN`), Google integrations, and logging.
- `bootstrap/cache` contains optimized config/routes for production builds.
- `config/` directory holds granular settings (auth, cors, broadcasting, mail, recommendations, status, etc.) that align with respective services.

---

## Security & Validation

- **Authentication:** Sanctum guards for API routes, with middleware ensuring valid tokens and verifying account status.
- **Authorization:** Policies and Spatie roles/permissions gate sensitive operations (e.g., meeting management, notification deletion).
- **Validation:** Form requests under `app/Http/Requests` perform per-endpoint validation, including enum/UUID rules and custom validation logic in `app/Rules`.
- **Audit Trails:** `TrackHistoryTrait` and `History` model log changes to key entities for compliance.
- **Rate limiting:** Auth routes throttle verification links and notification resends.

---

## Developer Tooling & Testing

- **Testing:** Feature and unit tests reside in `tests/Feature` and `tests/Unit`, bootstrapped via `Tests\TestCase` and `CreatesApplication`.
- **Examples:** `examples/call-messaging-examples.md` offers ready-made snippets for call messaging flows.
- **Docs:** `VIDEO_CALLING_README.md` and `notification-structure.md` act as living specifications.
- **Utilities:** `phpunit.xml`, `artisan`, and `composer` scripts speed up onboarding.

---

## Operational Considerations & Next Steps

- **Monitoring:** Review `storage/logs/laravel.log`, set `LOG_LEVEL=debug` during troubleshooting, and ensure queue workers and broadcasting services are monitored.
- **Scalability:** Real-time features depend on Pusher plan limits; consider self-hosted alternatives if needed.
- **Documentation gaps:** The public `README.md` remains default Laravel boilerplate—update it with this summary or link to `PROJECT_SUMMARY.md`.
- **Env templates:** Confirm `.env.example` lists Pusher, Whereby, Google, and mail credentials to avoid onboarding friction.
- **Future enhancements:** Video-call roadmap (recording, screen sharing, file transfer, analytics, scheduling) is captured in `VIDEO_CALLING_README.md`; align upcoming sprints with those items.
- **Visual aids:** Add architecture diagrams or sequence charts for conversations and call flows to complement textual docs.

---

This document should serve as the authoritative onboarding reference for contributors, QA engineers, and stakeholders who need a holistic understanding of the backend’s capabilities, touchpoints, and operational requirements.
